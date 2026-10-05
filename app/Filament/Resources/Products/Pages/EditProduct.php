<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\RelationManagers\PriceLogsRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /** @var array<int, int> */
    protected array $attributeValueIds = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPublic')
                ->label('Publikus nézet')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => route('catalog.show', $this->record))
                ->openUrlInNewTab(),
            ViewAction::make()
                ->label('Statisztika')
                ->icon('heroicon-o-chart-bar'),
            DeleteAction::make(),
        ];
    }

    /**
     * @return array<class-string<RelationManager>|RelationGroup|RelationManagerConfiguration>
     */
    protected function getAllRelationManagers(): array
    {
        return array_filter(
            parent::getAllRelationManagers(),
            fn (string|RelationGroup|RelationManagerConfiguration $manager): bool => $manager !== PriceLogsRelationManager::class,
        );
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
