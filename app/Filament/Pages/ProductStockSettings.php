<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Region;
use App\Models\RegionProductSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class ProductStockSettings extends Page
{
    protected static ?string $slug = 'keszletlista/{record}/beallitas';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.product-stock-settings';

    public Product $record;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(Product $record): void
    {
        $this->record = $record;

        $existingSettings = $record->regionSettings()->get()->keyBy('region_id');

        $this->form->fill([
            'settings' => $this->regions()
                ->mapWithKeys(fn (Region $region): array => [
                    $region->id => [
                        'region_id' => $region->id,
                        'min_stock' => $existingSettings->get($region->id)?->min_stock,
                        'max_stock' => $existingSettings->get($region->id)?->max_stock,
                    ],
                ])
                ->toArray(),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return "Készletbeállítás – {$this->record->name}";
    }

    /** @return Collection<int, Region> */
    protected function regions(): Collection
    {
        return Region::query()->with('site')->orderBy('name')->get();
    }

    public function form(Schema $schema): Schema
    {
        $regions = $this->regions()->keyBy('id');

        return $schema
            ->components([
                Repeater::make('settings')
                    ->label('Régiónkénti minimum és maximum készlet')
                    ->schema([
                        Hidden::make('region_id'),
                        TextInput::make('min_stock')
                            ->label('Minimum')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_stock')
                            ->label('Maximum')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemLabel(function (array $state) use ($regions): ?string {
                        $region = $regions->get($state['region_id'] ?? null);

                        if (! $region) {
                            return null;
                        }

                        return trim("{$region->site?->name} – {$region->name}", ' –');
                    }),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Vissza a készletlistához')
                ->color('gray')
                ->url(StockOverview::getUrl()),
            Action::make('save')
                ->label('Mentés')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state['settings'] as $row) {
            RegionProductSetting::updateOrCreate(
                [
                    'region_id' => $row['region_id'],
                    'product_id' => $this->record->id,
                ],
                [
                    'min_stock' => $row['min_stock'],
                    'max_stock' => $row['max_stock'],
                ],
            );
        }

        Notification::make()
            ->title('A készletbeállítások mentve')
            ->success()
            ->send();
    }
}
