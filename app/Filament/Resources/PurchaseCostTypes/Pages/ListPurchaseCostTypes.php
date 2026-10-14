<?php

namespace App\Filament\Resources\PurchaseCostTypes\Pages;

use App\Filament\Resources\PurchaseCostTypes\PurchaseCostTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseCostTypes extends ListRecords
{
    protected static string $resource = PurchaseCostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
