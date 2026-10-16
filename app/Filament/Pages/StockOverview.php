<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class StockOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Készlet';

    protected static ?string $navigationLabel = 'Készletlista';

    protected static ?string $title = 'Készletlista';

    protected static ?string $slug = 'keszletlista';

    protected string $view = 'filament.pages.stock-overview';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->withFreeStock()->withPendingPurchase())
            ->columns([
                ImageColumn::make('default_image_url')
                    ->label('Kép')
                    ->square()
                    ->checkFileExistence(false),
                TextColumn::make('sku')
                    ->label('Cikkszám')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                TextColumn::make('public_stock')
                    ->label('Készlet')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('free_stock')
                    ->label('Szabad készlet')
                    ->getStateUsing(fn ($record) => max(0, (int) $record->public_stock - (int) $record->pending_reservation))
                    ->numeric(),
                TextColumn::make('pending_purchase')
                    ->label('Beszerzés alatt')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategória')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                Action::make('settings')
                    ->label('Beállítás')
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->url(fn (Product $record): string => ProductStockSettings::getUrl(['record' => $record])),
            ])
            ->defaultSort('public_stock');
    }
}
