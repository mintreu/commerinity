<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Casts\UserTypeCast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\AppointmentRequest;
use App\Http\Resources\Dashboard\AppointmentResource;
use App\Models\Dashboard\Appointment;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

final class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Appointment::with(['advisor', 'mentor', 'attendee', 'creator'])
            ->where(function ($builder) use ($user) {
                $builder->where('attendee_user_id', $user->id)
                    ->orWhere('advisor_id', $user->id)
                    ->orWhere('mentor_id', $user->id);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('creator_type')) {
            $query->where('creator_type', $request->input('creator_type'));
        }

        $perPage = (int) $request->input('per_page', 20);

        return AppointmentResource::collection($query->orderByDesc('start_at')->paginate($perPage));
    }

    public function show(Appointment $appointment)
    {
        return AppointmentResource::make($appointment->load(['advisor', 'mentor', 'attendee', 'creator']));
    }

    public function store(AppointmentRequest $request)
    {
        $user = $request->user();

        $validated = $request->validated();

        $attendee = $this->resolveAttendee($request);

        $advisor = $this->resolveAdvisor($request, $user);
        $mentor = $this->resolveMentor($request, $user);

        if ($attendee instanceof User && $user->type === UserTypeCast::ADVISOR && ! $attendee->originator?->is($user)) {
            throw new AuthorizationException('You can only schedule appointments for users assigned to your advisor originator tree.');
        }

        $appointment = Appointment::create([
            'creator_type' => $user->getMorphClass(),
            'creator_id' => $user->getKey(),
            'attendee_user_id' => $attendee instanceof User ? $attendee->getKey() : null,
            'advisor_id' => $advisor?->getKey(),
            'mentor_id' => $mentor?->getKey(),
            'title' => $validated['title'],
            'agenda' => $validated['agenda'] ?? null,
            'meeting_mode' => $validated['meeting_mode'],
            'meeting_link' => $validated['meeting_link'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'] ?? null,
            'status' => 'pending',
        ]);

        $participantUuids = array_filter(
            ($request->participants() ?: []),
            fn ($uuid) => ! $attendee instanceof User || $uuid !== $attendee->uuid
        );

        foreach ($participantUuids as $participantUuid) {
            $participantUser = User::where('uuid', $participantUuid)->first();
            if (! $participantUser) {
                continue;
            }

            $appointment->participants()->create([
                'user_id' => $participantUser->getKey(),
                'role' => 'participant',
            ]);
        }

        $appointment->load(['advisor', 'mentor', 'attendee', 'creator']);

        $notificationPayload = function (User $target, string $role) use ($appointment) {
            $when = $appointment->start_at?->format('d M Y H:i');

            return GeneralNotification::info(
                'New appointment scheduled',
                "{$role} appointment on {$when}",
                '/appointments'
            );
        };

        if ($attendee instanceof User) {
            $attendee->notify($notificationPayload($attendee, 'You have'));
        }

        if ($advisor && ! $advisor->is($user)) {
            $advisor->notify($notificationPayload($advisor, 'Advisor'));
        }

        if ($mentor && ! $mentor->is($user)) {
            $mentor->notify($notificationPayload($mentor, 'Mentor'));
        }

        return AppointmentResource::make($appointment);
    }

    private function resolveAdvisor(AppointmentRequest $request, User $user): ?User
    {
        if ($user->type === UserTypeCast::ADVISOR) {
            return $user;
        }

        if (! $uuid = $request->advisorUuid()) {
            return null;
        }

        $advisor = User::where('uuid', $uuid)->where('type', UserTypeCast::ADVISOR)->first();

        return $advisor;
    }

    private function resolveAttendee(AppointmentRequest $request): User|Admin
    {
        $type = $request->attendeeType();
        $uuid = $request->attendeeUuid();
        $contact = $request->attendeeContact();

        if ($type === 'admin') {
            if ($uuid) {
                return Admin::where('uuid', $uuid)->firstOrFail();
            }

            if ($contact) {
                $admin = Admin::where('mobile', $contact)
                    ->orWhere('email', $contact)
                    ->orWhere('name', 'like', "%{$contact}%")
                    ->first();

                if ($admin) {
                    return $admin;
                }
            }

            abort(404, 'Admin attendee not found');
        }

        if ($uuid) {
            return User::where('uuid', $uuid)->firstOrFail();
        }

        if ($contact) {
            $attendee = User::where('mobile', $contact)
                ->orWhere('email', $contact)
                ->orWhere('referral_code', $contact)
                ->orWhere('name', 'like', "%{$contact}%")
                ->first();

            if ($attendee) {
                return $attendee;
            }
        }

        abort(404, 'User attendee not found');
    }

    private function resolveMentor(AppointmentRequest $request, User $user): ?User
    {
        if ($user->type === UserTypeCast::MENTOR) {
            return $user;
        }

        if (! $uuid = $request->mentorUuid()) {
            return null;
        }

        return User::where('uuid', $uuid)->where('type', UserTypeCast::MENTOR)->first();
    }

    public function searchUsers(Request $request)
    {
        $query = trim($request->input('q', ''));
        $type = $request->input('type', 'user');
        $scope = $request->input('scope', 'all');

        if (! $query) {
            return response()->json(['data' => []]);
        }

        if ($type === 'admin') {
            $admins = Admin::where(function ($builder) use ($query) {
                $builder->where('mobile', $query)
                    ->orWhere('email', $query)
                    ->orWhere('name', 'like', "%{$query}%");
            })
                ->limit(20)
                ->get(['uuid', 'name', 'mobile', 'email']);

            return response()->json([
                'data' => $admins->map(fn (Admin $admin) => [
                    'uuid' => $admin->uuid,
                    'label' => $admin->name,
                    'details' => $admin->mobile ?: $admin->email,
                    'type' => 'admin',
                ])
            ]);
        }

        $users = User::where(function ($builder) use ($query) {
            $builder->where('mobile', $query)
                ->orWhere('email', $query)
                ->orWhere('referral_code', $query)
                ->orWhere('name', 'like', "%{$query}%");
        });

        if ($scope === 'team') {
            $requester = $request->user();
            if ($requester->type === UserTypeCast::ADVISOR) {
                $users->where('originator_type', $requester->getMorphClass())
                    ->where('originator_id', $requester->getKey());
            } elseif (in_array($requester->type, [UserTypeCast::MEMBER, UserTypeCast::PROMOTER], true)) {
                $users->where('parent_id', $requester->getKey());
            }
        }

        $users = $users->whereNotIn('type', [UserTypeCast::REGULAR])
            ->limit(20)
            ->get(['uuid', 'name', 'mobile', 'email', 'type']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'uuid' => $user->uuid,
                'label' => $user->name,
                'details' => $user->mobile ?: $user->email,
                'type' => $user->type->value,
            ])
        ]);
    }

    public function attendeeTypes()
    {
        return response()->json([
            'data' => [
                [
                    'value' => 'admin',
                    'label' => 'Company',
                    'model' => Admin::class,
                ],
                [
                    'value' => 'user',
                    'label' => 'Users',
                    'model' => User::class,
                ],
            ],
        ]);
    }
}
