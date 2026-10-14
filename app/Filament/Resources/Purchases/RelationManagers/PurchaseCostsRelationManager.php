<?php

namespace App\Filament\Resources\Purchases\RelationManagers;

use App\Models\PurchaseCost;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseCostsRelationManager extends RelationManager
{
    protected static string $relationship = 'costs';

    protected static ?string $title = 'Egyéb költségek';

    protected static ?string $modelLabel = 'Költség';

    protected static ?string $pluralModelLabel = 'Költségek';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('purchase_cost_type_id')
                    ->label('Költségtípus')
                    ->relationship('purchaseCostType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('amount')
                    ->label('Összeg')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    ->suffix(fn () => $this->getOwnerRecord()->currency->code)
                    ->required(),
                Textarea::make('note')
                    ->label('Megjegyzés')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record) => $record->purchaseCostType?->name ?? '')
            ->columns([
                TextColumn::make('purchaseCostType.name')
                    ->label('Költségtípus'),
                TextColumn::make('amount')
                    ->label('Összeg')
                    ->formatStateUsing(fn (PurchaseCost $record) => number_format((float) $record->amount, 4).' '.$this->getOwnerRecord()->currency->code),
                TextColumn::make('note')
                    ->label('Megjegyzés')
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
