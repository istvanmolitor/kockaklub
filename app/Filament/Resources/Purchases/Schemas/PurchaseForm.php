<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\PurchaseStatus;
use App\Models\Site;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Eladó (beszállító)')
                    ->relationship('customer', 'name', fn ($query) => $query->where('is_seller', true))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('destination_site_id')
                    ->label('Cél telephely')
                    ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('currency_id')
                    ->label('Beszerzési deviza')
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('url')
                    ->label('Megrendelés linkje')
                    ->url()
                    ->columnSpanFull(),
                DateTimePicker::make('ordered_at')
                    ->label('Megrendelés időpontja')
                    ->required()
                    ->default(now()),
                Select::make('purchase_status_id')
                    ->label('Állapot')
                    ->options(fn () => PurchaseStatus::query()->get()->mapWithKeys(fn (PurchaseStatus $status) => [$status->id => $status->label()]))
                    ->default(fn () => PurchaseStatus::findByName(PurchaseStatus::ORDERED)->id)
                    ->required(),
                DatePicker::make('expected_arrival_date_min')
                    ->label('Várható érkezés – legkorábbi'),
                DatePicker::make('expected_arrival_date_max')
                    ->label('Várható érkezés – legkésőbbi'),
                TextInput::make('paid_amount_default_currency')
                    ->label('Kifizetett összeg forintban (mindent egybevéve)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('Ft'),
                Textarea::make('note')
                    ->label('Megjegyzés')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
