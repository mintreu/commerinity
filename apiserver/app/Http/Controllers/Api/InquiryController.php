<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessInquiryRequest;
use App\Http\Requests\StoreUserInquiryRequest;
use App\Models\Inquiry;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for contact form submissions.
 *
 * Handles both general user inquiries and business inquiries.
 */
final class InquiryController extends Controller
{
    /**
     * Store a general user inquiry.
     *
     * POST /api/contact/user
     */
    public function storeUser(StoreUserInquiryRequest $request): JsonResponse
    {
        $inquiry = Inquiry::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'is_business' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us. We will get back to you within 24-48 hours.',
            'data' => [
                'id' => $inquiry->id,
            ],
        ], 201);
    }

    /**
     * Store a business inquiry.
     *
     * POST /api/contact/business
     */
    public function storeBusiness(StoreBusinessInquiryRequest $request): JsonResponse
    {
        $inquiry = Inquiry::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'message' => $request->validated('message'),
            'is_business' => true,
            'company_name' => $request->validated('company_name'),
            'address' => $request->validated('address'),
            'website' => $request->validated('website'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your business inquiry. Our team will review your message and contact you within 2-3 business days.',
            'data' => [
                'id' => $inquiry->id,
            ],
        ], 201);
    }
}
