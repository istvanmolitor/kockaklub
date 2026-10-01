<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /** @var array<int, int> */
    protected array $attributeValueIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['attribute_values'] = ProductForm::mapAttributeValuesForFill($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->attributeValueIds = ProductForm::extractAttributeValueIds($data);

        unset($data['attribute_values']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->attributeValues()->sync($this->attributeValueIds);
    }
}
