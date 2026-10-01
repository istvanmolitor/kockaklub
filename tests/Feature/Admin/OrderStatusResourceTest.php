<?php

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\OrderStatuses\Pages\CreateOrderStatus;
use App\Filament\Resources\OrderStatuses\Pages\ListOrderStatuses;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('allows an admin to create a new order status and assign it to an order', function () {
    livewire(CreateOrderStatus::class)
        ->fillForm([
            'name' => 'Csomagolás alatt',
            'slug' => 'csomagolas-alatt',
            'color' => 'info',
            'sort_order' => 5,
            'is_default' => false,
            'is_final' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newStatus = OrderStatus::where('slug', 'csomagolas-alatt')->first();
    expect($newStatus)->not->toBeNull();

    $defaultStatus = OrderStatus::factory()->create(['is_default' => true]);
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'order_status_id' => $defaultStatus->id,
    ]);

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->fillForm(['order_status_id' => $newStatus->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->order_status_id)->toBe($newStatus->id);
});

it('prevents deleting an order status that has orders referencing it', function () {
    $status = OrderStatus::factory()->create();
    $customer = Customer::factory()->create();
    Order::factory()->create(['customer_id' => $customer->id, 'order_status_id' => $status->id]);

    livewire(ListOrderStatuses::class)
        ->callAction(TestAction::make('delete')->table($status))
        ->assertNotified();

    expect(OrderStatus::find($status->id))->not->toBeNull();
});
