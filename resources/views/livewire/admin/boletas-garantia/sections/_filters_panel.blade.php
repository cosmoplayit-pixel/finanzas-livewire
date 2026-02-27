<div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
    <button type="button" class="w-full flex items-center justify-between"
        @click="secTipo = !secTipo">
        <span class="font-semibold text-gray-800 dark:text-neutral-100">Tipo</span>
        <span class="text-gray-400" x-text="secTipo ? '▾' : '▸'"></span>
    </button>

    <div x-show="secTipo" x-cloak class="mt-3 space-y-2">
        <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
            <input type="checkbox" wire:key="chk-tipo-seriedad" @checked(in_array('SERIEDAD', $f_tipo ?? [], true))
                wire:click="toggleFilter('tipo','SERIEDAD')"
                class="rounded border-gray-300 dark:border-neutral-700" />
            Garantía de Seriedad
        </label>

        <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
            <input type="checkbox" wire:key="chk-tipo-cumplimiento" @checked(in_array('CUMPLIMIENTO', $f_tipo ?? [], true))
                wire:click="toggleFilter('tipo','CUMPLIMIENTO')"
                class="rounded border-gray-300 dark:border-neutral-700" />
            Garantía de Cumplimiento
        </label>
    </div>
</div>

<div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
    <button type="button" class="w-full flex items-center justify-between"
        @click="secEstado = !secEstado">
        <span class="font-semibold text-gray-800 dark:text-neutral-100">Estado</span>
        <span class="text-gray-400" x-text="secEstado ? '▾' : '▸'"></span>
    </button>

    <div x-show="secEstado" x-cloak class="mt-3 space-y-2">
        <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
            <input type="checkbox" wire:key="chk-estado-abierta" @checked(in_array('abierta', $f_estado ?? [], true))
                wire:click="toggleFilter('estado','abierta')"
                class="rounded border-gray-300 dark:border-neutral-700" />
            Abiertas
        </label>

        <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
            <input type="checkbox" wire:key="chk-estado-devuelta" @checked(in_array('devuelta', $f_estado ?? [], true))
                wire:click="toggleFilter('estado','devuelta')"
                class="rounded border-gray-300 dark:border-neutral-700" />
            Devueltas
        </label>
    </div>
</div>

<div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
    <button type="button" class="w-full flex items-center justify-between"
        @click="secFecha = !secFecha">
        <span class="font-semibold text-gray-800 dark:text-neutral-100">Fecha (Emisión)</span>
        <span class="text-gray-400" x-text="secFecha ? '▾' : '▸'"></span>
    </button>

    <div x-show="secFecha" x-cloak class="mt-3 space-y-3">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Desde</label>
                <input type="date" wire:model.live="f_fecha_desde"
                    class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-offset-0
                           focus:ring-gray-300 dark:focus:ring-neutral-600" />
            </div>

            <div>
                <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Hasta</label>
                <input type="date" wire:model.live="f_fecha_hasta"
                    class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-offset-0
                           focus:ring-gray-300 dark:focus:ring-neutral-600" />
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="setFechaMesActual"
                class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                Mes actual
            </button>

            <button type="button" wire:click="setFechaEsteAnio"
                class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                Este año
            </button>

            <button type="button" wire:click="clearFecha"
                class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                Limpiar fecha
            </button>
        </div>
    </div>
</div>
