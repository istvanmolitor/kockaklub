<?php

use App\Models\Site;

it('unsets the previous main site when a new one is marked main', function () {
    $first = Site::factory()->create(['is_main' => true]);
    $second = Site::factory()->create(['is_main' => false]);

    $second->update(['is_main' => true]);

    expect($first->fresh()->is_main)->toBeFalse()
        ->and($second->fresh()->is_main)->toBeTrue();
});
