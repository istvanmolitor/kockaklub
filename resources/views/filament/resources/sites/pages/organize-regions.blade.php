<x-filament-panels::page>
    @php
        $regions = $this->getRegions();
        $rows = $this->getRows();
        $sourceRegion = $regions->firstWhere('id', $sourceRegionId);
        $destinationRegion = $regions->firstWhere('id', $destinationRegionId);
    @endphp

    <style>
        .organize-regions-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            align-items: start;
        }

        @media (max-width: 640px) {
            .organize-regions-columns {
                grid-template-columns: 1fr;
            }
        }

        .organize-regions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .organize-regions-table th,
        .organize-regions-table td {
            padding: 0.5rem 0.75rem;
            text-align: start;
            font-size: 0.875rem;
            border-bottom: 1px solid rgb(229 231 235);
            white-space: nowrap;
        }

        :is(.dark .organize-regions-table th, .dark .organize-regions-table td) {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .organize-regions-table th {
            font-weight: 600;
            color: rgb(107 114 128);
        }

        .organize-regions-table td.organize-regions-name {
            white-space: normal;
        }

        .organize-regions-image {
            width: 2.5rem;
            height: 2.5rem;
            object-fit: cover;
            border-radius: 0.375rem;
        }

        .organize-regions-quantity {
            text-align: center;
        }

        .organize-regions-arrows {
            text-align: center;
        }

        .organize-regions-transfer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .organize-regions-transfer-quantity {
            width: 4.5rem;
        }
    </style>

    <x-filament::section>
        <div class="organize-regions-columns">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="sourceRegionId">
                    <option value="">Válassz régiót</option>
                    @foreach ($regions as $region)
                        @continue($region->id === $destinationRegionId)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="destinationRegionId">
                    <option value="">Válassz régiót</option>
                    @foreach ($regions as $region)
                        @continue($region->id === $sourceRegionId)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </x-filament::section>

    @if ($sourceRegion && $destinationRegion)
        <x-filament::section>
            @if ($rows->isEmpty())
                <p style="color: rgb(107 114 128); font-size: 0.875rem;">Nincs készleten lévő termék ebben a két régióban.</p>
            @else
                <div style="overflow-x: auto;">
                    <table class="organize-regions-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cikkszám</th>
                                <th>Név</th>
                                <th class="organize-regions-quantity">{{ $sourceRegion->name }}</th>
                                <th class="organize-regions-arrows"></th>
                                <th class="organize-regions-quantity">{{ $destinationRegion->name }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr wire:key="row-{{ $row->product_id }}">
                                    <td>
                                        <img src="{{ $row->image_url }}" alt="" class="organize-regions-image" />
                                    </td>
                                    <td>{{ $row->sku }}</td>
                                    <td class="organize-regions-name">{{ $row->product_name }}</td>
                                    <td class="organize-regions-quantity">{{ $row->source_quantity }} db</td>
                                    <td class="organize-regions-arrows">
                                        <div class="organize-regions-transfer">
                                            <x-filament::icon-button
                                                icon="heroicon-o-arrow-left"
                                                color="primary"
                                                label="Átadás a forrás régióba"
                                                tooltip="Átadás a forrás régióba"
                                                wire:click="transferToSource({{ $row->product_id }})"
                                            />
                                            <div class="organize-regions-transfer-quantity">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input
                                                        type="number"
                                                        min="1"
                                                        wire:model="quantities.{{ $row->product_id }}"
                                                    />
                                                </x-filament::input.wrapper>
                                            </div>
                                            <x-filament::icon-button
                                                icon="heroicon-o-arrow-right"
                                                color="primary"
                                                label="Átadás a cél régióba"
                                                tooltip="Átadás a cél régióba"
                                                wire:click="transferToDestination({{ $row->product_id }})"
                                            />
                                        </div>
                                    </td>
                                    <td class="organize-regions-quantity">{{ $row->destination_quantity }} db</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @else
        <x-filament::section>
            <p style="color: rgb(107 114 128); font-size: 0.875rem;">Válassz forrás és cél régiót a rendezéshez.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
