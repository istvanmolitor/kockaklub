<?php

use App\Filament\Resources\Contents\Pages\CreateContent;
use App\Models\Content;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('allows an admin to create a content with ordered blocks', function () {
    livewire(CreateContent::class)
        ->fillForm([
            'title' => 'Általános szerződési feltételek',
            'slug' => 'altalanos-szerzodesi-feltetelek',
            'blocks' => [
                ['body' => 'Első blokk'],
                ['body' => 'Második blokk'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $content = Content::where('slug', 'altalanos-szerzodesi-feltetelek')->first();

    expect($content)->not->toBeNull();
    expect($content->blocks()->pluck('body')->all())->toEqual(['<p>Első blokk</p>', '<p>Második blokk</p>']);
    expect($content->blocks()->orderBy('sort_order')->pluck('sort_order')->all())->toEqual([1, 2]);
});
