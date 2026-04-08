<div>
    {{-- Header & Title --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Transacciones
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Consulta y consolidación de todos los movimientos de ingresos y egresos de los módulos.
            </p>
        </div>

        <div class="flex gap-2">
            <button wire:click="exportBrowser"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Exportar Excel
            </button>
        </div>
    </div>

    {{-- Resumen Cards --}}
    @php
        $isBoth = empty($moneda);
        $valClassBase = $isBoth ? 'text-lg' : 'text-2xl';
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Ingresos --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-3">Total Ingresos ({{ $dateLabel }})</p>
            <div class="mt-auto flex flex-col gap-2 relative z-10">
                @if ($isBoth || $moneda === 'BOB')
                    <div class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100' : '' }}">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">BOB</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->ingresos_bob ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($isBoth || $moneda === 'USD')
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">USD</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->ingresos_usd ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Egresos --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-3">Total Egresos ({{ $dateLabel }})</p>
            <div class="mt-auto flex flex-col gap-2 relative z-10">
                @if ($isBoth || $moneda === 'BOB')
                    <div class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100' : '' }}">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">BOB</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->egresos_bob ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($isBoth || $moneda === 'USD')
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">USD</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->egresos_usd ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Neto --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3">
                    </path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-3">Flujo Neto ({{ $dateLabel }})</p>
            @php
                $neto_bob = ($totales->ingresos_bob ?? 0) - ($totales->egresos_bob ?? 0);
                $neto_usd = ($totales->ingresos_usd ?? 0) - ($totales->egresos_usd ?? 0);
            @endphp
            <div class="mt-auto flex flex-col gap-2 relative z-10">
                @if ($isBoth || $moneda === 'BOB')
                    <div
                        class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100' : '' }}">
                        <span
                            class="text-[11px] font-semibold {{ $neto_bob >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-1.5 py-0.5 rounded">BOB</span>
                        <span
                            class="{{ $valClassBase }} font-bold {{ $neto_bob >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tabular-nums">
                            {{ number_format($neto_bob, 2, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($isBoth || $moneda === 'USD')
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold {{ $neto_usd >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-1.5 py-0.5 rounded">USD</span>
                        <span
                            class="{{ $valClassBase }} font-bold {{ $neto_usd >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tabular-nums">
                            {{ number_format($neto_usd, 2, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Cantidad --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                    </path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-2">Transacciones ({{ $dateLabel }})</p>
            <p class="mt-auto text-2xl font-bold text-gray-900 relative z-10">
                {{ number_format((int) ($totales->total_transacciones ?? 0), 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- FILTROS --}}
    @php
        $filtrosActivos = 0;
        if (!empty($banco_id)) {
            $filtrosActivos++;
        }
        if (!empty($modulo)) {
            $filtrosActivos++;
        }
        if (!empty($date_from) || !empty($date_to) || $periodo !== 'all') {
            $filtrosActivos++;
        }
    @endphp

    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div
            class="rounded-xl border border-gray-200 bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            {{-- DESKTOP --}}
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    {{-- Búsqueda --}}
                    <div class="md:col-span-6 lg:col-span-8">
                        <label
                            class="block text-xs mb-1 text-gray-600 dark:text-neutral-300 font-medium">Búsqueda</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.500ms="search"
                                placeholder="Concepto, referencia, notas..."
                                class="w-full rounded-lg border px-3 py-2 pl-9 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 transition-all shadow-sm" />
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Mostrar (PerPage) --}}
                    <div class="md:col-span-3 lg:col-span-2">
                        <label
                            class="block text-xs mb-1 text-gray-600 dark:text-neutral-300 font-medium">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-lg border border-gray-300 bg-white dark:bg-neutral-900 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 transition-all shadow-sm cursor-pointer">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    {{-- Botón Opciones --}}
                    <div class="md:col-span-3 lg:col-span-2">
                        <label
                            class="block text-xs mb-1 text-gray-600 dark:text-neutral-300 font-medium">Filtros</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition shadow-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Opciones
                            <span
                                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                                {{ $filtrosActivos }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Flotante --}}
        <div x-show="openFilters" x-cloak @click.outside="openFilters = false"
            @keydown.escape.window="openFilters = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute right-0 top-full mt-2 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
            wire:ignore.self>

            <div
                class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
                <button wire:click="limpiarFiltros"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Limpiar Filtros
                </button>
            </div>

            <div class="px-4 pb-4 space-y-4 pt-3 max-h-[60vh] overflow-y-auto" x-data="{ secBanco: true, secModulo: true, secFecha: true }">

                {{-- Banco --}}
                <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                    <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                        @click="secBanco = !secBanco">
                        <span class="font-semibold text-gray-800 dark:text-neutral-100">Banco / Cuenta</span>
                        <span class="text-gray-400" x-text="secBanco ? '▾' : '▸'"></span>
                    </button>
                    <div x-show="secBanco" class="mt-3">
                        <select wire:model.live="banco_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-600 transition-all cursor-pointer">
                            <option value="">-- Todos los bancos --</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b->id }}">{{ $b->nombre }} -
                                    {{ $b->numero_cuenta ?? 'Sin Nro' }} ({{ $b->moneda }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Módulo --}}
                <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                    <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                        @click="secModulo = !secModulo">
                        <span class="font-semibold text-gray-800 dark:text-neutral-100">Módulo Origen</span>
                        <span class="text-gray-400" x-text="secModulo ? '▾' : '▸'"></span>
                    </button>
                    <div x-show="secModulo" class="mt-3">
                        <select wire:model.live="modulo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-600 transition-all cursor-pointer">
                            <option value="">-- Todos los módulos --</option>
                            @foreach ($modulos_disponibles as $mod)
                                <option value="{{ $mod }}">{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Fecha --}}
                <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                    <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                        @click="secFecha = !secFecha">
                        <span class="font-semibold text-gray-800 dark:text-neutral-100">Fecha</span>
                        <span class="text-gray-400" x-text="secFecha ? '▾' : '▸'"></span>
                    </button>
                    <div x-show="secFecha" x-cloak class="mt-3 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Desde</label>
                                <input type="date" wire:model.live="date_from"
                                    class="w-full cursor-pointer border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
                                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                                           focus:outline-none focus:ring-2 focus:ring-offset-0
                                           focus:ring-gray-300 dark:focus:ring-neutral-600" />
                            </div>

                            <div>
                                <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Hasta</label>
                                <input type="date" wire:model.live="date_to"
                                    class="w-full cursor-pointer border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
                                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                                           focus:outline-none focus:ring-2 focus:ring-offset-0
                                           focus:ring-gray-300 dark:focus:ring-neutral-600" />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="setFechaMesActual"
                                class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors cursor-pointer">
                                Mes actual
                            </button>

                            <button type="button" wire:click="setFechaEsteAnio"
                                class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors cursor-pointer">
                                Este año
                            </button>

                            <button type="button" wire:click="clearFecha"
                                class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors cursor-pointer">
                                Limpiar fecha
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Transacciones --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Módulo / Banco</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Concepto y Comprobante</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Monto</th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Comprobante</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($transacciones as $t)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">Pago:
                                    {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y H:i') }}</span>
                                <div class="text-xs text-gray-500">Creación:
                                    {{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mb-1 border border-gray-200">
                                    {{ $t->modulo }}
                                </span>
                                <div class="text-xs text-gray-500 truncate max-w-xs"
                                    title="{{ collect($bancos)->firstWhere('id', $t->banco_id)?->nombre ?? 'N/A' }}">
                                    🏦 {{ collect($bancos)->firstWhere('id', $t->banco_id)?->nombre ?? 'N/A' }}
                                    @if (collect($bancos)->firstWhere('id', $t->banco_id)?->numero_cuenta)
                                        - {{ collect($bancos)->firstWhere('id', $t->banco_id)->numero_cuenta }}
                                    @endif
                                    ({{ collect($bancos)->firstWhere('id', $t->banco_id)?->moneda ?? $t->moneda }})
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 mb-0.5">{{ $t->concepto }}</div>
                                <div class="text-xs text-gray-500 flex gap-2">
                                    @if ($t->referencia)
                                        <span class="text-indigo-600 font-medium">
                                            {{ $t->modulo === 'Inversiones' ? 'Comp.:' : 'Ref:' }}
                                            {{ $t->referencia }}
                                        </span>
                                    @endif
                                    @if ($t->notas)
                                        <span class="truncate block max-w-xs text-gray-400"
                                            title="{{ $t->notas }}">| Nota: {{ $t->notas }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span
                                    class="text-sm font-bold {{ $t->tipo_movimiento === 'INGRESO' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $t->tipo_movimiento === 'INGRESO' ? '+' : '-' }}
                                    {{ number_format($t->monto, 2) }}
                                </span>
                                <div class="text-xs font-medium text-gray-500">{{ $t->moneda }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center">
                                    @if ($t->comprobante)
                                        <a href="{{ Storage::url($t->comprobante) }}" target="_blank"
                                            class="text-gray-400 hover:text-indigo-600 transition-colors"
                                            title="Ver comprobante">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-gray-300" title="Sin comprobante">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                </path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if ($t->url_origen)
                                    <a href="{{ $t->url_origen }}" target="_blank" rel="noopener noreferrer"
                                        class="text-indigo-600 hover:text-indigo-900 inline-flex items-center gap-1 group"
                                        title="Abrir origen en nueva pestaña">
                                        Origen
                                        <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100 transition-opacity"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                            </path>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-sm font-medium">No se encontraron transacciones con los filtros
                                    actuales.</p>
                                <button wire:click="limpiarFiltros"
                                    class="mt-2 text-sm text-indigo-600 font-medium hover:text-indigo-500 cursor-pointer">Limpiar
                                    los
                                    filtros</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transacciones->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $transacciones->links() }}
            </div>
        @endif
    </div>
</div>
