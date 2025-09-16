<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\KycResource;
use App\Http\Resources\User\UserIndexResource;
use App\Http\Resources\User\UserResource;
use App\Models\Transaction\Kyc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserKycController extends Controller
{
    public function getUserKyc(Request $request)
    {
        $user = $request->user();

        $user->load(['kyc' => fn($q) => $q->with('media')]);

       // return UserResource::make($user);

        return $user?->kyc ? KycResource::make($user->kyc) : response()->json([
            'message'   => 'no kyc info found',
            'data' => []
        ]);
    }

    public function addUserKyc(Request $request)
    {

//        $validate = $request->validate([
//            'aadhaar'       => ['required', 'regex:/^\d{12}$/'],
//            'pan'           => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
//            'has_tax'       => ['required', 'in:0,1'],
//            'gst'           => ['nullable', 'regex:/^[0-9A-Z]{15}$/'],
//            'utility_bills' => ['nullable'],
//            'aadhaar_file'  => ['nullable', 'file', 'mimes:jpeg,png', 'max:5120'],
//            'pan_file'      => ['nullable', 'file', 'mimes:jpeg,png', 'max:5120'],
//            'gst_file'      => ['nullable', 'file', 'mimes:jpeg,png', 'max:5120'],
//        ]);



        $user = $request->user();

        $data = $this->validatePayload($request);

        // utility_bills from JSON string or array
        $utility = $this->normalizeUtilityBills($request->input('utility_bills'));



        return DB::transaction(function () use ($user, $data, $request, $utility) {
            /** @var Kyc $kyc */
            $kyc = $user->kyc()->create([
                'user_type'     => $user->type ?? 'regular',
                'company_name'  => $request->input('company_name'),
                'company_type'  => $request->input('company_type'),
                'has_tax'       => $data['has_tax'],
                'aadhaar'       => $data['aadhaar'],
                'pan'           => $data['pan'],
                'gst'           => $data['gst'] ?? null,
                'utility_bills' => $utility,
            ]);

            // Media uploads (Spatie)
            if ($request->hasFile('aadhaar_file')) {
                $kyc->clearMediaCollection('aadhaarImage');
                $kyc->addMedia($request->file('aadhaar_file'))->toMediaCollection('aadhaarImage');
            }
            if ($request->hasFile('pan_file')) {
                $kyc->clearMediaCollection('panImage');
                $kyc->addMedia($request->file('pan_file'))->toMediaCollection('panImage');
            }
            if ($request->hasFile('gst_file')) {
                $kyc->clearMediaCollection('gstImage');
                $kyc->addMedia($request->file('gst_file'))->toMediaCollection('gstImage');
            }

            $kyc->load('media');

            return response()->json([
                'success' => true,
                'data'    => KycResource::make($kyc),
            ]);
        });
    }

    public function updateUserKyc(Request $request)
    {
        // Alias of addUserKyc allowing partial updates; same validator but fields nullable
        $user = $request->user();

        $rules = [
            'aadhaar' => ['nullable','regex:/^\d{12}$/'],
            'pan'     => ['nullable','regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'has_tax' => ['nullable', Rule::in(['0','1',0,1,true,false])],
            'gst'     => ['nullable','regex:/^[0-9A-Z]{15}$/'],
            'utility_bills' => ['nullable'], // JSON string or array
            'aadhaar_file'  => ['nullable','file','mimetypes:image/jpeg,image/png,application/pdf','max:5120'], // 5MB
            'pan_file'      => ['nullable','file','mimetypes:image/jpeg,image/png,application/pdf','max:5120'],
            'gst_file'      => ['nullable','file','mimetypes:image/jpeg,image/png,application/pdf','max:5120'],
        ];
        $validated = $request->validate($rules);

        $utility = $this->normalizeUtilityBills($request->input('utility_bills'));

        return DB::transaction(function () use ($user, $request, $validated, $utility) {
            /** @var Kyc $kyc */
            $kyc = $user->kyc()->firstOrCreate([], [
                'user_type' => $user->type ?? 'regular',
            ]);

            // Only update provided fields
            if (array_key_exists('aadhaar', $validated) && $validated['aadhaar'] !== null) {
                $kyc->aadhaar = $validated['aadhaar'];
            }
            if (array_key_exists('pan', $validated) && $validated['pan'] !== null) {
                $kyc->pan = strtoupper($validated['pan']);
            }
            if (array_key_exists('has_tax', $validated)) {
                $kyc->has_tax = (bool) ($validated['has_tax']);
            }
            if (array_key_exists('gst', $validated)) {
                $kyc->gst = $validated['gst'] ? strtoupper($validated['gst']) : null;
            }
            if (!is_null($utility)) {
                $kyc->utility_bills = $utility;
            }

            $kyc->save();

            if ($request->hasFile('aadhaar_file')) {
                $kyc->clearMediaCollection('aadhaarImage');
                $kyc->addMedia($request->file('aadhaar_file'))->toMediaCollection('aadhaarImage');
            }
            if ($request->hasFile('pan_file')) {
                $kyc->clearMediaCollection('panImage');
                $kyc->addMedia($request->file('pan_file'))->toMediaCollection('panImage');
            }
            if ($request->hasFile('gst_file')) {
                $kyc->clearMediaCollection('gstImage');
                $kyc->addMedia($request->file('gst_file'))->toMediaCollection('gstImage');
            }

            $kyc->load('media');

            return response()->json([
                'success' => true,
                'data'    => KycResource::make($kyc),
            ]);
        });
    }

    protected function validatePayload(Request $request): array
    {
        $rules = [
            'aadhaar' => ['required','regex:/^\d{12}$/'],
            'pan'     => ['required','regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'has_tax' => ['required', Rule::in(['0','1',0,1,true,false])],
            'gst'     => ['nullable','regex:/^[0-9A-Z]{15}$/'],
            'utility_bills' => ['nullable'], // JSON string or array
            'aadhaar_file'  => ['nullable','file','mimetypes:image/jpeg,image/png,application/pdf','max:5120'],
            'pan_file'      => ['nullable','file','mimetypes:image/jpeg,image/png,application/pdf','max:5120'],
            'gst_file'      => ['nullable','file','mimetypes:image/jpeg,image/png,application/pdf','max:5120'],
        ];

        $validated = $request->validate($rules);

        // if has_tax truthy then gst required
        $hasTax = (bool) ($validated['has_tax'] ?? false);
        if ($hasTax && empty($validated['gst'])) {
            throw ValidationException::withMessages([
                'gst' => 'GST is required when has_tax is true.',
            ]);
        }

        // Uppercase normalizations
        if (!empty($validated['pan'])) $validated['pan'] = strtoupper($validated['pan']);
        if (!empty($validated['gst'])) $validated['gst'] = strtoupper($validated['gst']);

        return $validated;
    }

    protected function normalizeUtilityBills($value): ?array
    {
        if (is_null($value) || $value === '') return null;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                return null;
            }
        }
        if (!is_array($value)) return null;

        // each item: { type: string, value: string }
        $clean = [];
        foreach ($value as $row) {
            $type = isset($row['type']) ? (string) $row['type'] : null;
            $val  = isset($row['value']) ? trim((string) $row['value']) : null;
            if ($type && $val) $clean[] = ['type' => $type, 'value' => $val];
        }
        return $clean;
    }
}
