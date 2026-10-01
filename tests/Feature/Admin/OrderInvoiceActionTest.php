<?php

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Services\SzamlazzService;
use Filament\Actions\Testing\TestAction;
use Omisai\Szamlazzhu\SzamlaAgentException;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('issues an invoice for an order through the admin action', function () {
    $this->app->bind(SzamlazzService::class, fn () => new class extends SzamlazzService
    {
        public function issueInvoice(Order $order): Order
        {
            $order->update([
                'invoice_number' => 'TESTSZ-2026-1',
                'invoiced_at' => now(),
                'invoice_pdf_path' => null,
            ]);

            return $order;
        }
    });

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
    ]);

    livewire(ListOrders::class)
        ->callAction(TestAction::make('issueInvoice')->table($order))
        ->assertNotified();

    expect($order->fresh())
        ->invoice_number->toBe('TESTSZ-2026-1')
        ->isInvoiced()->toBeTrue();
});

it('shows an error notification when invoice issuing fails', function () {
    $this->app->bind(SzamlazzService::class, fn () => new class extends SzamlazzService
    {
        public function issueInvoice(Order $order): Order
        {
            throw new SzamlaAgentException('Hiányzó Agent kulcs.');
        }
    });

    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $status->id,
    ]);

    livewire(ListOrders::class)
        ->callAction(TestAction::make('issueInvoice')->table($order))
        ->assertNotified();

    expect($order->fresh()->isInvoiced())->toBeFalse();
});
