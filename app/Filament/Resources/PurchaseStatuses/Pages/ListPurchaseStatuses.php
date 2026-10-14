<?php

namespace App\Filament\Resources\PurchaseStatuses\Pages;

use App\Filament\Resources\PurchaseStatuses\PurchaseStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseStatuses extends ListRecords
{
    protected static string $resource = PurchaseStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
