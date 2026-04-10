<x-ui.modal wire:key="rendicion-editor-{{ $editorRendicionId ?? 'none' }}" model="openEditor"
    maxWidth="sm:max-w-4xl lg:max-w-7xl" onClose="closeEditor">


    <x-slot:title>
        <div class="flex items-center gap-4">
            <span>Planilla de Rendición</span>

            @if ($editorRendicionId)
                <div class="hidden sm:flex items-center gap-2 font-normal">

                    <div
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white dark:bg-neutral-800/50 border border-gray-200 dark:border-neutral-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                        </svg>

                        <span class="text-[11px] font-medium text-gray-500 dark:text-neutral-400">Nro:</span>
                        <span
                            class="text-[11px] font-bold text-[#1a202c] dark:text-neutral-100 uppercase tracking-wide">
                            {{ $editorRendicionNro ?? '—' }}
                        </span>
                    </div>

                    <div
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white dark:bg-neutral-800/50 border border-gray-200 dark:border-neutral-700 shadow-sm">
                        {{-- icon user --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-500"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span class="text-[11px] font-medium text-gray-500 dark:text-neutral-400">Agente:</span>
                        <span
                            class="text-[11px] font-bold text-[#1a202c] dark:text-neutral-100 uppercase tracking-wide">
                            {{ $editorAgenteNombre ?? '—' }}
                        </span>
                    </div>

                    <div
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white dark:bg-neutral-800/50 border border-gray-200 dark:border-neutral-700 shadow-sm">
                        {{-- icon calendar --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-500"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span class="text-[11px] font-medium text-gray-500 dark:text-neutral-400">Fecha:</span>
                        <span class="text-[11px] font-bold text-[#1a202c] dark:text-neutral-100">
                            {{ $editorFecha ? \Carbon\Carbon::parse($editorFecha)->format('d/m/Y H:i') : '—' }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </x-slot:title>

    @php
        $hasCompras = !empty($editorCompras) && count($editorCompras) > 0;
        $hasDevoluciones = !empty($editorDevoluciones) && count($editorDevoluciones) > 0;
        $hasMovs = $hasCompras || $hasDevoluciones;
    @endphp

    <div class="space-y-4">
        {{-- CABECERA --}}
        <div
            class="rounded-xl border border-gray-200 bg-white dark:bg-neutral-900 dark:border-neutral-700 shadow-sm flex flex-col lg:flex-row items-center justify-between p-3 gap-6">

            {{-- DERECHA: KPIs (Toman el espacio restante en pantallas grandes) --}}
            <div class="flex items-center gap-3 flex-1 w-full flex-col sm:flex-row">
                {{-- Presupuesto --}}
                <div
                    class="flex-1 w-full rounded border border-gray-200 bg-white dark:bg-neutral-800 dark:border-neutral-700 py-1.5 px-3 shadow-sm">
                    <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500 font-bold">
                        Presupuesto ({{ $editorMonedaBase ?? 'BOB' }})
                    </div>
                    <div
                        class="text-[16px] font-bold tabular-nums text-[#1a202c] dark:text-neutral-100 leading-none mt-1">
                        {{ number_format((float) ($editorPresupuestoTotal ?? 0), 2, ',', '.') }}
                    </div>
                </div>

                {{-- Rendido --}}
                <div
                    class="flex-1 w-full rounded border border-gray-200 bg-white dark:bg-neutral-800 dark:border-neutral-700 py-1.5 px-3 shadow-sm">
                    <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500 font-bold">
                        Rendido ({{ $editorMonedaBase ?? 'BOB' }})
                    </div>
                    <div
                        class="text-[16px] font-bold tabular-nums text-[#009b5a] dark:text-emerald-400 leading-none mt-1">
                        {{ number_format((float) ($editorRendidoTotal ?? 0), 2, ',', '.') }}
                    </div>
                </div>

                {{-- Saldo --}}
                <div
                    class="flex-1 w-full rounded border border-gray-200 bg-white dark:bg-neutral-800 dark:border-neutral-700 py-1.5 px-3 shadow-sm">
                    <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500 font-bold">
                        Saldo ({{ $editorMonedaBase ?? 'BOB' }})
                    </div>
                    <div
                        class="text-[16px] font-bold tabular-nums leading-none mt-1
                        {{ (float) ($editorSaldo ?? 0) <= 0
                            ? 'text-emerald-700 dark:text-emerald-300'
                            : 'text-[#e11d48] dark:text-rose-400' }}">
                        {{ number_format((float) ($editorSaldo ?? 0), 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTROS DEL MODAL --}}
        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-sm p-3">
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end flex-wrap">

                {{-- Toggle modo --}}
                <div class="flex items-center gap-1 bg-gray-100 dark:bg-neutral-800 rounded-lg p-1 shrink-0">
                    <button type="button" wire:click="$set('editorFiltroModo','mes')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all
                            {{ $editorFiltroModo === 'mes' ? 'bg-white dark:bg-neutral-700 text-gray-900 dark:text-neutral-100 shadow-sm' : 'text-gray-500 dark:text-neutral-400 hover:text-gray-700' }}">
                        Por mes
                    </button>
                    <button type="button" wire:click="$set('editorFiltroModo','rango')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all
                            {{ $editorFiltroModo === 'rango' ? 'bg-white dark:bg-neutral-700 text-gray-900 dark:text-neutral-100 shadow-sm' : 'text-gray-500 dark:text-neutral-400 hover:text-gray-700' }}">
                        Rango
                    </button>
                </div>

                {{-- Input mes --}}
                @if ($editorFiltroModo === 'mes')
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500 dark:text-neutral-400 shrink-0">Mes:</label>
                        <input type="month" wire:model.live="editorFiltroMes"
                            class="text-xs rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer" />
                    </div>
                @else
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="text-xs text-gray-500 dark:text-neutral-400 shrink-0">Desde:</label>
                        <input type="date" wire:model.live="editorFiltroDesde"
                            class="text-xs rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer" />
                        <label class="text-xs text-gray-500 dark:text-neutral-400 shrink-0">Hasta:</label>
                        <input type="date" wire:model.live="editorFiltroHasta"
                            class="text-xs rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer" />
                    </div>
                @endif

                {{-- Filtro proyecto --}}
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 dark:text-neutral-400 shrink-0">Proyecto:</label>
                    <select wire:model.live="editorFiltroProyecto"
                        class="text-xs rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">Todos</option>
                        @foreach ($editorProyectos as $p)
                            <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Limpiar --}}
                @if ($editorFiltroMes || $editorFiltroDesde || $editorFiltroHasta || $editorFiltroProyecto)
                    <button type="button" wire:click="clearEditorFiltros"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 underline shrink-0 transition-colors">
                        Limpiar filtros
                    </button>
                @endif

            </div>
        </div>

        {{-- TABLAS --}}
        @if ($hasMovs)
            <div class="grid grid-cols-1 gap-4">

                {{-- DEVOLUCIONES --}}
                @if ($hasDevoluciones)
                    <div
                        class="rounded-xl border border-emerald-200/60 dark:border-emerald-800/30 bg-white dark:bg-neutral-900/40 overflow-hidden shadow-sm mb-4">
                        <div
                            class="p-2 border-b border-emerald-100 dark:border-emerald-800/30 bg-emerald-50/50 dark:bg-emerald-900/10 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-800/50 text-emerald-600 dark:text-emerald-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-emerald-800 dark:text-emerald-400">Devoluciones
                                </div>
                            </div>
                            <div class="text-xs text-emerald-600 dark:text-emerald-400/80">
                                Total devolución (base):
                                <span class="font-bold tabular-nums ml-1 text-emerald-700 dark:text-emerald-300">
                                    {{ number_format((float) ($editorTotalDevolucionesBase ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- DESKTOP: TABLA --}}
                        <div class="hidden md:block overflow-visible">
                            <table class="w-full text-sm">
                                <thead
                                    class="bg-gray-50/50 dark:bg-neutral-800/50 border-b border-gray-100 dark:border-neutral-700/50">
                                    <tr
                                        class="text-left text-[11px] uppercase tracking-wider text-gray-500 dark:text-neutral-400 font-semibold">
                                        <th class="p-2 text-center w-[5%]">Nro</th>
                                        <th class="p-2 text-center w-[10%]">Fecha</th>
                                        <th class="p-2 w-[30%]">Banco</th>
                                        <th class="p-2 w-[15%]">Transacción</th>
                                        <th class="p-2 w-[20%]">Detalle</th>
                                        <th class="p-2 w-[10%]">Monto</th>
                                        <th class="p-2 text-center w-[10%]">Acc.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/80">
                                    @foreach ($editorDevoluciones ?? [] as $i => $m)
                                        @php
                                            $isDevHighlighted =
                                                isset($highlight_devolucion_id) &&
                                                $highlight_devolucion_id &&
                                                (int) $m->id === (int) $highlight_devolucion_id;
                                        @endphp
                                        <tr wire:key="dev-table-{{ $m->id }}"
                                            @if ($isDevHighlighted) id="devolucion-highlight-{{ $m->id }}" @endif
                                            class="transition group
                                            {{ $isDevHighlighted ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10' }}">

                                            {{-- NRO (con highlight si corresponde) --}}
                                            <td
                                                class="p-1 text-center font-medium
                                                {{ $isDevHighlighted ? 'border-l-4 border-emerald-400' : 'border-l-4 border-transparent' }}">
                                                @if ($isDevHighlighted)
                                                    <span
                                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-400 text-white font-bold text-[10px] animate-pulse"
                                                        title="Esta es la devolución de la transacción">{{ $i + 1 }}</span>
                                                @else
                                                    <span
                                                        class="text-gray-400 dark:text-neutral-500">{{ $i + 1 }}</span>
                                                @endif
                                            </td>

                                            {{-- FECHA --}}
                                            <td
                                                class="p-1 text-gray-600 dark:text-neutral-300 whitespace-nowrap text-center">
                                                <div class="text-xs">{{ $m->fecha?->format('d/m/Y') ?? '—' }}</div>
                                                <div class="text-[10px] text-gray-400">
                                                    {{ $m->fecha?->format('H:i') ?? '' }}</div>
                                            </td>

                                            {{-- BANCO --}}
                                            <td
                                                class="p-1 text-gray-700 dark:text-neutral-200 font-medium whitespace-nowrap">
                                                <div class="text-sm font-semibold">
                                                    {{ $m->banco?->nombre ?? '—' }}
                                                </div>
                                                @if ($m->banco?->titular)
                                                    <div class="text-[10px] text-gray-500 font-normal">
                                                        {{ $m->banco->titular }}
                                                    </div>
                                                @endif
                                                <div
                                                    class="text-[10px] text-gray-500 font-normal mt-0.5 flex items-center gap-1">
                                                    @if ($m->tipo_cambio && $m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                        <span>TC:
                                                            {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}</span>
                                                        <span class="text-gray-300">·</span>
                                                    @endif
                                                    <span
                                                        class="font-semibold text-gray-700 dark:text-neutral-200">{{ number_format((float) $m->monto, 2, ',', '.') }}
                                                        {{ $m->moneda }}</span>
                                                </div>
                                            </td>

                                            {{-- NRO TRANSACCION --}}
                                            <td class="p-1 text-gray-600 dark:text-neutral-300 whitespace-nowrap">
                                                {{ $m->nro_transaccion ?? '—' }}
                                            </td>

                                            {{-- OBSERVACION --}}
                                            <td class="p-1 text-gray-500 dark:text-neutral-400 text-xs"
                                                title="{{ $m->observacion }}"> {{ $m->observacion ?: '—' }}
                                            </td>

                                            {{-- MONTO BASE --}}
                                            <td
                                                class="p-1 tabular-nums text-emerald-700 dark:text-emerald-400 font-semibold whitespace-nowrap">
                                                <div>
                                                    {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                    <span
                                                        class="text-[10px] opacity-70">{{ $editorMonedaBase ?? 'BOB' }}</span>
                                                </div>

                                                <div class="text-[10px] text-gray-500 flex items-center ">
                                                    @if ($m->tipo_cambio && $m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                        <span>TC:
                                                            {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }} -
                                                            {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                            {{ $m->moneda }}</span>
                                                    @endif
                                                </div>

                                            </td>

                                            {{-- ACCIONES --}}
                                            <td class="p-1 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    {{-- EDITAR --}}
                                                    @can('agente_presupuestos.delete_movement')
                                                        <button type="button"
                                                            wire:click="openEditMovimiento({{ $m->id }})"
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-amber-600 border-amber-300 hover:bg-amber-50 hover:border-amber-400 dark:bg-neutral-900 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/20 shadow-sm"
                                                            title="Editar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path
                                                                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                                <path
                                                                    d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                            </svg>
                                                        </button>
                                                    @endcan

                                                    {{-- FOTO --}}
                                                    @if (!empty($m->foto_path))
                                                        @php
                                                            $ext = strtolower(
                                                                pathinfo($m->foto_path, PATHINFO_EXTENSION),
                                                            );
                                                            $esPdf = $ext === 'pdf';
                                                            $esImagen = in_array($ext, [
                                                                'jpg',
                                                                'jpeg',
                                                                'png',
                                                                'webp',
                                                                'bmp',
                                                            ]);
                                                        @endphp
                                                        @if ($esPdf)
                                                            <a href="{{ asset('storage/' . $m->foto_path) }}"
                                                                target="_blank"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-500 border-red-200 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                                                title="Ver PDF">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path
                                                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                                    <polyline points="14 2 14 8 20 8" />
                                                                    <line x1="16" y1="13"
                                                                        x2="8" y2="13" />
                                                                    <line x1="16" y1="17"
                                                                        x2="8" y2="17" />
                                                                    <polyline points="10 9 9 9 8 9" />
                                                                </svg>
                                                            </a>
                                                        @elseif ($esImagen)
                                                            <button type="button"
                                                                wire:click="openFotoComprobante('{{ asset('storage/' . $m->foto_path) }}')"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-500 border-indigo-200 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                                title="Ver imagen">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <rect x="3" y="3" width="18" height="18"
                                                                        rx="2" ry="2" />
                                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                                    <path d="M21 15l-5-5L5 21" />
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <a href="{{ asset('storage/' . $m->foto_path) }}"
                                                                target="_blank"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-400 dark:bg-neutral-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-900/20 shadow-sm"
                                                                title="Ver archivo">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path
                                                                        d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                                                                    <polyline points="13 2 13 9 20 9" />
                                                                </svg>
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                            title="Sin comprobante">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <rect x="3" y="3" width="18" height="18"
                                                                    rx="2" ry="2" />
                                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                                <path d="M21 15l-5-5L5 21" />
                                                            </svg>
                                                        </span>
                                                    @endif

                                                    {{-- ELIMINAR --}}
                                                    @can('agente_presupuestos.delete_movement')
                                                        <button type="button" x-data
                                                            x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                                            title="Eliminar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path d="M3 6h18" />
                                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                                <line x1="10" y1="11" x2="10"
                                                                    y2="17" />
                                                                <line x1="14" y1="11" x2="14"
                                                                    y2="17" />
                                                            </svg>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- MOBILE: CARDS --}}
                        <div class="md:hidden p-3 space-y-3">
                            @foreach ($editorDevoluciones ?? [] as $i => $m)
                                @php
                                    $isDevHighlightedMob =
                                        isset($highlight_devolucion_id) &&
                                        $highlight_devolucion_id &&
                                        (int) $m->id === (int) $highlight_devolucion_id;
                                @endphp
                                <div wire:key="dev-mob-{{ $m->id }}"
                                    @if ($isDevHighlightedMob) id="mob-devolucion-highlight-{{ $m->id }}" @endif
                                    class="rounded-xl border-l-4 border-t border-r border-b border-t-gray-200 border-r-gray-200 border-b-gray-200 dark:border-t-neutral-700 dark:border-r-neutral-700 dark:border-b-neutral-700 border-l-emerald-400 dark:border-l-emerald-500 p-3 transition-colors
                                        {{ $isDevHighlightedMob ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-white dark:bg-neutral-900/20' }}">

                                    {{-- FILA 1: # · Banco + Acciones --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                                <span
                                                    class="text-xs font-normal text-gray-400 dark:text-neutral-500">#{{ $i + 1 }}</span>
                                                <span class="mx-1 text-gray-300 dark:text-neutral-600">·</span>
                                                {{ $m->banco?->nombre ?? '—' }}
                                            </div>
                                            @if ($m->banco?->titular)
                                                <div class="text-[11px] text-gray-400 dark:text-neutral-500 truncate">
                                                    {{ $m->banco->titular }}
                                                </div>
                                            @endif
                                        </div>

                                        {{-- ACCIONES --}}
                                        <div class="flex items-center gap-1 shrink-0">
                                            {{-- EDITAR --}}
                                            @can('agente_presupuestos.delete_movement')
                                                <button type="button"
                                                    wire:click="openEditMovimiento({{ $m->id }})"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-amber-600 border-amber-300 hover:bg-amber-50 hover:border-amber-400 dark:bg-neutral-900 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/20 shadow-sm"
                                                    title="Editar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                    </svg>
                                                </button>
                                            @endcan

                                            {{-- VER FOTO --}}
                                            @if (!empty($m->foto_path))
                                                @php
                                                    $ext = strtolower(pathinfo($m->foto_path, PATHINFO_EXTENSION));
                                                    $esPdf = $ext === 'pdf';
                                                    $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                                                @endphp
                                                @if ($esPdf)
                                                    <a href="{{ asset('storage/' . $m->foto_path) }}" target="_blank"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                                        title="Ver PDF">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path
                                                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                            <polyline points="14 2 14 8 20 8" />
                                                            <line x1="9" y1="13" x2="15"
                                                                y2="13" />
                                                            <line x1="9" y1="17" x2="15"
                                                                y2="17" />
                                                            <line x1="9" y1="9" x2="11"
                                                                y2="9" />
                                                        </svg>
                                                    </a>
                                                @elseif($esImagen)
                                                    <button type="button"
                                                        wire:click="openFotoComprobante('{{ asset('storage/' . $m->foto_path) }}')"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                        title="Ver imagen">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18"
                                                                rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <polyline points="21 15 16 10 5 21" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <a href="{{ asset('storage/' . $m->foto_path) }}" target="_blank"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400 dark:bg-neutral-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-900/20 shadow-sm"
                                                        title="Ver archivo">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path
                                                                d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                            </path>
                                                            <polyline points="13 2 13 9 20 9"></polyline>
                                                        </svg>
                                                    </a>
                                                @endif
                                            @else
                                                <span
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                    title="Sin comprobante">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18"
                                                            rx="2" ry="2" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <polyline points="21 15 16 10 5 21" />
                                                    </svg>
                                                </span>
                                            @endif

                                            {{-- ELIMINAR --}}
                                            @can('agente_presupuestos.delete_movement')
                                                <button type="button" x-data
                                                    x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                                    title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M3 6h18" />
                                                        <path d="M8 6V4h8v2" />
                                                        <path d="M6 6l1 16h10l1-16" />
                                                        <line x1="10" y1="11" x2="10"
                                                            y2="17"></line>
                                                        <line x1="14" y1="11" x2="14"
                                                            y2="17"></line>
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>

                                    {{-- FILA 2: Fecha + Tx | Monto --}}
                                    <div
                                        class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 flex items-start justify-between gap-2">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400 space-y-0.5">
                                            <div>
                                                {{ $m->fecha?->format('d/m/Y H:i') ?? '—' }}
                                                @if ($m->nro_transaccion)
                                                    <span class="mx-0.5 text-gray-300">·</span>Tx:
                                                    {{ $m->nro_transaccion }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- OBSERVACIÓN --}}
                                    @if (!empty($m->observacion))
                                        <div
                                            class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 text-xs text-gray-500 dark:text-neutral-400">
                                            <span class="font-medium text-gray-600 dark:text-neutral-300">Obs:</span>
                                            {{ $m->observacion }}
                                        </div>
                                    @endif

                                    {{-- MONTO --}}
                                    <div
                                        class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 flex items-center justify-between">
                                        <span
                                            class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500">Monto</span>
                                        <span
                                            class="text-sm font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">
                                            {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                            {{ $editorMonedaBase ?? 'BOB' }}
                                        </span>
                                    </div>

                                    {{-- BASE --}}
                                    @if ($m->tipo_cambio && $m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                        <div
                                            class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 flex justify-between">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500">Base</span>
                                            <span
                                                class="text-[11.5px] font-semibold tabular-nums text-gray-700 dark:text-gray-400">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }} — TC:
                                                {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


                {{-- COMPRAS --}}
                @if ($hasCompras)
                    <div
                        class="rounded-xl border border-blue-200/60 dark:border-blue-800/30 bg-white dark:bg-neutral-900/40 overflow-hidden shadow-sm mb-4">
                        <div
                            class="p-2 border-b border-blue-100 dark:border-blue-800/30 bg-blue-50/50 dark:bg-blue-900/10 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-800/50 text-blue-600 dark:text-blue-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-blue-800 dark:text-blue-400">Compras</div>
                            </div>
                            <div class="text-xs text-blue-600 dark:text-blue-400/80">
                                Total compras (base):
                                <span class="font-bold tabular-nums ml-1 text-blue-700 dark:text-blue-300">
                                    {{ number_format((float) ($editorTotalComprasBase ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- DESKTOP: TABLA --}}
                        <div class="hidden md:block overflow-visible">
                            <table class="w-full text-sm">
                                <thead
                                    class="bg-gray-50/50 dark:bg-neutral-800/50 border-b border-gray-100 dark:border-neutral-700/50">
                                    <tr
                                        class="text-left text-[11px] uppercase tracking-wider text-gray-500 dark:text-neutral-400 font-semibold">
                                        <th class="p-2 text-center w-[5%]">Nro</th>
                                        <th class="p-2 text-center w-[10%]">Fecha</th>
                                        <th class="p-2 w-[30%]">Entidad / Proyecto</th>
                                        <th class="p-2 w-[15%]">Comprobante</th>
                                        <th class="p-2 w-[20%]">Detalle</th>
                                        <th class="p-2 w-[10%]">Monto</th>
                                        <th class="p-2 text-center w-[10%]">Acc.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/80">
                                    @foreach ($editorCompras ?? [] as $i => $m)
                                        @php
                                            $isMovHighlighted =
                                                isset($highlight_movimiento_id) &&
                                                $highlight_movimiento_id &&
                                                (int) $m->id === (int) $highlight_movimiento_id;
                                        @endphp
                                        <tr wire:key="compra-table-{{ $m->id }}"
                                            @if ($isMovHighlighted) id="movimiento-highlight-{{ $m->id }}" @endif
                                            class="transition group
                                            {{ $isMovHighlighted ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-blue-50/30 dark:hover:bg-blue-900/10' }}">

                                            {{-- NRO --}}
                                            <td
                                                class="p-1 text-center font-medium
                                                {{ $isMovHighlighted ? 'border-l-4 border-blue-400' : 'border-l-4 border-transparent' }}">
                                                @if ($isMovHighlighted)
                                                    <span
                                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-400 text-white font-bold text-[10px] animate-pulse"
                                                        title="Este es el movimiento seleccionado">{{ $i + 1 }}</span>
                                                @else
                                                    <span
                                                        class="text-gray-400 dark:text-neutral-500">{{ $i + 1 }}</span>
                                                @endif
                                            </td>

                                            {{-- FECHA --}}
                                            <td
                                                class="p-1 text-gray-600 dark:text-neutral-300 whitespace-nowrap text-center">
                                                <div class="text-xs">{{ $m->fecha?->format('d/m/Y') ?? '—' }}</div>
                                                <div class="text-[10px] text-gray-400">
                                                    {{ $m->fecha?->format('H:i') ?? '' }}</div>
                                            </td>

                                            {{-- ENTIDAD / PROYECTO --}}
                                            <td class="p-1">
                                                <div class="text-sm font-medium text-gray-800 dark:text-neutral-200"
                                                    title="{{ $m->entidad?->nombre }}">
                                                    {{ $m->entidad?->nombre ?? '—' }}</div>
                                                <div class="text-[11px] text-gray-500 dark:text-neutral-400"
                                                    title="{{ $m->proyecto?->nombre }}">
                                                    {{ $m->proyecto?->nombre ?? '—' }}</div>
                                            </td>

                                            {{-- COMPROBANTE --}}
                                            <td class="p-1 text-gray-600 dark:text-neutral-300 text-xs">
                                                <div class="font-medium text-gray-700 dark:text-neutral-200">
                                                    {{ $m->tipo_comprobante ?? '—' }}
                                                    @if (!empty($m->nro_comprobante))
                                                        - {{ $m->nro_comprobante }}
                                                    @endif
                                                </div>

                                            </td>

                                            {{-- DETALLE / OBSERVACIÓN --}}
                                            <td class="p-1 text-gray-500 dark:text-neutral-400 text-xs"
                                                title="{{ $m->observacion }}">
                                                {{ $m->observacion ?: '—' }}
                                            </td>

                                            {{-- MONTO BASE --}}
                                            <td
                                                class="p-1 tabular-nums text-blue-700 dark:text-blue-400 font-semibold whitespace-nowrap">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                <span
                                                    class="text-[10px] opacity-70">{{ $editorMonedaBase ?? 'BOB' }}</span>
                                                <div class="text-[10px] text-gray-500 flex items-center ">
                                                    @if ($m->tipo_cambio && $m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                        <span>TC:
                                                            {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }} -
                                                            {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                            {{ $m->moneda }}</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- ACCIONES --}}
                                            <td class="p-1 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    {{-- EDITAR --}}
                                                    @can('agente_presupuestos.delete_movement')
                                                        <button type="button"
                                                            wire:click="openEditMovimiento({{ $m->id }})"
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-amber-600 border-amber-300 hover:bg-amber-50 hover:border-amber-400 dark:bg-neutral-900 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/20 shadow-sm"
                                                            title="Editar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path
                                                                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                                <path
                                                                    d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                            </svg>
                                                        </button>
                                                    @endcan

                                                    {{-- FOTO --}}
                                                    @if (!empty($m->foto_path))
                                                        @php
                                                            $ext = strtolower(
                                                                pathinfo($m->foto_path, PATHINFO_EXTENSION),
                                                            );
                                                            $esPdf = $ext === 'pdf';
                                                            $esImagen = in_array($ext, [
                                                                'jpg',
                                                                'jpeg',
                                                                'png',
                                                                'webp',
                                                                'bmp',
                                                            ]);
                                                        @endphp
                                                        @if ($esPdf)
                                                            <a href="{{ asset('storage/' . $m->foto_path) }}"
                                                                target="_blank"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-500 border-red-200 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                                                title="Ver PDF">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path
                                                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                                    <polyline points="14 2 14 8 20 8" />
                                                                    <line x1="16" y1="13"
                                                                        x2="8" y2="13" />
                                                                    <line x1="16" y1="17"
                                                                        x2="8" y2="17" />
                                                                    <polyline points="10 9 9 9 8 9" />
                                                                </svg>
                                                            </a>
                                                        @elseif ($esImagen)
                                                            <button type="button"
                                                                wire:click="openFotoComprobante('{{ asset('storage/' . $m->foto_path) }}')"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-500 border-indigo-200 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                                title="Ver imagen">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <rect x="3" y="3" width="18" height="18"
                                                                        rx="2" ry="2" />
                                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                                    <path d="M21 15l-5-5L5 21" />
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <a href="{{ asset('storage/' . $m->foto_path) }}"
                                                                target="_blank"
                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-400 dark:bg-neutral-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-900/20 shadow-sm"
                                                                title="Ver archivo">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path
                                                                        d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                                                                    <polyline points="13 2 13 9 20 9" />
                                                                </svg>
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                            title="Sin comprobante">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <rect x="3" y="3" width="18" height="18"
                                                                    rx="2" ry="2" />
                                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                                <path d="M21 15l-5-5L5 21" />
                                                            </svg>
                                                        </span>
                                                    @endif

                                                    {{-- ELIMINAR --}}
                                                    @can('agente_presupuestos.delete_movement')
                                                        <button type="button" x-data
                                                            x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                                            title="Eliminar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path d="M3 6h18" />
                                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                                <line x1="10" y1="11" x2="10"
                                                                    y2="17" />
                                                                <line x1="14" y1="11" x2="14"
                                                                    y2="17" />
                                                            </svg>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>


                        {{--  MOBILE: CARDS --}}
                        <div class="md:hidden p-3 space-y-3">
                            @foreach ($editorCompras ?? [] as $i => $m)
                                @php
                                    $isMovHighlightedMob =
                                        isset($highlight_movimiento_id) &&
                                        $highlight_movimiento_id &&
                                        (int) $m->id === (int) $highlight_movimiento_id;
                                @endphp
                                <div wire:key="compra-mob-{{ $m->id }}"
                                    @if ($isMovHighlightedMob) id="mob-movimiento-highlight-{{ $m->id }}" @endif
                                    class="rounded-xl border-l-4 border-t border-r border-b border-t-gray-200 border-r-gray-200 border-b-gray-200 dark:border-t-neutral-700 dark:border-r-neutral-700 dark:border-b-neutral-700 border-l-blue-400 dark:border-l-blue-500 p-3 transition-colors
                                        {{ $isMovHighlightedMob ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-neutral-900/20' }}">

                                    {{-- FILA 1: # · Proyecto + Acciones --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="text-sm font-semibold text-gray-900 dark:text-neutral-100 line-clamp-2">
                                                <span
                                                    class="text-xs font-normal text-gray-400 dark:text-neutral-500">#{{ $i + 1 }}</span>
                                                <span class="mx-1 text-gray-300 dark:text-neutral-600">·</span>
                                                {{ $m->proyecto?->nombre ?? '—' }}
                                            </div>
                                            <div class="text-[12px] text-gray-400 dark:text-neutral-500 truncate">
                                                {{ $m->entidad?->nombre ?? '—' }}
                                                <span class="mx-0.5 text-gray-300">·</span>
                                                {{ $m->fecha?->format('d/m/Y H:i') ?? '—' }}
                                            </div>
                                        </div>

                                        {{-- Acciones (editar + foto + eliminar) --}}
                                        <div class="shrink-0 flex items-center gap-1">
                                            {{-- EDITAR --}}
                                            @can('agente_presupuestos.delete_movement')
                                                <button type="button"
                                                    wire:click="openEditMovimiento({{ $m->id }})"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-amber-600 border-amber-300 hover:bg-amber-50 hover:border-amber-400 dark:bg-neutral-900 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/20 shadow-sm"
                                                    title="Editar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                    </svg>
                                                </button>
                                            @endcan

                                            @if (!empty($m->foto_path))
                                                @php
                                                    $ext = strtolower(pathinfo($m->foto_path, PATHINFO_EXTENSION));
                                                    $esPdf = $ext === 'pdf';
                                                    $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                                                @endphp
                                                @if ($esPdf)
                                                    <a href="{{ asset('storage/' . $m->foto_path) }}" target="_blank"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                                        title="Ver PDF">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path
                                                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                            <polyline points="14 2 14 8 20 8" />
                                                            <line x1="9" y1="13" x2="15"
                                                                y2="13" />
                                                            <line x1="9" y1="17" x2="15"
                                                                y2="17" />
                                                            <line x1="9" y1="9" x2="11"
                                                                y2="9" />
                                                        </svg>
                                                    </a>
                                                @elseif($esImagen)
                                                    <button type="button"
                                                        wire:click="openFotoComprobante('{{ asset('storage/' . $m->foto_path) }}')"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                        title="Ver imagen">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18"
                                                                rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <polyline points="21 15 16 10 5 21" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <a href="{{ asset('storage/' . $m->foto_path) }}" target="_blank"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400 dark:bg-neutral-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-900/20 shadow-sm"
                                                        title="Ver archivo">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path
                                                                d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                            </path>
                                                            <polyline points="13 2 13 9 20 9"></polyline>
                                                        </svg>
                                                    </a>
                                                @endif
                                            @else
                                                <span
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                    title="Sin comprobante">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18"
                                                            rx="2" ry="2" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <polyline points="21 15 16 10 5 21" />
                                                    </svg>
                                                </span>
                                            @endif

                                            @can('agente_presupuestos.delete_movement')
                                                <button type="button" x-data
                                                    x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20 shadow-sm"
                                                    title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M3 6h18" />
                                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                        <line x1="10" y1="11" x2="10"
                                                            y2="17"></line>
                                                        <line x1="14" y1="11" x2="14"
                                                            y2="17"></line>
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>

                                    {{-- FILA 2: Comprobante + Monto --}}
                                    <div
                                        class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 flex items-start justify-between gap-2">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400 space-y-0.5">
                                            <div class="flex items-center gap-1 flex-wrap">
                                                <span class="font-medium">{{ $m->tipo_comprobante ?? '—' }}</span>
                                                @if (!empty($m->nro_comprobante))
                                                    <span class="text-gray-300">—</span>
                                                    <span>{{ $m->nro_comprobante }}</span>
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                    {{-- OBSERVACIÓN --}}
                                    @if (!empty($m->observacion))
                                        <div
                                            class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 text-xs text-gray-500 dark:text-neutral-400">
                                            <span class="font-medium text-gray-600 dark:text-neutral-300">OBS:</span>
                                            {{ $m->observacion }}
                                        </div>
                                    @endif

                                    {{-- MONTO --}}
                                    <div
                                        class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 flex items-center justify-between">
                                        <span
                                            class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500">Base</span>
                                        <span
                                            class="text-sm font-semibold tabular-nums text-blue-700 dark:text-blue-400">
                                            {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                            {{ $editorMonedaBase ?? 'BOB' }}
                                        </span>
                                    </div>


                                    {{-- BASE --}}
                                    @if ($m->tipo_cambio && $m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                        <div
                                            class="mt-2 pt-2 border-t border-gray-100 dark:border-neutral-700/50 flex justify-between">
                                            <span
                                                class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-neutral-500">Monto</span>
                                            <span
                                                class="text-[11.5px] font-semibold tabular-nums text-gray-700 dark:text-gray-400">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }} — TC:
                                                {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endif

            </div>
        @else
            <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 p-4">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Aún no hay movimientos</div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                    Usa el boton <span class="font-semibold">+ Movimientos</span> para agregar compras o devoluciones a
                    esta rendición.
                </div>
            </div>
        @endif
    </div>

    @slot('footer')
        @php $saldoActual = (float) ($editorSaldo ?? 0); @endphp
        <div class="flex items-center justify-end gap-2">
            <button type="button" wire:click="closeEditor"
                class="cursor-pointer px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
                Cerrar
            </button>

            {{-- + Movimiento: solo cuando hay saldo --}}
            @if ($editorEstado !== 'cerrado' && $saldoActual > 0)
                @can('agente_presupuestos.register_movement')
                    <button type="button" wire:click="openMovimientoModal1" wire:loading.attr="disabled"
                        wire:target="openMovimientoModal1"
                        class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-lg  transition
                               bg-[#111827] text-white hover:bg-gray-800
                               disabled:opacity-50 disabled:cursor-not-allowed">
                        {{-- ícono recibo/ticket --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 2v20l3-2 2 2 3-2 3 2 2-2 3 2V2l-3 2-2-2-3 2-3-2-2 2Z" />
                            <path d="M9 8h6M9 12h6M9 16h4" />
                        </svg>
                        <span wire:loading.remove wire:target="openMovimientoModal1">Registrar gasto</span>
                        <span wire:loading wire:target="openMovimientoModal1">…</span>
                    </button>
                @endcan
            @endif

            {{-- Cerrar rendición: solo cuando saldo = 0 --}}
            @if ($editorEstado !== 'cerrado' && $saldoActual <= 0)
                @can('agente_presupuestos.close_movement')
                    <button type="button" wire:click="cerrarRendicion" wire:loading.attr="disabled"
                        wire:target="cerrarRendicion"
                        class="cursor-pointer px-4 py-2 rounded-lg bg-emerald-600 text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="cerrarRendicion">Cerrar rendición</span>
                        <span wire:loading wire:target="cerrarRendicion">Cerrando…</span>
                    </button>
                @endcan
            @endif
        </div>
    @endslot
</x-ui.modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        {{-- Confirmación antes de eliminar movimiento --}}
        window.addEventListener('swal:delete-movimiento', (e) => {
            const {
                id,
                monto
            } = e.detail;
            Swal.fire({
                title: '¿Eliminar movimiento?',
                html: `Se eliminará el movimiento por <strong>${monto}</strong>.<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doDeleteMovimiento', {
                        id: id
                    });
                }
            });
        });

        {{-- Error al eliminar (banco sin fondos, etc.) --}}
        window.addEventListener('swal:error', (e) => {
            // Pequeño delay para que el Swal de confirmación termine de cerrarse
            setTimeout(() => {
                const detail = e.detail ?? {};
                Swal.fire({
                    icon: 'error',
                    title: detail.title ?? 'No se puede realizar',
                    html: detail.html ?
                        `<p style="font-size:0.92em; color:#555; line-height:1.6;">${detail.html}</p>` :
                        (detail.text ?? detail.message ?? ''),
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ef4444',
                });
            }, 350);
        });

        {{-- Auto-scroll hacia la devolución destacada cuando el modal se abre desde Transacciones --}}
        @php $highlightDevId = (int) ($highlight_devolucion_id ?? 0); @endphp
        @if ($highlightDevId > 0)
            (function() {
                const devId = {{ $highlightDevId }};
                let alreadyScrolled = false;
                document.addEventListener('livewire:navigated', () => {
                    alreadyScrolled = false;
                });
                // Observar cambios en el DOM: cuando aparece #devolucion-highlight-{id}
                const observer = new MutationObserver(() => {
                    if (alreadyScrolled) return;
                    const el = document.getElementById('devolucion-highlight-' + devId);
                    if (el) {
                        alreadyScrolled = true;
                        setTimeout(() => el.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        }), 400);
                    }
                });
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
                // Limpiar observer después de 10s
                setTimeout(() => observer.disconnect(), 10000);
            })();
        @endif

        {{-- Scroll al elemento resaltado cuando se abre el modal --}}
        Livewire.on('rendicion-editor-opened', () => {
            setTimeout(() => {
                // Busca el elemento visible (mobile tiene prefix "mob-", desktop no)
                const all = document.querySelectorAll('[id*="-highlight-"]');
                const highlighted = Array.from(all).find(el => el.offsetParent !== null);
                if (highlighted) {
                    highlighted.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Limpiar el resaltado después de 5 segundos
                    setTimeout(() => {
                        @this.set('highlight_movimiento_id', null);
                        @this.set('highlight_devolucion_id', null);
                    }, 5000);
                }
            }, 600);
        });
    });
</script>
