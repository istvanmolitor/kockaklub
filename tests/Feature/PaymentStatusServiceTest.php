<?php

use App\Enums\PaymentStatus;
use App\Mail\PaymentUpdateMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymentStatusService;
use Illuminate\Support\Facades\Mail;

it('changes the order payment status and sends an email to the customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'vasarlo@example.com']);
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'payment_status' => PaymentStatus::Pending,
    ]);

    app(PaymentStatusService::class)->setStatus($order, PaymentStatus::Paid);

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->paid_at)->not->toBeNull();

    Mail::assertQueued(
        PaymentUpdateMail::class,
        fn ($mail) => $mail->hasTo('vasarlo@example.com')
            && $mail->order->is($order)
            && $mail->payment === null
            && $mail->previousStatus === PaymentStatus::Pending,
    );
});

it('does not send an email when the payment status does not change', function () {
    Mail::fake();

    $order = Order::factory()->create(['payment_status' => PaymentStatus::Paid]);

    app(PaymentStatusService::class)->setStatus($order, PaymentStatus::Paid);

    Mail::assertNothingQueued();
});

it('clears paid_at when the status changes away from paid', function () {
    Mail::fake();

    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'paid_at' => now(),
    ]);

    app(PaymentStatusService::class)->setStatus($order, PaymentStatus::Cancelled);

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Cancelled)
        ->paid_at->toBeNull();
});

it('records an incoming payment for an order and notifies the customer', function () {
    Mail::fake();

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->create(['email' => 'vasarlo@example.com']);
    $order = Order::factory()->create(['customer_id' => $customer->id]);

    $payment = app(PaymentStatusService::class)->recordPayment(
        $order,
        5000,
        note: 'Banki átutalás',
    );

    expect($payment->order_id)->toBe($order->id)
        ->and($payment->amount)->toBe(5000)
        ->and($payment->note)->toBe('Banki átutalás')
        ->and($payment->user_id)->toBe($user->id);

    expect($order->payments)->toHaveCount(1);

    Mail::assertQueued(
        PaymentUpdateMail::class,
        fn ($mail) => $mail->hasTo('vasarlo@example.com')
            && $mail->order->is($order)
            && $mail->payment->is($payment)
            && $mail->previousStatus === null,
    );
});

it('records a payment and changes the status in a single notification', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'vasarlo@example.com']);
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'payment_status' => PaymentStatus::Pending,
        'total' => 10000,
    ]);

    app(PaymentStatusService::class)->recordPaymentAndSetStatus($order, 10000, PaymentStatus::Paid);

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->payments)->toHaveCount(1);

    Mail::assertQueued(
        PaymentUpdateMail::class,
        fn ($mail) => $mail->order->is($order)
            && $mail->payment?->amount === 10000
            && $mail->previousStatus === PaymentStatus::Pending,
    );
    Mail::assertQueuedCount(1);
});
