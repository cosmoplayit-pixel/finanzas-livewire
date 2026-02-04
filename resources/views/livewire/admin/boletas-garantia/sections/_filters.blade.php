{{-- ===================== BUSCADOR + FILTROS ===================== --}}
<div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 p-4 md:p-5" x-data="{ openFilters: false }">

    {{-- FILA PRINCIPAL (ordenada) --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-20 md:items-end">

        {{-- Buscar --}}
        <div class="md:col-span-7 lg:col-span-8">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                Buscar
            </label>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar por Nro de Boleta"
                class="w-full rounded-lg border px-3 py-2.5
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                autocomplete="off" />
        </div>
        <div class="md:col-span-2 lg:col-span-8">

        </div>

        {{-- Por página --}}
        <div class="md:col-span-2 lg:col-span-2">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                Por página
            </label>
            <select wire:model.live="perPage"
                class="w-full cursor-pointer rounded-lg border px-3 py-2.5
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>



        {{-- Botón filtros --}}
        <div class="md:col-span-3 lg:col-span-2 relative">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                Por Filtros
            </label>

            <button type="button" @click="openFilters = !openFilters"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg
                       bg-black text-white hover:bg-gray-800 transition-colors duration-150">

                {{-- icon sliders --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="21" x2="4" y2="14" />
                    <line x1="4" y1="10" x2="4" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12" y2="3" />
                    <line x1="20" y1="21" x2="20" y2="16" />
                    <line x1="20" y1="12" x2="20" y2="3" />
                    <line x1="1" y1="14" x2="7" y2="14" />
                    <line x1="9" y1="8" x2="15" y2="8" />
                    <line x1="17" y1="16" x2="23" y2="16" />
                </svg>

                <span>Filtros</span>

                {{-- contador (igual que tenías) --}}
                <span class="text-xs opacity-80">
                    ({{ count($f_tipo ?? []) + count($f_estado ?? []) + count($f_devoluciones ?? []) + (!empty($f_banco_egreso) ? 1 : 0) + (!empty($f_fecha_desde) ? 1 : 0) + (!empty($f_fecha_hasta) ? 1 : 0) }})
                </span>
            </button>

            {{-- ====== PANEL FLOTANTE (NO TOCADO, igual que tu versión) ====== --}}
            <div x-show="openFilters" x-cloak x-transition.origin.top.right @click.outside="openFilters = false"
                @keydown.escape.window="openFilters = false"
                class="absolute right-0 top-full mt-3 w-full sm:w-[360px] z-50
                       rounded-xl border border-gray-200 bg-white shadow-xl
                       dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
                wire:ignore.self wire:key="boletas-panel-filtros">

                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                    <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros</div>
                </div>

                {{-- Secciones colapsables (TU CONTENIDO, SIN CAMBIOS) --}}
                <div class="px-4 pb-4 space-y-4" x-data="{ secTipo: true, secEstado: true, secDev: true, secFecha: true, secBanco: true }">

                    {{-- ===================== TIPO ===================== --}}
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

                    {{-- ===================== ESTADO ===================== --}}
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

                    {{-- ===================== FECHA (EMISIÓN) ===================== --}}
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

                    {{-- (si tienes más secciones: Dev/Banco, déjalas aquí igual) --}}

                </div>
            </div>
        </div>
    </div>

    {{-- Mobile ajuste: PerPage + Filtros en 2 columnas (ya lo da el grid general) --}}
</div>
