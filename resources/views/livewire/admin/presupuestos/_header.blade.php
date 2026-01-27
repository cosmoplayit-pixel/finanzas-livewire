{{-- HEADER --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-neutral-100">
            Presupuestos y Rendiciones
        </h1>
        <div class="text-sm text-gray-500 dark:text-neutral-400">
            Resumen general por agente (presupuesto / rendido / saldo por rendir). El detalle está en el Panel.
        </div>
    </div>

    <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
        class="w-full sm:w-auto px-4 py-2 rounded
                   bg-black text-white hover:bg-gray-800 transition
                   cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
        <span wire:loading.remove wire:target="openCreate">Nuevo Presupuesto</span>
        <span wire:loading wire:target="openCreate">Abriendo…</span>
    </button>
</div>
