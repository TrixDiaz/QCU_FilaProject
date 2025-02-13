<x-filament-panels::page>
    <livewire:stats-overview/>
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <livewire:ticket-summary/>
        <livewire:pending-approval/>
    </section>
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <livewire:ticket-type/>
        <livewire:asset-availability/>
    </section>
    <livewire:inventory-trends/>
</x-filament-panels::page>
