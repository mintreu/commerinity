<?php

declare(strict_types=1);

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Inquiry', function () {
    it('stores a user inquiry with valid data', function () {
        $response = $this->postJson('/api/contact/user', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+91 98765 43210',
            'subject' => 'General Inquiry',
            'message' => 'This is a test message for the contact form.',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Thank you for contacting us. We will get back to you within 24-48 hours.',
            ]);

        $this->assertDatabaseHas('inquiries', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_business' => false,
        ]);
    });

    it('stores a user inquiry without optional fields', function () {
        $response = $this->postJson('/api/contact/user', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'This is a test message without optional fields.',
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => null,
            'subject' => null,
        ]);
    });

    it('validates required fields for user inquiry', function () {
        $response = $this->postJson('/api/contact/user', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    });

    it('validates email format for user inquiry', function () {
        $response = $this->postJson('/api/contact/user', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'message' => 'Test message here.',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('validates message minimum length for user inquiry', function () {
        $response = $this->postJson('/api/contact/user', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    });

    it('validates message maximum length for user inquiry', function () {
        $response = $this->postJson('/api/contact/user', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => str_repeat('a', 5001),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    });
});

describe('Business Inquiry', function () {
    it('stores a business inquiry with valid data', function () {
        $response = $this->postJson('/api/contact/business', [
            'name' => 'John Smith',
            'email' => 'john@company.com',
            'phone' => '+91 98765 43210',
            'company_name' => 'Acme Inc.',
            'address' => '123 Business Street, City, Country',
            'website' => 'https://acme.com',
            'message' => 'We are interested in a business partnership.',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Thank you for your business inquiry. Our team will review your message and contact you within 2-3 business days.',
            ]);

        $this->assertDatabaseHas('inquiries', [
            'name' => 'John Smith',
            'email' => 'john@company.com',
            'company_name' => 'Acme Inc.',
            'is_business' => true,
        ]);
    });

    it('stores a business inquiry without optional website', function () {
        $response = $this->postJson('/api/contact/business', [
            'name' => 'Jane Smith',
            'email' => 'jane@company.com',
            'phone' => '+91 12345 67890',
            'company_name' => 'Tech Corp',
            'address' => '456 Tech Lane',
            'message' => 'We need bulk pricing information.',
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('inquiries', [
            'company_name' => 'Tech Corp',
            'website' => null,
        ]);
    });

    it('validates required fields for business inquiry', function () {
        $response = $this->postJson('/api/contact/business', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'phone',
                'company_name',
                'address',
                'message',
            ]);
    });

    it('validates website url format for business inquiry', function () {
        $response = $this->postJson('/api/contact/business', [
            'name' => 'John Smith',
            'email' => 'john@company.com',
            'phone' => '+91 98765 43210',
            'company_name' => 'Acme Inc.',
            'address' => '123 Business Street',
            'website' => 'not-a-valid-url',
            'message' => 'Test business inquiry message.',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['website']);
    });

    it('validates email format for business inquiry', function () {
        $response = $this->postJson('/api/contact/business', [
            'name' => 'John Smith',
            'email' => 'invalid-email',
            'phone' => '+91 98765 43210',
            'company_name' => 'Acme Inc.',
            'address' => '123 Business Street',
            'message' => 'Test business inquiry message.',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('validates message minimum length for business inquiry', function () {
        $response = $this->postJson('/api/contact/business', [
            'name' => 'John Smith',
            'email' => 'john@company.com',
            'phone' => '+91 98765 43210',
            'company_name' => 'Acme Inc.',
            'address' => '123 Business Street',
            'message' => 'Short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    });
});

describe('Inquiry Model', function () {
    it('marks inquiry as replied', function () {
        $inquiry = Inquiry::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Test message content.',
        ]);

        expect($inquiry->status)->toBe('pending');
        expect($inquiry->replied_at)->toBeNull();

        $inquiry->markAsReplied();

        expect($inquiry->fresh()->status)->toBe('replied');
        expect($inquiry->fresh()->replied_at)->not->toBeNull();
    });

    it('filters pending inquiries', function () {
        Inquiry::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'message' => 'Pending inquiry.',
            'status' => 'pending',
        ]);

        Inquiry::create([
            'name' => 'Replied User',
            'email' => 'replied@example.com',
            'message' => 'Replied inquiry.',
            'status' => 'replied',
        ]);

        $pending = Inquiry::pending()->get();

        expect($pending)->toHaveCount(1);
        expect($pending->first()->email)->toBe('pending@example.com');
    });

    it('filters business inquiries', function () {
        Inquiry::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'message' => 'User inquiry.',
            'is_business' => false,
        ]);

        Inquiry::create([
            'name' => 'Business',
            'email' => 'business@example.com',
            'message' => 'Business inquiry.',
            'is_business' => true,
            'company_name' => 'Test Corp',
        ]);

        $business = Inquiry::business()->get();
        $general = Inquiry::general()->get();

        expect($business)->toHaveCount(1);
        expect($general)->toHaveCount(1);
        expect($business->first()->email)->toBe('business@example.com');
        expect($general->first()->email)->toBe('user@example.com');
    });
});
