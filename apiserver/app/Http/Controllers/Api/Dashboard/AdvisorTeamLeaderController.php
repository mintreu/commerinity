<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\AdvisorTeamLeaderRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\KycService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AdvisorTeamLeaderController extends Controller
{
    public function __construct(private readonly KycService $kycService)
    {
    }

    public function store(AdvisorTeamLeaderRequest $request): JsonResponse
    {
        $advisor = $request->user();

        if ($advisor->type !== UserTypeCast::ADVISOR) {
            throw new AuthorizationException('Only advisors can add team leaders.');
        }

        $validated = $request->validated();

        $teamLeader = User::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'status' => UserStatusCast::ACTIVE,
            'type' => UserTypeCast::PROMOTER,
            'onboarded' => true,
            'parent_id' => null,
            'originator_type' => $advisor->getMorphClass(),
            'originator_id' => $advisor->getKey(),
            'mobile_verified_at' => now(),
            'email_verified_at' => $validated['email'] ? now() : null,
            'password' => Hash::make(Str::random(12)),
        ]);

        $address = $request->address();

        $teamLeader->addresses()->create([
            'title' => 'Team Leader Address',
            'person_name' => $address['person_name'],
            'person_mobile' => $address['person_mobile'],
            'address_1' => $address['address_1'],
            'address_2' => $address['address_2'] ?? null,
            'city' => $address['city'],
            'postal_code' => $address['postal_code'],
            'country_code' => $address['country_code'],
            'state_code' => $address['state_code'] ?? null,
            'type' => 'home',
            'default' => true,
        ]);

        $this->kycService->submitKyc($teamLeader, [
            'kyc_type' => $validated['kyc_type'],
            'pan_number' => $validated['pan_number'],
            'aadhaar_number' => $validated['aadhaar_number'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_type' => $validated['company_type'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            $teamLeader->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        if ($beneficiary = $request->beneficiary()) {
            $teamLeader->loadMissing('wallet');

            $teamLeader->beneficiaryAccounts()->create([
                'wallet_id' => $teamLeader->wallet?->getKey(),
                'type' => $beneficiary['type'],
                'account_number' => $beneficiary['account_number'],
                'ifsc_code' => $beneficiary['ifsc_code'] ?? null,
                'bank_name' => $beneficiary['bank_name'] ?? null,
                'holder_name' => $beneficiary['holder_name'],
                'upi_id' => $beneficiary['upi_id'] ?? null,
                'status' => BeneficiaryStatusCast::PENDING,
                'metadata' => ['added_by' => $advisor->uuid],
            ]);
        }

        $teamLeader->notify(GeneralNotification::success(
            'Team leader onboarded',
            'You have been added to the team. Complete your onboarding to access dashboards.',
            '/dashboard'
        ));

        $advisor->notify(GeneralNotification::info(
            'Team leader created',
            "{$teamLeader->name} has been added under your guidance.",
            '/dashboard/team'
        ));

        return UserResource::make($teamLeader->load(['addresses', 'kyc']))->response()->setStatusCode(201);
    }
}
