<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reserveItems')
                ->label('Termékek befoglalása')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->visible(fn (Order $record) => ! $record->isReserved())
                ->url(fn (Order $record) => ReserveOrderItems::getUrl(['record' => $record])),
            DeleteAction::make(),
        ];
    }
}
