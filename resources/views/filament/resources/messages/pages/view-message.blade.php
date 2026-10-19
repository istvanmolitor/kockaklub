<x-filament-panels::page>
    @php
        $record = $this->getRecord();
        $customer = $record->customer;
    @endphp

    <x-filament::section heading="Ügyfél">
        <a
            href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $customer]) }}"
            style="font-weight: 600; color: var(--primary-600); text-decoration: underline;"
        >
            {{ $customer->name }}
        </a>
        <div style="margin-top: 0.25rem; font-size: 0.875rem; color: var(--gray-500);">
            {{ $record->email }}
            @if ($record->phone)
                · {{ $record->phone }}
            @endif
        </div>
    </x-filament::section>

    <x-filament::section heading="Kommunikáció">
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach ($this->getHistory() as $item)
                <div style="display: flex; flex-direction: column; gap: 0.75rem; {{ $item->is($record) ? 'box-shadow: 0 0 0 2px var(--primary-500); border-radius: 0.5rem; padding: 0.5rem;' : '' }}">
                    @unless ($item->initiated_by_admin)
                        <div style="background: color-mix(in oklab, var(--gray-500) 10%, transparent); border-radius: 0.5rem; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; gap: 1rem; font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">
                                <span>{{ $item->name }}</span>
                                <span style="white-space: nowrap;">{{ $item->created_at->format('Y.m.d. H:i') }}</span>
                            </div>
                            <div style="margin-top: 0.5rem; white-space: pre-line; font-size: 0.875rem;">{{ $item->message }}</div>
                        </div>
                    @endunless

                    @if ($item->isReplied())
                        <div style="background: color-mix(in oklab, var(--primary-500) 10%, transparent); border: 1px solid color-mix(in oklab, var(--primary-500) 30%, transparent); border-radius: 0.5rem; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; gap: 1rem; font-size: 0.75rem; font-weight: 600; color: var(--primary-600);">
                                <span>Kockaklub</span>
                                <span style="white-space: nowrap;">{{ $item->replied_at->format('Y.m.d. H:i') }}</span>
                            </div>
                            <div style="margin-top: 0.5rem; white-space: pre-line; font-size: 0.875rem;">{{ $item->reply }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section heading="Válasz küldése">
        <form wire:submit.prevent="sendReply" style="display: flex; flex-direction: column; gap: 1rem;">
            {{ $this->form }}

            <div>
                <x-filament::button type="submit">
                    Válasz küldése
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
