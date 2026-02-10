{{-- HEADER (RESPONSIVE) --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-2xl font-semibold">Facturas</h1>

    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto sm:items-center">


        {{-- Nueva factura --}}
        @can('facturas.create')
            <button wire:click="openCreateFactura" wire:loading.attr="disabled" wire:target="openCreateFactura"
                class="inline-flex items-center gap-2
                w-full sm:w-auto px-4 py-2 rounded
                bg-black text-white
                hover:bg-gray-800 hover:text-white
                transition-colors duration-150
                cursor-pointer
                disabled:opacity-50 disabled:cursor-not-allowed">

                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
                </svg>
                <span wire:loading.remove wire:target="openCreateFactura">Nueva factura</span>
                <span wire:loading wire:target="openCreateFactura">Abriendo…</span>
            </button>
        @endcan
    </div>
</div>
