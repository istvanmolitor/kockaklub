<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReserveOrderItems extends Page
{
    use InteractsWithRecord;

    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.reserve-order-items';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    public function getTitle(): string
    {
        return 'Termékek befoglalása';
    }

    public function getPlan(): Collection
    {
        return app(StockService::class)->planReservation($this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reserve')
                ->label('Termék befoglalása kész')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Order $record) => ! $record->isReserved())
                ->requiresConfirmation()
                ->modalDescription('A fenti bontás szerint levonódik a készlet a publikus régiókból, és a rendelés befoglalt lesz. A művelet nem vonható vissza.')
                ->action(function (Order $record) {
                    try {
                        app(StockService::class)->reserveOrder($record);

                        Notification::make()
                            ->title('A termékek befoglalva')
                            ->success()
                            ->send();

                        $this->redirect(OrderResource::getUrl('edit', ['record' => $record]));
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('A befoglalás sikertelen')
                            ->body(implode(' ', $exception->validator->errors()->all()))
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
