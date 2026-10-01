<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /** @var array<int, int> */
    protected array $attributeValueIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->attributeValueIds = ProductForm::extractAttributeValueIds($data);

        unset($data['attribute_values']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->attributeValues()->sync($this->attributeValueIds);
    }
}
