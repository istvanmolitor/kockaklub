<?php

namespace App\Filament\Resources\PurchaseStatuses\Pages;

use App\Filament\Resources\PurchaseStatuses\PurchaseStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseStatus extends EditRecord
{
    protected static string $resource = PurchaseStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
