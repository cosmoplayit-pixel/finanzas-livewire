@section('title', 'Resumen de Proyectos')

<div>
    {{-- Header + acciones --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                Resumen de Proyectos
            </h1>
            <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">
                Visualización consolidada de indicadores financieros de ingresos y egresos por proyecto.
            </p>
        </div>
    </div>

    {{-- Resumen Totales (Summary Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-4">
        {{-- Total Adjudicado --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-10 h-10 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Adjudicado ({{ $dateLabel }})</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">
                Bs {{ number_format((float) ($totales['adjudicado'] ?? 0), 2, ',', '.') }}
            </p>
        </div>
        {{-- Total Facturado --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Facturado ({{ $dateLabel }})</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                Bs {{ number_format((float) ($totales['facturado'] ?? 0), 2, ',', '.') }}
            </p>
        </div>
        {{-- Total Pagado --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Pagado ({{ $dateLabel }})</p>
            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                Bs {{ number_format((float) ($totales['pagado'] ?? 0), 2, ',', '.') }}
            </p>
        </div>
        {{-- Total Deuda --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-10 h-10 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Deuda ({{ $historicalLabel }})</p>
            <p class="text-xl font-bold text-rose-600 dark:text-rose-400">
                Bs {{ number_format((float) ($totales['deuda'] ?? 0), 2, ',', '.') }}
            </p>
        </div>
        {{-- Total Compras --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-10 h-10 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Compras ({{ $dateLabel }})</p>
            <p class="text-xl font-bold text-orange-600 dark:text-orange-400">
                Bs {{ number_format((float) ($totales['compras'] ?? 0), 2, ',', '.') }}
            </p>
        </div>
        {{-- Utilidad Aprox --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-10 h-10 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Utilidad Aprox. ({{ $dateLabel }})</p>
            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                Bs {{ number_format((float) ($totales['utilidad'] ?? 0), 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Filtros (Basado en el estilo de Facturas) --}}
    @php
        $filtrosActivos = $this->countActiveFilters();
    @endphp
    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6 lg:col-span-8">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Nombre de proyecto, entidad..."
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    </div>

                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Filtros</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="relative w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition
                                   {{ $filtrosActivos > 0 ? 'border-blue-500 dark:border-blue-500' : '' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Opciones
                            @if ($filtrosActivos > 0)
                                <span
                                    class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                                    {{ $filtrosActivos }}
                                </span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel de filtros flotante (mismo estilo que la imagen y facturas) --}}
        <div x-show="openFilters" x-cloak @click.outside="openFilters = false"
            @keydown.escape.window="openFilters = false"
            class="absolute right-0 top-full mt-2 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
            wire:ignore.self>

            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
                <button wire:click="clearFilters"
                    class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium">Limpiar
                    Todo</button>
            </div>

            <div class="p-4 space-y-4 max-h-[60vh] overflow-y-auto">
                {{-- Entidad --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-1">Entidad
                        Cliente</label>
                    <select wire:model.live="f_entidad"
                        class="w-full rounded border px-3 py-2 text-sm border-gray-300 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                        <option value="">-- Todas las entidades --</option>
                        @foreach ($entidadesOpciones as $e)
                            <option value="{{ $e['value'] }}">{{ $e['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Facturas --}}
                <div class="border-t border-gray-100 dark:border-neutral-800 pt-3">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">Estado de
                        Facturas</label>
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="f_facturas" value="con_facturas"
                                class="rounded border-gray-300 dark:border-neutral-700 text-indigo-600">
                            Con Facturas
                        </label>
                        <label
                            class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="f_facturas" value="sin_facturas"
                                class="rounded border-gray-300 dark:border-neutral-700 text-indigo-600">
                            Sin Facturas
                        </label>
                    </div>
                </div>

                {{-- Deuda --}}
                <div class="border-t border-gray-100 dark:border-neutral-800 pt-3">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">Estado de
                        Deuda</label>
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="f_deuda" value="con_deuda"
                                class="rounded border-gray-300 dark:border-neutral-700 text-indigo-600">
                            Con Deuda (Pendientes)
                        </label>
                        <label
                            class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="f_deuda" value="sin_deuda"
                                class="rounded border-gray-300 dark:border-neutral-700 text-indigo-600">
                            Sin Deuda (Pagados)
                        </label>
                    </div>
                </div>

                {{-- Compras --}}
                <div class="border-t border-gray-100 dark:border-neutral-800 pt-3">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">Estado de
                        Compras</label>
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="f_compras" value="con_compras"
                                class="rounded border-gray-300 dark:border-neutral-700 text-indigo-600">
                            Con Rendiciones/Compras
                        </label>
                        <label
                            class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="f_compras" value="sin_compras"
                                class="rounded border-gray-300 dark:border-neutral-700 text-indigo-600">
                            Sin Rendiciones/Compras
                        </label>
                    </div>
                </div>

                {{-- Fechas --}}
                <div class="border-t border-gray-100 dark:border-neutral-800 pt-3">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">Fechas del
                        Proyecto</label>
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <input type="date" wire:model.live="f_fecha_desde"
                            class="w-full border rounded px-2 py-1.5 text-xs border-gray-300 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                        <input type="date" wire:model.live="f_fecha_hasta"
                            class="w-full border rounded px-2 py-1.5 text-xs border-gray-300 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="setFechaEsteMes"
                            class="text-[10px] px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200">Este
                            mes</button>
                        <button type="button" wire:click="setFechaEsteAño"
                            class="text-[10px] px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200">Este
                            año</button>
                        <button type="button" wire:click="clearFechas"
                            class="text-[10px] px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200">Limpiar
                            Fechas</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla consolidada (Desktop) --}}
    <div class="hidden md:block border border-gray-100 rounded bg-white dark:bg-neutral-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-auto text-sm">
                <thead
                    class="bg-slate-50/50 text-slate-600 dark:bg-neutral-900/50 dark:text-neutral-400 border-b border-gray-100 dark:border-neutral-800">
                    <tr class="text-left text-[11px] uppercase tracking-wider font-semibold">
                        <th class="p-3 text-center">#</th>
                        <th class="p-3">Entidad / Proyecto</th>
                        <th class="p-3">Periodo</th>
                        <th class="p-3 text-right">Adjudicado</th>
                        <th class="p-3 text-right">Facturado</th>
                        <th class="p-3 text-right">Pagado</th>
                        <th class="p-3 text-right">Deuda</th>
                        <th class="p-3 text-right">Compras</th>
                        <th class="p-3 text-right">Utilidad Aprox.</th>
                        <th class="p-3 text-center">Acc.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse ($proyectos as $index => $t)
                        <tr
                            class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/60 transition-colors text-gray-700 dark:text-neutral-200">
                            <td class="p-3 text-center text-xs text-gray-400">
                                {{ $proyectos->firstItem() + $index }}
                            </td>
                            <td class="p-3">
                                <div
                                    class="truncate text-[10px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">
                                    {{ $t->entidad_nombre }}</div>
                                <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $t->nombre }}</div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="text-[11px] text-gray-600 dark:text-neutral-400 block">D:
                                    {{ $t->fecha_inicio ? \Carbon\Carbon::parse($t->fecha_inicio)->format('d/m/y') : 'N/A' }}</span>
                                <span class="text-[11px] text-gray-600 dark:text-neutral-400 block">H:
                                    {{ $t->fecha_fin ? \Carbon\Carbon::parse($t->fecha_fin)->format('d/m/y') : 'N/A' }}</span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <span
                                    class="text-[13px] font-medium">{{ number_format((float) $t->monto_adjudicado, 0, ',', '.') }}</span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <span
                                    class="text-[13px] font-bold {{ $t->total_facturado > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                    {{ number_format((float) $t->total_facturado, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <span
                                    class="text-[13px] font-bold {{ $t->total_pagado > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}">
                                    {{ number_format((float) $t->total_pagado, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <span
                                    class="text-[13px] font-bold {{ $t->total_deuda > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                                    {{ number_format((float) $t->total_deuda, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <span
                                    class="text-[13px] font-bold {{ $t->total_compras > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}">
                                    {{ number_format((float) $t->total_compras, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <span
                                    class="inline-block px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[13px] font-bold">
                                    {{ number_format((float) $t->utilidad_aproximada, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-center">
                                <button type="button"
                                    wire:click="$dispatch('open-modal-detalle-proyecto', { id: {{ $t->id }} })"
                                    class="w-8 h-8 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 transition shadow-sm cursor-pointer inline-flex items-center justify-center">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-12 text-center">
                                <div
                                    class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                        </path>
                                    </svg>
                                    <span class="text-sm font-medium">Sin resultados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $proyectos->links() }}
    </div>

    {{-- Modal Component --}}
    <livewire:admin.proyectos.resumen-modal />
</div>
