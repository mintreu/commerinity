<?php

declare(strict_types=1);

use App\Models\Ecommerce\Order;
use App\Models\User;
use App\Notifications\Ecommerce\OrderInvoiceNotification;
use App\Services\Ecommerce\InvoiceService;

test('order invoice notification mail contains pdf attachment', function () {
    $order = new Order([
        'uuid' => 'ORD-UUID-TEST',
        'order_number' => 'ORD-TEST-1001',
    ]);

    app()->instance(InvoiceService::class, new class
    {
        public function pdf(Order $order): object
        {
            return new class
            {
                public function output(): string
                {
                    return '%PDF-test-content%';
                }
            };
        }
    });

    $user = new User([
        'name' => 'Regular User',
        'email' => 'regular@demo.com',
    ]);

    $notification = new OrderInvoiceNotification($order);
    $mail = $notification->toMail($user);

    expect($mail->rawAttachments)->toHaveCount(1);
    expect($mail->rawAttachments[0]['name'])->toBe('invoice-ORD-TEST-1001.pdf');
    expect($mail->rawAttachments[0]['options']['mime'])->toBe('application/pdf');
});

test('order invoice notification includes database and mail channels for email users', function () {
    $order = new Order([
        'uuid' => 'ORD-UUID-TEST-2',
        'order_number' => 'ORD-TEST-1002',
    ]);

    $user = new User([
        'name' => 'Regular User',
        'email' => 'regular@demo.com',
    ]);

    $notification = new OrderInvoiceNotification($order);
    $channels = $notification->via($user);

    expect($channels)->toContain('database');
    expect($channels)->toContain('mail');
});
