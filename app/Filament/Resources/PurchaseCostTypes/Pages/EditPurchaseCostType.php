<?php

namespace App\Filament\Resources\PurchaseCostTypes\Pages;

use App\Filament\Resources\PurchaseCostTypes\PurchaseCostTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseCostType extends EditRecord
{
    protected static string $resource = PurchaseCostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
