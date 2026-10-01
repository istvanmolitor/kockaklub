<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpenOrdersOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $openOrdersCount = Order::whereRelation('orderStatus', 'is_final', false)->count();

        return [
            Stat::make('Nyitott megrendelések', $openOrdersCount)
                ->description('Nem lezárt státuszú megrendelések')
                ->icon(Heroicon::OutlinedShoppingBag)
                ->url(OrderResource::getUrl('index')),
        ];
    }
}
