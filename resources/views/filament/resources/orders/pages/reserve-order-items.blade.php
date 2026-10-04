<x-filament-panels::page>
    @php
        $record = $this->getRecord();
        $plan = $this->getPlan();
    @endphp

    @if ($record->isReserved())
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Ez a rendelés már be van foglalva ({{ $record->reserved_at->format('Y.m.d. H:i') }}).
            </p>
        </div>
    @else
        <div class="fi-section overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="fi-ta-table w-full divide-y divide-gray-200 text-sm dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-2 text-start font-medium text-gray-600 dark:text-gray-300">Termék</th>
                        <th class="px-4 py-2 text-start font-medium text-gray-600 dark:text-gray-300">Szükséges mennyiség</th>
                        <th class="px-4 py-2 text-start font-medium text-gray-600 dark:text-gray-300">Honnan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($plan as $row)
                        <tr>
                            <td class="px-4 py-3 align-top">{{ $row['item']->product_name }}</td>
                            <td class="px-4 py-3 align-top">{{ $row['item']->quantity }} db</td>
                            <td class="px-4 py-3 align-top">
                                @if (count($row['allocations']))
                                    <ul class="space-y-1">
                                        @foreach ($row['allocations'] as $allocation)
                                            <li>{{ $allocation['region_name'] }}: {{ $allocation['quantity'] }} db</li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($row['shortfall'] > 0)
                                    <p class="mt-1 font-medium text-danger-600 dark:text-danger-400">
                                        Hiányzik {{ $row['shortfall'] }} db a publikus készletből.
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
