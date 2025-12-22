<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mintreu\LaravelHelpdesk\Models\Inquiry;

class InquiryController extends Controller
{


    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required','string','max:120'],
            'email'   => ['required','email','max:190'],
            'message' => ['required','string','max:5000'],
        ]);

        $contact = Inquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message received',
            'data' => $contact,
        ], 201);
    }

    public function storeBusiness(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required','string','max:120'],
            'company_name' => ['required','string','max:190'],
            'address'      => ['required','string','max:255'],
            'email'        => ['required','email','max:190'],
            'phone'        => ['required','string','max:50'],
            'website'      => ['nullable','url','max:255'],
            'message'      => ['required','string','max:5000'],
        ]);

        $contact = Inquiry::create([
            'is_business'  => true,
            'company_name' => $validated['company_name'],
            'address'      => $validated['address'],
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'],
            'website'      => $validated['website'] ?? null,
            'message'      => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business inquiry received',
            'data' => $contact,
        ], 201);
    }


}
