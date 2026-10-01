<?php

use App\Models\OrderStatus;

it('unsets the previous default order status when a new one is marked default', function () {
    $first = OrderStatus::factory()->create(['is_default' => true]);
    $second = OrderStatus::factory()->create(['is_default' => false]);

    $second->update(['is_default' => true]);

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($second->fresh()->is_default)->toBeTrue();
});
