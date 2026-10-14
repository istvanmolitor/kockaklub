<?php

namespace App\Filament\Resources\Sites\Pages;

use App\Filament\Resources\Sites\SiteResource;
use App\Models\Region;
use App\Services\RegionProductTransferService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OrganizeRegions extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SiteResource::class;

    protected string $view = 'filament.resources.sites.pages.organize-regions';

    public ?int $sourceRegionId = null;

    public ?int $destinationRegionId = null;

    /** @var array<int, int> */
    public array $quantities = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    public function getTitle(): string
    {
        return "{$this->getRecord()->name} telephely rendezése";
    }

    /** @return Collection<int, Region> */
    public function getRegions(): Collection
    {
        return $this->getRecord()->regions()->orderBy('name')->get();
    }

    /** @return Collection<int, object> */
    public function getRows(): Collection
    {
        if (! $this->sourceRegionId || ! $this->destinationRegionId) {
            return collect();
        }

        $rows = app(RegionProductTransferService::class)->productRowsForRegionPair(
            $this->sourceRegionId,
            $this->destinationRegionId,
        );

        foreach ($rows as $row) {
            if (! array_key_exists($row->product_id, $this->quantities)) {
                $this->quantities[$row->product_id] = max($row->source_quantity, $row->destination_quantity);
            }
        }

        return $rows;
    }

    public function transferToDestination(int $productId): void
    {
        $this->transfer($this->sourceRegionId, $this->destinationRegionId, $productId);
    }

    public function transferToSource(int $productId): void
    {
        $this->transfer($this->destinationRegionId, $this->sourceRegionId, $productId);
    }

    private function transfer(?int $sourceRegionId, ?int $destinationRegionId, int $productId): void
    {
        if (! $sourceRegionId || ! $destinationRegionId) {
            return;
        }

        $quantity = (int) ($this->quantities[$productId] ?? 0);

        try {
            app(RegionProductTransferService::class)->transfer(
                Region::findOrFail($sourceRegionId),
                Region::findOrFail($destinationRegionId),
                $productId,
                $quantity,
            );

            unset($this->quantities[$productId]);

            Notification::make()
                ->title('A termék átkerült a régiók között')
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Az átadás sikertelen')
                ->body(implode(' ', $exception->validator->errors()->all()))
                ->danger()
                ->send();
        }
    }
}
