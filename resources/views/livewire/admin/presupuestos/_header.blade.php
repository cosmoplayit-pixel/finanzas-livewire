{{-- HEADER --}}

{{-- MOBILE (<= md): título + botón arriba a la derecha, descripción compacta --}}
<div class="md:hidden">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-lg font-semibold leading-tight text-gray-900 dark:text-neutral-100">
                Presupuestos y Rendiciones
            </h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400 line-clamp-2">
                Resumen general por agente (presupuesto / rendido / saldo por rendir). El detalle está en el Panel.
            </p>
        </div>

        <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
            class="shrink-0 inline-flex items-center gap-2 rounded-lg px-3 py-2
                   text-sm font-semibold
                   bg-black text-white hover:bg-gray-800 transition
                   disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
            </svg>

            <span wire:loading.remove wire:target="openCreate">Nuevo</span>
            <span wire:loading wire:target="openCreate">…</span>
        </button>
    </div>
</div>

{{-- DESKTOP (>= md): layout clásico con botón a la derecha --}}
<div class="hidden md:flex md:items-start md:justify-between md:gap-6">
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-neutral-100">
            Presupuestos y Rendiciones
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
            Resumen general por agente (presupuesto / rendido / saldo por rendir). El detalle está en el Panel.
        </p>
    </div>

    <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
        class="inline-flex items-center justify-center gap-2
               px-4 py-2.5 rounded-lg
               bg-black text-white hover:bg-gray-800 transition
               cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
        </svg>
        <span wire:loading.remove wire:target="openCreate">Nuevo Presupuesto</span>
        <span wire:loading wire:target="openCreate">Abriendo…</span>
    </button>
</div>
