<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MaintenanceModeSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Beállítások';

    protected static ?string $navigationLabel = 'Karbantartási mód';

    protected static ?string $title = 'Karbantartási mód';

    protected static ?string $slug = 'karbantartasi-mod';

    protected string $view = 'filament.pages.maintenance-mode-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'enabled' => Setting::get('maintenance_mode') === '1',
            'message' => Setting::get('maintenance_message'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('enabled')
                    ->label('Karbantartási mód bekapcsolva')
                    ->helperText('Bekapcsolva a webshop látogatói egy karbantartási oldalt látnak. Az admin felület bejelentkezése karbantartási módban is elérhető marad.'),
                Textarea::make('message')
                    ->label('Üzenet a karbantartási oldalon')
                    ->placeholder('Hamarosan visszatérünk! Köszönjük a türelmed, amíg frissítjük az oldalt.')
                    ->helperText('Üresen hagyva az alapértelmezett üzenet jelenik meg.')
                    ->rows(3),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Mentés')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('maintenance_mode', $state['enabled'] ? '1' : '0');
        Setting::set('maintenance_message', $state['message'] ?: null);

        Notification::make()
            ->title('A karbantartási mód beállításai mentve')
            ->success()
            ->send();
    }
}
