<div class="space-y-4">
    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Boletas de Garantía</h1>

        @can('boletas_garantia.create')
            <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                class="w-full sm:w-auto px-4 py-2 rounded
                   bg-black text-white hover:bg-gray-800
                   transition disabled:opacity-50 disabled:cursor-not-allowed">

                <span wire:loading.remove wire:target="openCreate">Nueva Boleta</span>
                <span wire:loading wire:target="openCreate">Abriendo…</span>
            </button>
        @endcan
    </div>

    {{-- ===================== ALERTAS ===================== --}}
    @if (session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif
    {{-- ===================== BUSCADOR + FILTROS ===================== --}}
    <div class="space-y-3" x-data="{ openFilters: false }">

        {{-- FILA PRINCIPAL --}}
        <div class="relative flex flex-col gap-2 md:flex-row md:items-center">

            {{-- Buscar --}}
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar por Nro o Tipo…"
                class="w-full md:w-80 border rounded px-3 py-2
                   bg-white text-gray-900 border-gray-300
                   dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700"
                autocomplete="off" />

            {{-- Acciones derecha --}}
            <div class="flex gap-2 md:ml-auto w-full md:w-auto">

                {{-- PerPage --}}
                <select wire:model.live="perPage"
                    class="w-full md:w-auto border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>

                {{-- Botón Filtros --}}
                <button type="button" @click="openFilters = !openFilters"
                    class="w-full md:w-auto px-4 py-2 rounded
                       bg-black text-white hover:bg-gray-800
                       transition-colors duration-150">
                    Filtros
                    {{-- opcional contador simple --}}
                    <span class="ml-2 text-xs opacity-80">
                        ({{ count($f_tipo ?? []) + count($f_estado ?? []) + count($f_devoluciones ?? []) + (!empty($f_banco_egreso) ? 1 : 0) + (!empty($f_fecha_desde) ? 1 : 0) + (!empty($f_fecha_hasta) ? 1 : 0) }})
                    </span>
                </button>

            </div>

            {{--  PANEL FLOTANTE --}}
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

                {{-- Secciones colapsables --}}
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

                    {{-- ===================== DEVOLUCIONES (SI/NO) ===================== --}}
                    <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                        <button type="button" class="w-full flex items-center justify-between"
                            @click="secDev = !secDev">
                            <span class="font-semibold text-gray-800 dark:text-neutral-100">Devoluciones</span>
                            <span class="text-gray-400" x-text="secDev ? '▾' : '▸'"></span>
                        </button>

                        <div x-show="secDev" x-cloak class="mt-3 space-y-2">
                            <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                                <input type="checkbox" wire:key="chk-dev-con" @checked(in_array('con', $f_devoluciones ?? [], true))
                                    wire:click="toggleFilter('devoluciones','con')"
                                    class="rounded border-gray-300 dark:border-neutral-700" />
                                Con devoluciones
                            </label>

                            <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                                <input type="checkbox" wire:key="chk-dev-sin" @checked(in_array('sin', $f_devoluciones ?? [], true))
                                    wire:click="toggleFilter('devoluciones','sin')"
                                    class="rounded border-gray-300 dark:border-neutral-700" />
                                Sin devoluciones
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
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                                <button type="button" wire:click="setFechaEsteAnio"
                                    class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                                    Este año
                                </button>

                                <button type="button" wire:click="setFechaAnioPasado"
                                    class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                                    Año pasado
                                </button>

                                <button type="button" wire:click="clearFecha"
                                    class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                                    Limpiar fecha
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- TABLET + DESKTOP --}}
    <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table wire:key="boletas-table" class="w-full text-sm min-w-[1100px] lg:min-w-0">
                <thead
                    class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-700">
                    <tr class="text-left">

                        {{-- ID --}}
                        <th class="w-[80px] text-center p-2 select-none whitespace-nowrap">
                            <div x-data="{ allOpen: false }" class="flex items-center justify-center gap-2">
                                <button type="button"
                                    class="w-7 h-7 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100 hover:text-gray-800
                                       dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white transition cursor-pointer"
                                    title="Desplegar / Ocultar todos"
                                    @click="allOpen = !allOpen; window.dispatchEvent(new CustomEvent('boletas:toggle-all', { detail: { open: allOpen } }));">
                                    <span x-show="!allOpen">⇵</span>
                                    <span x-show="allOpen" x-cloak>×</span>
                                </button>
                                <span>ID</span>
                            </div>
                        </th>

                        <th class="p-2 select-none whitespace-nowrap w-[34%]">Proyecto</th>
                        <th class="p-2 select-none whitespace-nowrap w-[26%]">Banco</th>
                        <th class="p-2 select-none whitespace-nowrap w-[28%]">Boleta</th>
                        <th class="p-2 select-none whitespace-nowrap text-center w-[120px]">Estado</th>
                        <th class="p-2 select-none whitespace-nowrap text-center w-[170px]">Devuelto</th>
                        <th class="p-2 whitespace-nowrap text-center w-[170px]">Acciones</th>
                    </tr>
                </thead>

                @foreach ($boletas as $bg)
                    @php
                        $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
                        $rest = max(0, (float) $bg->retencion - $totalDev);
                        $devuelta = $totalDev >= (float) $bg->retencion;
                        $colspan = 7;
                    @endphp

                    <tbody x-data="{ open: false }" x-on:boletas:toggle-all.window="open = $event.detail.open"
                        class="divide-y divide-gray-200 dark:divide-neutral-700"
                        wire:key="boleta-row-{{ $bg->id }}">

                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900/60 text-gray-700 dark:text-neutral-200">
                            {{-- ID + toggle --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        class="w-6 h-6 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600
                                           hover:bg-gray-100 hover:text-gray-800
                                           dark:border-neutral-700 dark:text-neutral-300
                                           dark:hover:bg-neutral-700 dark:hover:text-white
                                           transition cursor-pointer"
                                        @click.stop="open = !open" :aria-expanded="open">
                                        <span x-show="!open">+</span>
                                        <span x-show="open" x-cloak>−</span>
                                    </button>

                                    <span class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                        {{ $bg->id }}
                                    </span>
                                </div>
                            </td>

                            {{-- PROYECTO --}}
                            <td class="p-2 align-top">
                                <div class="min-w-0 space-y-0.5 leading-snug">
                                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                        {{ $bg->proyecto?->nombre ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Entidad: {{ $bg->entidad?->nombre ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Agente: {{ $bg->agenteServicio?->nombre ?? '—' }}
                                    </div>
                                </div>
                            </td>

                            {{-- BANCO --}}
                            <td class="p-2 align-top">
                                <div class="min-w-0 space-y-0.5 leading-snug">
                                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                        {{ $bg->bancoEgreso?->nombre ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Cuenta: {{ $bg->bancoEgreso?->numero_cuenta ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Moneda: {{ $bg->bancoEgreso?->moneda ?? ($bg->moneda ?? '—') }}
                                    </div>
                                </div>
                            </td>

                            {{-- BOLETA --}}
                            <td class="p-2 align-top">
                                <div class="min-w-0 space-y-0.5 leading-snug">
                                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                        Nro: {{ $bg->nro_boleta ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Tipo: {{ $bg->tipo }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Emisión: {{ $bg->fecha_emision?->format('Y-m-d') ?? '—' }}
                                        | Venc.: {{ $bg->fecha_vencimiento?->format('Y-m-d') ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                        Total egreso:
                                        <span class="font-semibold text-gray-900 dark:text-neutral-100">
                                            {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                            {{ number_format((float) $bg->total, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- ESTADO --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                <div class="flex items-center justify-center">
                                    @if ($devuelta)
                                        <span
                                            class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                            Devuelta
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded text-xs bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200">
                                            Abierta
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- DEVUELTO --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                <div class="text-center tabular-nums">
                                    <div class="text-sm font-bold text-gray-900 dark:text-neutral-100">
                                        {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                        {{ number_format((float) $totalDev, 2, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        Restante: {{ number_format((float) $rest, 2, ',', '.') }}
                                    </div>
                                </div>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                <div class="flex items-center justify-center gap-2">

                                    <button type="button" wire:click="openDevolucion({{ $bg->id }})"
                                        @disabled($rest <= 0)
                                        class="px-3 py-1.5 rounded-lg border transition text-sm
                                        {{ $rest <= 0
                                            ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                            : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }}">
                                        Devolver
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- DETALLE (SOLO DEVOLUCIONES) --}}
                        <tr x-show="open" x-cloak
                            class="bg-gray-50/60 dark:bg-neutral-900/40 border-t border-gray-200 dark:border-neutral-700">
                            <td class="p-3 md:p-4" colspan="{{ $colspan }}">

                                <div
                                    class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">

                                    <div class="p-3">
                                        <div class="border rounded-lg overflow-hidden dark:border-neutral-800">
                                            <table class="w-full text-sm">
                                                <thead
                                                    class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-800">
                                                    <tr class="text-left text-xs text-gray-600 dark:text-neutral-300">
                                                        <th class="p-2 w-[5%] text-center">#</th>
                                                        <th class="p-2 w-[35%]">Banco</th>
                                                        <th class="p-2 w-[22%]">Fecha</th>
                                                        <th class="p-2 w-[18%] text-right">Monto</th>
                                                        <th class="p-2 w-[15%]">Nro Op.</th>
                                                        <th class="p-2 w-[5%] text-center">Acc.</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                                    @forelse(($bg->devoluciones ?? collect()) as $dv)
                                                        <tr class="text-gray-700 dark:text-neutral-200">
                                                            <td class="p-2 text-center">{{ $loop->iteration }}</td>

                                                            <td class="p-2">
                                                                <div class="font-medium truncate">
                                                                    {{ $dv->banco?->nombre ?? '—' }}
                                                                </div>
                                                                <div
                                                                    class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                                                    {{ $dv->banco?->numero_cuenta ?? '—' }}
                                                                    ({{ $dv->banco?->moneda ?? '—' }})
                                                                </div>
                                                            </td>

                                                            <td class="p-2 text-xs">
                                                                {{ $dv->fecha_devolucion?->format('Y-m-d H:i') ?? '—' }}
                                                            </td>

                                                            <td class="p-2 text-right tabular-nums font-semibold">
                                                                {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                                                {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                                            </td>

                                                            <td class="p-2 text-xs truncate">
                                                                {{ $dv->nro_transaccion ?? '—' }}
                                                            </td>

                                                            <td class="p-2 text-center">
                                                                <button type="button"
                                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-300 text-red-700
                                                                       hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition cursor-pointer"
                                                                    title="Eliminar devolución"
                                                                    wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }})">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-4 h-4" viewBox="0 0 24 24"
                                                                        fill="none" stroke="currentColor"
                                                                        stroke-width="2" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path d="M3 6h18" />
                                                                        <path d="M8 6V4h8v2" />
                                                                        <path d="M6 6l1 16h10l1-16" />
                                                                        <path d="M10 11v6" />
                                                                        <path d="M14 11v6" />
                                                                    </svg>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6"
                                                                class="p-3 text-center text-gray-500 dark:text-neutral-400">
                                                                No hay devoluciones registradas.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>

                                            </table>
                                        </div>
                                    </div>

                                </div>

                            </td>
                        </tr>

                    </tbody>
                @endforeach

                @if ($boletas->count() === 0)
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        <tr>
                            <td class="p-4 text-center text-gray-500 dark:text-neutral-400" colspan="7">
                                Sin resultados.
                            </td>
                        </tr>
                    </tbody>
                @endif

            </table>
        </div>
    </div>

    {{-- MOBILE --}}
    <div class="md:hidden space-y-3">
        @forelse($boletas as $bg)
            @php
                $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
                $rest = max(0, (float) $bg->retencion - $totalDev);
                $devuelta = $totalDev >= (float) $bg->retencion;
            @endphp

            <div x-data="{ open: false }"
                class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">

                {{-- HEADER --}}
                <button type="button" @click="open = !open"
                    class="w-full px-4 py-3 flex items-start justify-between gap-3 text-left
                       hover:bg-gray-50 dark:hover:bg-neutral-900 transition">

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="text-sm font-extrabold text-gray-900 dark:text-neutral-100">
                                #{{ $bg->id }}
                            </div>

                            @if ($devuelta)
                                <span
                                    class="px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                    Devuelta
                                </span>
                            @else
                                <span
                                    class="px-2 py-0.5 rounded-full text-[11px] bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200">
                                    Abierta
                                </span>
                            @endif
                        </div>

                        <div class="mt-1 truncate text-sm font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $bg->proyecto?->nombre ?? '—' }}
                        </div>

                        <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-neutral-400">
                            Nro: {{ $bg->nro_boleta ?? '—' }} • {{ $bg->tipo }}
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <div class="text-xs text-gray-500 dark:text-neutral-400">Devuelto</div>
                        <div class="text-sm font-bold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                            {{ number_format((float) $totalDev, 2, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                            Rest: {{ number_format((float) $rest, 2, ',', '.') }}
                        </div>
                    </div>
                </button>

                {{-- BODY --}}
                <div x-show="open" x-cloak class="px-4 pb-4 pt-2 space-y-3">

                    {{-- PROYECTO --}}
                    <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                        <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                            Proyecto
                        </div>

                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $bg->proyecto?->nombre ?? '—' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                            Entidad: {{ $bg->entidad?->nombre ?? '—' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                            Agente: {{ $bg->agenteServicio?->nombre ?? '—' }}
                        </div>
                    </div>

                    {{-- BANCO --}}
                    <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                        <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                            Banco egreso
                        </div>

                        <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $bg->bancoEgreso?->nombre ?? '—' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                            Cuenta: {{ $bg->bancoEgreso?->numero_cuenta ?? '—' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                            Moneda: {{ $bg->bancoEgreso?->moneda ?? ($bg->moneda ?? '—') }}
                        </div>
                    </div>

                    {{-- BOLETA --}}
                    <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                        <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                            Boleta
                        </div>

                        <div class="space-y-2 text-sm">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Nro</div>
                                <div class="font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $bg->nro_boleta ?? '—' }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Emisión</div>
                                    <div class="font-medium text-gray-900 dark:text-neutral-100">
                                        {{ $bg->fecha_emision?->format('Y-m-d') ?? '—' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Vencimiento</div>
                                    <div class="font-medium text-gray-900 dark:text-neutral-100">
                                        {{ $bg->fecha_vencimiento?->format('Y-m-d') ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Total egreso:
                                <span class="font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                                    {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                    {{ number_format((float) $bg->total, 2, ',', '.') }}
                                </span>
                            </div>

                            @if (!empty($bg->observacion))
                                <div class="text-xs text-gray-500 dark:text-neutral-400 break-words">
                                    {{ $bg->observacion }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- DEVOLUCIONES --}}
                    <div
                        class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                        <div class="px-3 py-2 border-b dark:border-neutral-800">
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                Devoluciones ({{ $bg->devoluciones?->count() ?? 0 }})
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Total: {{ number_format((float) $totalDev, 2, ',', '.') }} •
                                Restante: {{ number_format((float) $rest, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="p-3 space-y-2">
                            @forelse(($bg->devoluciones ?? collect()) as $dv)
                                <div
                                    class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-semibold truncate text-gray-900 dark:text-neutral-100">
                                                {{ $dv->banco?->nombre ?? '—' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                                {{ $dv->banco?->numero_cuenta ?? '—' }}
                                                ({{ $dv->banco?->moneda ?? '—' }})
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $dv->fecha_devolucion?->format('Y-m-d H:i') ?? '—' }}
                                                • Op: {{ $dv->nro_transaccion ?? '—' }}
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="text-xs text-gray-500 dark:text-neutral-400">Monto</div>
                                            <div
                                                class="text-sm font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                                {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                            </div>

                                            <button type="button"
                                                class="mt-2 inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-red-300 text-red-700
                                                   hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition"
                                                wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }})">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-sm text-gray-500 dark:text-neutral-400 py-3">
                                    No hay devoluciones registradas.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ACCIONES --}}
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" wire:click="openDevolucion({{ $bg->id }})"
                            @disabled($rest <= 0)
                            class="px-4 py-2 rounded-lg border text-sm font-semibold transition
                            {{ $rest <= 0
                                ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }}">
                            Devolver
                        </button>
                    </div>

                </div>
            </div>

        @empty
            <div class="p-4 text-center text-gray-500 dark:text-neutral-400">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $boletas->links() }}
    </div>

    {{-- ===================== MODAL BOLETA (SOLO CREAR) ===================== --}}
    <x-ui.modal wire:key="boleta-garantia-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal"
        title="Nueva Boleta de Garantía" maxWidth="sm:max-w-xl md:max-w-4xl" onClose="closeModal">

        <div class="space-y-3 sm:space-y-4">

            <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos principales</div>
                </div>

                <div class="p-3 sm:p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                        <div>
                            <label class="block text-sm mb-1">Agente de servicio <span
                                    class="text-red-500">*</span></label>
                            <select wire:model.live="agente_servicio_id"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                <option value="">Seleccione…</option>
                                @foreach ($agentes as $a)
                                    <option value="{{ $a->id }}">{{ $a->nombre }} — CI:
                                        {{ $a->ci ?? '—' }}</option>
                                @endforeach
                            </select>
                            @error('agente_servicio_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Tipo <span class="text-red-500">*</span></label>
                            <select wire:model.live="tipo"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                <option value="SERIEDAD">Garantía de Seriedad de Propuesta</option>
                                <option value="CUMPLIMIENTO">Garantía de Cumplimiento de Contrato</option>
                            </select>
                            @error('tipo')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Nro. Boleta <span class="text-red-500">*</span></label>
                            <input wire:model.live="nro_boleta" placeholder="Ej: BG-001"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('nro_boleta')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Entidad <span class="text-red-500">*</span></label>
                            <select wire:model.live="entidad_id"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                <option value="">Seleccione…</option>
                                @foreach ($entidades as $en)
                                    <option value="{{ $en->id }}">{{ $en->nombre }}</option>
                                @endforeach
                            </select>
                            @error('entidad_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Proyecto <span class="text-red-500">*</span></label>
                            <select wire:model.live="proyecto_id" @disabled($this->proyectoBloqueado)
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                                <option value="">
                                    {{ $this->proyectoBloqueado ? 'Seleccione entidad primero…' : 'Seleccione…' }}
                                </option>
                                @foreach ($this->proyectosEntidad as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Banco egreso <span class="text-red-500">*</span></label>
                            <select wire:model.live="banco_egreso_id"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                <option value="">Seleccione…</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b->id }}">{{ $b->nombre }} —
                                        {{ $b->numero_cuenta }} ({{ $b->moneda }})</option>
                                @endforeach
                            </select>
                            @error('banco_egreso_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Moneda <span class="text-red-500">*</span></label>
                            <select wire:model.live="moneda"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                <option value="BOB">BOB</option>
                                <option value="USD">USD</option>
                            </select>

                            @if ($banco_egreso_id && $monedaBanco && $moneda !== $monedaBanco)
                                <div class="text-xs text-red-600 mt-1">
                                    La moneda del banco ({{ $monedaBanco }}) no coincide.
                                </div>
                            @endif

                            @error('moneda')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Fecha emisión</label>
                            <input type="date" wire:model="fecha_emision"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('fecha_emision')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Fecha vencimiento</label>
                            <input type="date" wire:model="fecha_vencimiento"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('fecha_vencimiento')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- MONTOS --}}
            <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                        Montos (Total = Retención + Comisión)
                    </div>
                </div>

                <div class="p-3 sm:p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                        <div>
                            <label class="block text-sm mb-1">Retención <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model.blur="retencion_formatted"
                                placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('retencion')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Comisión <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model.blur="comision_formatted"
                                placeholder="0,00"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('comision')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Total egreso (auto)</label>
                            <input type="text" readonly value="{{ $total_formatted }}"
                                class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none" />

                            @if ($total_excede_saldo)
                                <div class="text-xs text-red-600 mt-1">El total excede el saldo del banco.</div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- IMPACTO BANCO --}}
            <div class="rounded-lg border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
                <div class="px-3 sm:px-4 py-2 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Impacto banco egreso</div>
                </div>

                <div class="p-3 sm:p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo actual</div>
                            <div class="font-semibold tabular-nums">
                                {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                                <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo después</div>
                            <div class="font-semibold tabular-nums">
                                {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                                <span class="text-xs">{{ $monedaBanco === 'USD' ? '$' : 'Bs' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- OBS --}}
            <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Observación</div>
                </div>

                <div class="p-3 sm:p-4">
                    <textarea wire:model.live="observacion" rows="3" placeholder="Detalle adicional (opcional)…"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40"></textarea>
                    @error('observacion')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>

        @slot('footer')
            <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">
                <button type="button" wire:click="saveBoleta" wire:loading.attr="disabled" wire:target="saveBoleta"
                    @disabled(!$this->puedeGuardar)
                    class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveBoleta">Guardar</span>
                    <span wire:loading wire:target="saveBoleta">Guardando…</span>
                </button>

                <button type="button" wire:click="closeModal"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- ===================== MODAL DEVOLUCIÓN ===================== --}}
    <x-ui.modal wire:key="boleta-garantia-devolucion-modal-{{ $openDevolucionModal ? 'open' : 'closed' }}"
        model="openDevolucionModal" title="Registrar Devolución" maxWidth="sm:max-w-xl md:max-w-2xl"
        onClose="closeDevolucionModal">

        <div class="space-y-3 sm:space-y-4">

            <div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos de devolución</div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Devuelto: {{ number_format((float) $this->devolucionTotalDevuelto, 2, ',', '.') }}
                        | Restante: {{ number_format((float) $this->devolucionRestante, 2, ',', '.') }}
                    </div>
                </div>

                <div class="p-3 sm:p-4 space-y-3">

                    <div>
                        <label class="block text-sm mb-1">Banco destino <span class="text-red-500">*</span></label>
                        <select wire:model.live="banco_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <option value="">Seleccione…</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b->id }}">{{ $b->nombre }} — {{ $b->numero_cuenta }}
                                    ({{ $b->moneda }})
                                </option>
                            @endforeach
                        </select>
                        @error('banco_id')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Fecha devolución <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" wire:model="fecha_devolucion"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('fecha_devolucion')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Nro. transacción</label>
                            <input wire:model.live="nro_transaccion"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            @error('nro_transaccion')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Monto (máx = restante) <span
                                class="text-red-500">*</span></label>
                        <input type="text" inputmode="decimal" wire:model.blur="devol_monto_formatted"
                            placeholder="0,00"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />

                        @if ($devol_monto > $this->devolucionRestante)
                            <div class="text-xs text-red-600 mt-1">El monto excede el restante.</div>
                        @endif

                        @error('devol_monto')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Observación</label>
                        <textarea wire:model.live="devolucion_observacion" rows="3"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40"></textarea>
                        @error('devolucion_observacion')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

        </div>

        @slot('footer')
            <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">

                <button type="button" wire:click="saveDevolucion" wire:loading.attr="disabled"
                    wire:target="saveDevolucion" @disabled(!$this->puedeGuardarDevolucion)
                    class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer bg-emerald-600 text-white hover:opacity-90
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveDevolucion">Guardar devolución</span>
                    <span wire:loading wire:target="saveDevolucion">Guardando…</span>
                </button>

                <button type="button" wire:click="closeDevolucionModal"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                           border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                           hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

            </div>
        @endslot
    </x-ui.modal>

    {{-- ===================== MODAL CONFIRM DELETE DEVOLUCIÓN ===================== --}}
    <x-ui.modal wire:key="delete-devolucion-modal-{{ $openDeleteDevolucionModal ? 'open' : 'closed' }}"
        model="openDeleteDevolucionModal" title="Eliminar devolución" maxWidth="sm:max-w-md"
        onClose="closeDeleteDevolucionModal">

        <div class="text-sm text-gray-700 dark:text-neutral-200">
            ¿Seguro que quieres eliminar esta devolución?
            <div class="mt-2 text-xs text-gray-500 dark:text-neutral-400">
                Se revertirá el monto en el banco destino (se restará).
            </div>
        </div>

        @slot('footer')
            <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3">

                <button type="button" wire:click="deleteDevolucion"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    Eliminar
                </button>

                <button type="button" wire:click="closeDeleteDevolucionModal"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border
                           border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                           hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

            </div>
        @endslot
    </x-ui.modal>

</div>
