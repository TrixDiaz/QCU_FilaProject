<x-filament-panels::page>
    <livewire:inventory-stats-overview/>
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{--  Graph  --}}
        <livewire:asset-categories/>
        <livewire:allocation-status/>
    </section>
    <x-filament::section>
        <x-slot name="heading">
            Inventory
        </x-slot>
        {{-- Content --}}
        <livewire:inventory/>
    </x-filament::section>
</x-filament-panels::page>
