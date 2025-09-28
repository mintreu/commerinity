<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\BeneficiaryAccountResource;
use Illuminate\Http\Request;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountStatusCast;
use Mintreu\LaravelTransaction\Models\BeneficiaryAccount;

class BeneficiaryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('wallet');
        $list = $user->beneficiaryAccounts()->latest()->paginate(10);
        return BeneficiaryAccountResource::collection($list);
    }

    public function show(BeneficiaryAccount $account, Request $request)
    {
        $this->authorizeOwner($account, $request);
        return BeneficiaryAccountResource::make($account);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $user->load('wallet');

        $validated = $request->validate([
            'type'           => ['required', 'in:savings,current,upi'],
            'bank_name'      => ['nullable','string','max:120'],
            'bank_branch'    => ['nullable','string','max:120'],
            'account_name'   => ['nullable','string','max:120'],
            'account_number' => ['nullable','string','max:64'],
            'ifsc'           => ['nullable','string','max:32'],
            'upi_handle'     => ['nullable','string','max:120'],
            'default'        => ['sometimes','boolean'],
        ]);

        // Basic field checks per type
        if (in_array($validated['type'], ['savings','current'])) {
            $request->validate([
                'account_name'   => ['required','string'],
                'account_number' => ['required','string'],
                'ifsc'           => ['required','string'],
                'bank_name'      => ['required','string'],
            ]);
        } else { // upi
            $request->validate([
                'upi_handle' => ['required','string'],
            ]);
        }

        $beneficiary = $user->beneficiaryAccounts()->create([
            ...$validated,
            'wallet_id' => $user->wallet?->id,
            'status' => BeneficiaryAccountStatusCast::PENDING
        ]);

        if ($request->boolean('default')) {
            $user->setDefaultBeneficiary($beneficiary);
            $beneficiary->refresh();
        }

        return BeneficiaryAccountResource::make($beneficiary);
    }

    public function update(BeneficiaryAccount $account, Request $request)
    {
        $this->authorizeOwner($account, $request);

        $validated = $request->validate([
            'bank_name'      => ['nullable','string','max:120'],
            'bank_branch'    => ['nullable','string','max:120'],
            'account_name'   => ['nullable','string','max:120'],
            'account_number' => ['nullable','string','max:64'],
            'ifsc'           => ['nullable','string','max:32'],
            'upi_handle'     => ['nullable','string','max:120'],
            'default'        => ['sometimes','boolean'],
        ]);

        $account->update($validated);

        if ($request->has('default')) {
            $owner = $request->user();
            if ($request->boolean('default')) {
                $owner->setDefaultBeneficiary($account);
            } else {
                // If unsetting default, ensure at least one default remains or allow none
                $account->update(['default' => false]);
            }
        }

        return BeneficiaryAccountResource::make($account->fresh());
    }

    public function destroy(BeneficiaryAccount $account, Request $request)
    {
        $this->authorizeOwner($account, $request);
        $account->delete();

        return response()->json([
            'data' => ['success' => true, 'message' => 'Beneficiary Account Deleted!']
        ]);
    }

    public function makeDefault(BeneficiaryAccount $account, Request $request)
    {
        $this->authorizeOwner($account, $request);
        $account->makeDefault();
//        $owner = $request->user();
//        $owner->setDefaultBeneficiary($account);
        return BeneficiaryAccountResource::make($account->fresh());
    }

    protected function authorizeOwner(BeneficiaryAccount $account, Request $request): void
    {
        $user = $request->user();
        abort_if($account->accountable_type !== get_class($user) || $account->accountable_id !== $user->getKey(), 403, 'Forbidden');
    }
}
