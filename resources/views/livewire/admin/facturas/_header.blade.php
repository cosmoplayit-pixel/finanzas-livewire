{{-- HEADER (RESPONSIVE) --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-2xl font-semibold">Facturas</h1>

    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto sm:items-center">


        {{-- Nueva factura --}}
        @can('facturas.create')
            <button wire:click="openCreateFactura" wire:loading.attr="disabled" wire:target="openCreateFactura"
                class="w-full sm:w-auto px-4 py-2 rounded
                           bg-black text-white
                           hover:bg-gray-800 hover:text-white
                           transition-colors duration-150
                           cursor-pointer
                           disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="openCreateFactura">Nueva factura</span>
                <span wire:loading wire:target="openCreateFactura">Abriendo…</span>
            </button>
        @endcan
    </div>
</div>
