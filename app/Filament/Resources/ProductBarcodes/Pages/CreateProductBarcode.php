<?php

namespace App\Filament\Resources\ProductBarcodes\Pages;

use App\Filament\Resources\ProductBarcodes\ProductBarcodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductBarcode extends CreateRecord
{
    protected static string $resource = ProductBarcodeResource::class;
}
