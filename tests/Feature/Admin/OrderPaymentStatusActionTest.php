<?php

use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Mail\PaymentUpdateMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('defaults the amount to the outstanding balance, the date to now and the checkbox to the current paid state', function () {
    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
        'payment_status' => PaymentStatus::Pending,
        'subtotal' => 10000,
        'total' => 10000,
    ]);
    $order->payments()->create(['amount' => 3000, 'received_at' => now()]);

    livewire(ListOrders::class)
        ->mountAction(TestAction::make('managePaymentStatus')->table($order))
        ->assertActionDataSet([
            'amount' => 7000,
            'mark_as_paid' => false,
        ]);
});

it('marks the order as paid when the checkbox is checked', function () {
    Mail::fake();

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
        'payment_status' => PaymentStatus::Pending,
    ]);

    livewire(ListOrders::class)
        ->callAction(
            TestAction::make('managePaymentStatus')->table($order),
            data: ['mark_as_paid' => true, 'amount' => 0],
        )
        ->assertNotified();

    expect($order->fresh())->payment_status->toBe(PaymentStatus::Paid);

    Mail::assertQueued(PaymentUpdateMail::class, fn ($mail) => $mail->previousStatus === PaymentStatus::Pending && $mail->payment === null);
});

it('leaves the payment status untouched when the checkbox is left unchecked, but still notifies about the recorded payment', function () {
    Mail::fake();

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
        'payment_status' => PaymentStatus::Pending,
    ]);

    livewire(ListOrders::class)
        ->callAction(
            TestAction::make('managePaymentStatus')->table($order),
            data: ['mark_as_paid' => false, 'amount' => 5000, 'note' => 'Előleg'],
        )
        ->assertNotified();

    expect($order->fresh())->payment_status->toBe(PaymentStatus::Pending);

    Mail::assertQueued(PaymentUpdateMail::class, fn ($mail) => $mail->previousStatus === null && $mail->payment?->amount === 5000);
    Mail::assertQueuedCount(1);
});

it('does not record a payment when the outstanding balance is zero and the amount is left untouched', function () {
    Mail::fake();

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
        'payment_status' => PaymentStatus::Paid,
        'paid_at' => now(),
    ]);
    $order->payments()->create(['amount' => $order->total, 'received_at' => now()]);

    livewire(ListOrders::class)
        ->callAction(
            TestAction::make('managePaymentStatus')->table($order),
            data: ['mark_as_paid' => true, 'amount' => 0],
        )
        ->assertNotified();

    expect($order->payments()->count())->toBe(1);

    Mail::assertNothingQueued();
});

it('records an incoming payment when an amount is provided', function () {
    Mail::fake();

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
        'payment_status' => PaymentStatus::Pending,
    ]);

    livewire(ListOrders::class)
        ->callAction(
            TestAction::make('managePaymentStatus')->table($order),
            data: [
                'mark_as_paid' => false,
                'amount' => 5000,
                'note' => 'Előleg',
            ],
        )
        ->assertNotified();

    $order->refresh();

    expect($order->payments)->toHaveCount(1);
    expect($order->payments->first())
        ->amount->toBe(5000)
        ->note->toBe('Előleg');

    Mail::assertQueued(PaymentUpdateMail::class);
});

it('sends a single combined notification when a payment is recorded and the order is marked paid together', function () {
    Mail::fake();

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
        'payment_status' => PaymentStatus::Pending,
        'total' => 10000,
    ]);

    livewire(ListOrders::class)
        ->callAction(
            TestAction::make('managePaymentStatus')->table($order),
            data: ['mark_as_paid' => true, 'amount' => 10000],
        )
        ->assertNotified();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->payments)->toHaveCount(1);

    Mail::assertQueued(
        PaymentUpdateMail::class,
        fn ($mail) => $mail->payment?->amount === 10000 && $mail->previousStatus === PaymentStatus::Pending,
    );
    Mail::assertQueuedCount(1);
});
