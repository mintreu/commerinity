<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Casts\UserTypeCast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ProgramRequest;
use App\Http\Resources\Dashboard\ProgramResource;
use App\Models\Dashboard\Program;
use App\Models\Dashboard\ProgramParticipant;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

final class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::with(['creator', 'participants.user', 'participants.inviter']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('creator_type')) {
            $query->where('creator_type', $request->input('creator_type'));
        }

        $perPage = (int) $request->input('per_page', 20);

        return ProgramResource::collection($query->orderByDesc('start_date')->paginate($perPage));
    }

    public function show(Program $program)
    {
        return ProgramResource::make($program->load(['creator', 'participants.user', 'participants.inviter', 'addresses']));
    }

    public function store(ProgramRequest $request)
    {
        $user = $request->user();

        if (! in_array($user->type, [UserTypeCast::MENTOR, UserTypeCast::ADVISOR], true)) {
            throw new AuthorizationException('Only mentors or advisors can create programs.');
        }

        $validated = $request->validated();

        $status = $this->resolveProgramStatus($request, $user);

        $program = Program::create([
            'creator_type' => $user->getMorphClass(),
            'creator_id' => $user->getKey(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $status,
        ]);

        if ($address = $request->address()) {
            $program->addresses()->create(array_merge($address, [
                'person_name' => $address['person_name'],
                'person_mobile' => $address['person_mobile'],
                'address_1' => $address['address_1'],
                'city' => $address['city'],
                'postal_code' => $address['postal_code'],
                'country_code' => $address['country_code'],
                'state_code' => $address['state_code'] ?? null,
                'type' => 'service_point',
                'title' => $validated['title'],
            ]));
        }

        if ($participants = $request->participants()) {
            collect($participants)->each(function (array $participant) use ($program, $user) {
                $invitee = User::where('uuid', $participant['uuid'])->first();

                if (! $invitee) {
                    return;
                }

                $record = ProgramParticipant::create([
                    'program_id' => $program->getKey(),
                    'user_id' => $invitee->getKey(),
                    'role' => $participant['role'] ?? 'participant',
                    'status' => 'invited',
                    'invited_by' => $user->getKey(),
                ]);

                $invitee->notify(GeneralNotification::info(
                    'You have been invited to a program',
                    "Program \"{$program->title}\" is scheduled for you.",
                    '/programs'
                ));
            });
        }

        $program->load(['creator', 'participants.user', 'participants.inviter']);

        return ProgramResource::make($program);
    }

    private function resolveProgramStatus(ProgramRequest $request, User $user): string
    {
        if ($user->type === UserTypeCast::ADVISOR) {
            return 'draft';
        }

        $status = $request->status();

        if ($status === 'draft') {
            return 'scheduled';
        }

        return $status;
    }
}
