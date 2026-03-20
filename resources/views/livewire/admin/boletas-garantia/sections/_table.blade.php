{{-- TABLET + DESKTOP --}}
<div class="hidden md:block border border-gray-100 rounded bg-white dark:bg-neutral-800 overflow-hidden shadow-sm"
    @if (isset($highlight_boleta_id) && $highlight_boleta_id) x-data
    x-init="setTimeout(() => {
        const devolucionEl = document.getElementById('devolucion-panel-target-{{ (int) ($highlight_devolucion_id ?? 0) }}');
        const boletaEl = document.getElementById('boleta-row-target-{{ (int) ($highlight_boleta_id ?? 0) }}');
        const el = devolucionEl || boletaEl;
        if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    }, 700)" @endif>

    <div class="overflow-x-auto">
        <table wire:key="boletas-table" class="w-full table-fixed text-sm min-w-[1100px] lg:min-w-0">

            <thead
                class="bg-slate-50/50 text-slate-600 dark:bg-neutral-900/50 dark:text-neutral-400 border-b border-gray-100 dark:border-neutral-800">
                <tr class="text-left text-[11px] uppercase tracking-wider font-semibold">

                    {{-- ID --}}
                    <th class="w-[5%] text-center p-2 select-none whitespace-nowrap">
                        <div x-data="{ allOpen: false }" class="flex items-center justify-center gap-2">
                            <button type="button"
                                class="w-6 h-6 inline-flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:bg-white hover:border-gray-300 hover:text-gray-700 hover:shadow-sm
                                 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 transition-all cursor-pointer"
                                title="Desplegar / Ocultar todos"
                                @click="allOpen = !allOpen; $wire.toggleAllPanels(allOpen, {{ $boletas->pluck('id') }})">
                                <svg x-show="!allOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                    fill="currentColor" class="size-3">
                                    <path fill-rule="evenodd"
                                        d="M3.22 7.595a.75.75 0 0 0 0 1.06l4.25 4.25a.75.75 0 0 0 1.06 0l4.25-4.25a.75.75 0 0 0-1.06-1.06l-2.97 2.97V2.75a.75.75 0 0 0-1.5 0v7.81l-2.97-2.97a.75.75 0 0 0-1.06 0Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <svg x-show="allOpen" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                    fill="currentColor" class="size-3">
                                    <path fill-rule="evenodd"
                                        d="M12.78 8.405a.75.75 0 0 0 0-1.06l-4.25-4.25a.75.75 0 0 0-1.06 0l-4.25 4.25a.75.75 0 0 0 1.06 1.06l2.97-2.97v7.81a.75.75 0 0 0 1.5 0v-7.81l2.97 2.97a.75.75 0 0 0 1.06 0Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <span>ID</span>
                        </div>
                    </th>

                    <th class="w-[25%] p-2 select-none whitespace-nowrap">Proyecto</th>
                    <th class="w-[18%] p-2 select-none whitespace-nowrap">Banco</th>
                    <th class="w-[20%] p-2 select-none whitespace-nowrap">Boleta</th>
                    <th class="w-[7%]  p-2 select-none whitespace-nowrap text-center">Estado</th>
                    <th class="w-[10%]  p-2 select-none whitespace-nowrap text-center">Devuelto</th>
                    @canany(['boletas_garantia.register_return', 'boletas_garantia.delete'])
                        <th class="w-[10%]  p-2 whitespace-nowrap text-center">Acciones</th>
                    @endcanany
                </tr>
            </thead>

            @foreach ($boletas as $bg)
                @php
                    $isOpen = (bool) ($panelsOpen[$bg->id] ?? false);
                    $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
                    $hasDevoluciones = ($bg->devoluciones?->count() ?? 0) > 0;
                    $rest = max(0, (float) $bg->retencion - $totalDev);
                    $devuelta = $totalDev >= (float) $bg->retencion;

                    // Calcular colspan dinámico para la tabla principal
                    $colspan =
                        6 +
                        (auth()->user()->can('boletas_garantia.register_return') ||
                        auth()->user()->can('boletas_garantia.delete')
                            ? 1
                            : 0);

                    // Calcular colspan dinámico para la tabla anidada de devoluciones
                    $colspanInner = 6 + (auth()->user()->can('boletas_garantia.delete') ? 1 : 0);
                @endphp

                {{-- showFullProject: ver más/menos del proyecto --}}
                <tbody x-data="{ showFullProject: false, showFullObs: false }" class="divide-y divide-gray-100 dark:divide-neutral-800"
                    wire:key="boleta-row-{{ $bg->id }}">

                    {{-- CLICK EN LA FILA: despliega/oculta detalle --}}
                    @php
                        $isTargetBoleta = isset($highlight_boleta_id) && $highlight_boleta_id == $bg->id;
                    @endphp
                    <tr @if ($isTargetBoleta) id="boleta-row-target-{{ $bg->id }}" @endif
                        class="border-t border-gray-300 dark:border-neutral-800 transition-colors text-gray-700 dark:text-neutral-200
                        {{ $hasDevoluciones ? 'cursor-pointer' : 'cursor-default' }}
                        {{ $isTargetBoleta ? 'bg-indigo-50/60 dark:bg-indigo-900/20' : 'hover:bg-slate-50/50 dark:hover:bg-neutral-900/60' }}"
                        @click="if (@js($hasDevoluciones)) $wire.togglePanel({{ $bg->id }})">

                        {{-- ID + toggle (no disparar click de fila) --}}
                        <td
                            class="p-2 whitespace-nowrap align-middle {{ $isTargetBoleta ? 'border-l-4 border-indigo-400' : 'border-l-4 border-transparent' }}">
                            <div class="flex items-center justify-center gap-2">

                                <button type="button" @disabled(!$hasDevoluciones)
                                    class="w-6 h-6 inline-flex items-center justify-center rounded border transition-all
                                        {{ $hasDevoluciones
                                            ? 'border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-700 hover:shadow-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:text-neutral-200 cursor-pointer'
                                            : 'border-transparent text-transparent bg-transparent cursor-default' }}"
                                    @click.stop="if (@js($hasDevoluciones)) $wire.togglePanel({{ $bg->id }})"
                                    :aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
                                    @if ($hasDevoluciones)
                                        @if (!$isOpen)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                                fill="currentColor" class="size-3">
                                                <path
                                                    d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                                fill="currentColor" class="size-3">
                                                <path d="M3.75 7.25a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z" />
                                            </svg>
                                        @endif
                                    @endif
                                </button>

                                <span class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $bg->id }}
                                </span>
                            </div>
                        </td>

                        {{-- PROYECTO  --}}
                        <td class="p-2 align-top">
                            <div class="min-w-0 space-y-0.5 leading-snug">

                                <div class="flex gap-2 text-sm text-gray-900 dark:text-neutral-100">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 shrink-0 mt-0.5 text-gray-400 dark:text-neutral-400"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                    </svg>

                                    <div class="min-w-0 flex-1">
                                        @php
                                            $nombreProyecto = $bg->proyecto?->nombre ?? '—';
                                            $isLong = mb_strlen($nombreProyecto) > 45;
                                        @endphp

                                        @if ($isLong)
                                            <div x-show="!showFullProject" class="min-w-0 flex items-center gap-2">
                                                <span class="min-w-0 flex-1 truncate whitespace-nowrap font-semibold"
                                                    title="{{ $nombreProyecto }}">
                                                    {{ $nombreProyecto }}
                                                </span>

                                                <button type="button"
                                                    class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                                    @click.stop="showFullProject = true">
                                                    Ver más
                                                </button>
                                            </div>

                                            <div x-show="showFullProject" x-cloak class="min-w-0 leading-snug">
                                                <span class="break-words font-semibold">
                                                    {{ $nombreProyecto }}
                                                </span>

                                                <button type="button"
                                                    class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                                    @click.stop="showFullProject = false">
                                                    Ver menos
                                                </button>
                                            </div>
                                        @else
                                            <div class="min-w-0">
                                                <span class="font-semibold">{{ $nombreProyecto }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Código --}}
                                @if ($bg->proyecto?->codigo)
                                    <div
                                        class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 9h16" />
                                            <path d="M4 15h16" />
                                            <path d="M10 3 8 21" />
                                            <path d="M16 3 14 21" />
                                        </svg>
                                        <span>Código: {{ $bg->proyecto->codigo }}</span>
                                    </div>
                                @endif

                                {{-- Entidad --}}
                                <div
                                    class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                    </svg>
                                    <span>Entidad: {{ $bg->entidad?->nombre ?? '—' }}</span>
                                </div>

                                {{-- Agente --}}
                                <div
                                    class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="7" r="4" />
                                        <path d="M20 21a8 8 0 0 0-16 0" />
                                    </svg>
                                    <span>Agente: {{ $bg->agenteServicio?->nombre ?? '—' }}</span>
                                </div>

                            </div>
                        </td>


                        {{-- BANCO (iconos) --}}
                        <td class="p-2 align-top">
                            <div class="min-w-0 space-y-0.5 leading-snug">

                                {{-- Banco --}}
                                <div class="flex items-center gap-1 truncate text-sm font-semibold text-gray-900 dark:text-neutral-100"
                                    title="{{ $bg->bancoEgreso?->nombre ?? '-' }}">
                                    {{-- icon building --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 21h18" />
                                        <path d="M4 21V8" />
                                        <path d="M20 21V8" />
                                        <path d="M12 3l8 5H4l8-5z" />
                                        <path d="M8 12v5" />
                                        <path d="M12 12v5" />
                                        <path d="M16 12v5" />
                                    </svg>
                                    <span>{{ $bg->bancoEgreso?->nombre ?? '—' }}</span>
                                </div>

                                {{-- Titular --}}
                                <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                    title="{{ $bg->bancoEgreso?->titular ?? '-' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <span>Titular: {{ $bg->bancoEgreso?->titular ?? '—' }}</span>
                                </div>

                                {{-- Cuenta --}}
                                <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                    title="{{ $bg->bancoEgreso?->numero_cuenta ?? '-' }}">
                                    {{-- icon credit-card --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2" />
                                        <path d="M2 10h20" />
                                    </svg>
                                    <span>Cuenta: {{ $bg->bancoEgreso?->numero_cuenta ?? '—' }}</span>
                                </div>

                                {{-- Moneda --}}
                                <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                    title="{{ $bg->bancoEgreso?->moneda ?? ($bg->moneda ?? '-') }}">
                                    {{-- icon coin --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v10" />
                                        <path d="M8 11h8" />
                                    </svg>
                                    <span>Moneda: {{ $bg->bancoEgreso?->moneda ?? ($bg->moneda ?? '—') }}</span>
                                </div>

                            </div>
                        </td>

                        {{-- BOLETA (iconos) --}}
                        <td class="p-2 align-top">
                            <div class="min-w-0 space-y-0.5 leading-snug">

                                {{-- Nro --}}
                                <div class="flex items-center gap-1 truncate text-sm font-semibold text-gray-900 dark:text-neutral-100"
                                    title="{{ $bg->nro_boleta ?? '-' }}">
                                    {{-- icon file-text --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <path d="M14 2v6h6" />
                                        <path d="M16 13H8" />
                                        <path d="M16 17H8" />
                                        <path d="M10 9H8" />
                                    </svg>
                                    <span>Nro: {{ $bg->nro_boleta ?? '—' }}</span>
                                </div>

                                {{-- Tipo --}}
                                <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                    title="{{ $bg->tipo ?? '-' }}">
                                    {{-- icon tag --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20.59 13.41 12 22l-8-8V2h12l4.59 4.59z" />
                                        <path d="M7 7h.01" />
                                    </svg>
                                    <span>Tipo: {{ $bg->tipo ?? '—' }}</span>
                                </div>



                                {{-- Fechas --}}
                                <div
                                    class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                    {{-- icon calendar --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <span>
                                        Emi.: {{ $bg->fecha_emision?->format('Y-m-d') ?? '—' }}
                                        | Venc.: {{ $bg->fecha_vencimiento?->format('Y-m-d') ?? '—' }}
                                    </span>
                                </div>

                                {{-- Observación --}}
                                @if ($bg->observacion)
                                    @php
                                        $obs = $bg->observacion;
                                        $isLongObs = mb_strlen($obs) > 45;
                                    @endphp
                                    <div
                                        class="flex items-start gap-1 text-[12.8px] text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mt-0.5 shrink-0"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                        </svg>

                                        @if ($isLongObs)
                                            <div class="min-w-0 flex-1">
                                                <div x-show="!showFullObs" class="flex items-center gap-1.5 min-w-0">
                                                    <span class="truncate leading-tight" title="{{ $obs }}">
                                                        Obs: {{ $obs }}
                                                    </span>
                                                    <button type="button" @click.stop="showFullObs = true"
                                                        class="shrink-0 text-[12.8px] font-medium text-blue-600 hover:underline cursor-pointer">
                                                        Ver más
                                                    </button>
                                                </div>
                                                <div x-show="showFullObs" x-cloak class="leading-tight">
                                                    <span class="break-words">Obs: {{ $obs }}</span>
                                                    <button type="button" @click.stop="showFullObs = false"
                                                        class="ml-1 text-[12.8px] font-medium text-blue-600 hover:underline cursor-pointer whitespace-nowrap">
                                                        Ver menos
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="leading-tight truncate" title="{{ $obs }}">
                                                Obs: {{ $obs }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                            </div>
                        </td>

                        {{-- ESTADO (icono + badge) --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            <div class="flex items-center justify-center gap-1">
                                @if ($devuelta)
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                        DEVUELTO
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                        ACTIVO
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- DEVUELTO (iconos) --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            <div class="text-center tabular-nums">
                                <div
                                    class="flex items-center justify-center gap-1 text-sm font-bold text-gray-900 dark:text-neutral-100">
                                    {{-- icon coins --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <ellipse cx="12" cy="5" rx="8" ry="3" />
                                        <path d="M4 5v6c0 1.66 3.58 3 8 3s8-1.34 8-3V5" />
                                        <path d="M4 11v6c0 1.66 3.58 3 8 3s8-1.34 8-3v-6" />
                                    </svg>

                                    <span>
                                        {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                        {{ number_format((float) $totalDev, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">
                                    Saldo: {{ number_format((float) $rest, 2, ',', '.') }}
                                </div>
                            </div>
                        </td>

                        {{-- ACCIONES --}}
                        @canany(['boletas_garantia.register_return', 'boletas_garantia.delete'])
                            <td class="p-2 whitespace-nowrap align-middle" @click.stop>
                                <div class="flex items-center justify-center gap-2">

                                    @can('boletas_garantia.register_return')
                                        <button type="button" wire:click="openDevolucion({{ $bg->id }})"
                                            @disabled($rest <= 0)
                                            class="inline-flex cursor-pointer items-center justify-center w-9 h-9 rounded-lg border transition text-sm
                                                {{ $rest <= 0
                                                    ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                                    : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }}"
                                            title="Registrar devolución">
                                            {{-- icon corner-down-left (devolver) --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M9 14 4 9l5-5" />
                                                <path d="M20 20v-7a4 4 0 0 0-4-4H4" />
                                            </svg>
                                        </button>
                                    @endcan

                                    {{-- VER COMPROBANTE --}}
                                    @php
                                        $fPath = $bg->foto_comprobante ?? null;
                                        $fExt = $fPath ? strtolower(pathinfo($fPath, PATHINFO_EXTENSION)) : '';
                                        $fIsPdf = $fExt === 'pdf';
                                    @endphp

                                    @if ($fPath)
                                        @if ($fIsPdf)
                                            <a href="{{ asset('storage/' . $fPath) }}" target="_blank"
                                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                                title="Ver PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                    <polyline points="14 2 14 8 20 8" />
                                                    <line x1="9" y1="13" x2="15" y2="13" />
                                                    <line x1="9" y1="17" x2="15" y2="17" />
                                                    <line x1="9" y1="9" x2="11" y2="9" />
                                                </svg>
                                            </a>
                                        @else
                                            <button type="button"
                                                @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $fPath) }}' })"
                                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                title="Ver imagen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"
                                                        ry="2" />
                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                    <polyline points="21 15 16 10 5 21" />
                                                </svg>
                                            </button>
                                        @endif
                                    @else
                                        <span
                                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                            title="Sin comprobante">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2"
                                                    ry="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21 15 16 10 5 21" />
                                            </svg>
                                        </span>
                                    @endif

                                    @can('boletas_garantia.delete')
                                        <button type="button" wire:click="abrirEliminarBoletaModal({{ $bg->id }})"
                                            @disabled($hasDevoluciones)
                                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition
                                                {{ $hasDevoluciones
                                                    ? 'bg-gray-100 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                                    : 'bg-white text-red-600 border-red-300 cursor-pointer hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20' }}"
                                            title="Eliminar boleta">
                                            {{-- icon trash --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M8 6v-2c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v2" />
                                                <path d="M6 6l1 14c0 1.1.9 2 2 2h6c1.1 0 2-.9 2-2l1-14" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                        </button>
                                    @endcan

                                </div>
                            </td>
                        @endcanany
                    </tr>

                    {{-- DETALLE (SOLO DEVOLUCIONES) --}}
                    @if ($hasDevoluciones && $isOpen)
                        <tr
                            class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50/60 dark:bg-neutral-900/40">
                            <td class="p-3 md:p-4 {{ $isTargetBoleta ? 'border-l-4 border-indigo-400' : 'border-l-4 border-transparent' }}"
                                colspan="{{ $colspan }}">

                                <div
                                    class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                                    <div class="p-3">
                                        <div class="border rounded-lg overflow-hidden dark:border-neutral-800">
                                            <table class="w-full text-sm">
                                                <thead
                                                    class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-800">
                                                    <tr class="text-left text-xs text-gray-600 dark:text-neutral-300">
                                                        <th class="p-2 w-[5%] text-center">#</th>
                                                        <th class="p-2 w-[30%]">Banco</th>
                                                        <th class="p-2 w-[22%]">Fecha y Nro Op. </th>
                                                        <th class="p-2 w-[18%] text-center">Monto</th>
                                                        <th class="p-2 w-[18%]">Observación</th>
                                                        @can('boletas_garantia.delete')
                                                            <th class="p-2 w-[12%] text-center">Acc.</th>
                                                        @endcan
                                                    </tr>
                                                </thead>

                                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                                    @forelse(($bg->devoluciones ?? collect()) as $dv)
                                                        @php
                                                            $isDevHighlighted =
                                                                isset($highlight_devolucion_id) &&
                                                                (int) $highlight_devolucion_id === (int) $dv->id;
                                                        @endphp
                                                        <tr @if ($isDevHighlighted) id="devolucion-panel-target-{{ $dv->id }}" @endif
                                                            class="text-gray-700 dark:text-neutral-200 transition-colors
                                                            {{ $isDevHighlighted ? 'bg-amber-50 dark:bg-amber-900/20' : 'hover:bg-slate-50 dark:hover:bg-neutral-900/40' }}">

                                                            {{-- Nro --}}
                                                            <td
                                                                class="p-2 text-center font-medium {{ $isDevHighlighted ? 'border-l-4 border-amber-400' : 'border-l-4 border-transparent' }}">
                                                                @if ($isDevHighlighted)
                                                                    <span
                                                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-400 text-white font-bold text-[10px] animate-pulse"
                                                                        title="Esta es la devolución de la transacción">{{ $loop->iteration }}</span>
                                                                @else
                                                                    {{ $loop->iteration }}
                                                                @endif
                                                            </td>

                                                            {{-- Banco --}}
                                                            <td class="p-2 text-xs">

                                                                {{-- Nombre Banco --}}
                                                                <div
                                                                    class="flex items-center gap-1 font-medium truncate">

                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path d="M3 21h18" />
                                                                        <path d="M4 21V8" />
                                                                        <path d="M20 21V8" />
                                                                        <path d="M12 3l8 5H4l8-5z" />
                                                                        <path d="M8 12v5" />
                                                                        <path d="M12 12v5" />
                                                                        <path d="M16 12v5" />
                                                                    </svg>

                                                                    <span>{{ $dv->banco?->nombre ?? '—' }}</span>
                                                                </div>

                                                                {{-- Titular --}}
                                                                <div
                                                                    class="flex items-center gap-1 text-gray-500 dark:text-neutral-400 truncate">

                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-3 h-3 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path
                                                                            d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                                                        <circle cx="12" cy="7" r="4" />
                                                                    </svg>

                                                                    <span>Titular:
                                                                        {{ $dv->banco?->titular ?? '—' }}</span>
                                                                </div>

                                                                {{-- Nro de Cuenta y Moneda --}}
                                                                <div
                                                                    class="flex items-center gap-1 text-gray-500 dark:text-neutral-400 truncate">

                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <rect x="2" y="5" width="20"
                                                                            height="14" rx="2" />
                                                                        <path d="M2 10h20" />
                                                                    </svg>

                                                                    <span>{{ $dv->banco?->numero_cuenta ?? '—' }}
                                                                        ({{ $dv->banco?->moneda ?? '—' }})
                                                                    </span>
                                                                </div>
                                                            </td>

                                                            {{-- Fecha y Nro Op --}}
                                                            <td class="p-2 text-xs">
                                                                {{-- Fecha --}}
                                                                <div class="flex items-center gap-1">

                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <rect x="3" y="4" width="18"
                                                                            height="18" rx="2" />
                                                                        <line x1="16" y1="2"
                                                                            x2="16" y2="6" />
                                                                        <line x1="8" y1="2"
                                                                            x2="8" y2="6" />
                                                                        <line x1="3" y1="10"
                                                                            x2="21" y2="10" />
                                                                    </svg>
                                                                    <span>{{ $dv->fecha_devolucion?->format('Y-m-d H:i') ?? '—' }}</span>
                                                                </div>

                                                                {{-- Nro Op --}}
                                                                <div class="flex items-center gap-1">
                                                                    {{-- icon hash --}}
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path d="M4 9h16" />
                                                                        <path d="M4 15h16" />
                                                                        <path d="M10 3 8 21" />
                                                                        <path d="M16 3 14 21" />
                                                                    </svg>
                                                                    <span>{{ $dv->nro_transaccion ?? '—' }}</span>
                                                                </div>
                                                            </td>

                                                            {{-- Monto --}}
                                                            <td
                                                                class="p-2 text-xs text-center tabular-nums font-semibold">
                                                                {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                                                {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                                            </td>

                                                            {{-- Observación --}}
                                                            <td class="p-2 text-xs">

                                                                <div class="flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-3.5 h-3.5 mt-0.5 shrink-0"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path
                                                                            d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                                                    </svg>
                                                                    <span>{{ $dv->observacion ?? '—' }}</span>
                                                                </div>
                                                            </td>

                                                            {{-- Acciones --}}
                                                            @can('boletas_garantia.delete')
                                                                <td class="p-2 text-center align-middle whitespace-nowrap">
                                                                    <div class="flex items-center justify-center gap-1.5">
                                                                        {{-- VER COMPROBANTE DEVOLUCION --}}
                                                                        @php
                                                                            $dvPath = $dv->foto_comprobante ?? null;
                                                                            $dvExt = $dvPath
                                                                                ? strtolower(
                                                                                    pathinfo(
                                                                                        $dvPath,
                                                                                        PATHINFO_EXTENSION,
                                                                                    ),
                                                                                )
                                                                                : '';
                                                                            $dvIsPdf = $dvExt === 'pdf';
                                                                        @endphp

                                                                        @if ($dvPath)
                                                                            @if ($dvIsPdf)
                                                                                <a href="{{ asset('storage/' . $dvPath) }}"
                                                                                    target="_blank"
                                                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                                                                    title="Ver PDF">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                                        class="w-3.5 h-3.5"
                                                                                        viewBox="0 0 24 24" fill="none"
                                                                                        stroke="currentColor"
                                                                                        stroke-width="2"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round">
                                                                                        <path
                                                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                                                        <polyline
                                                                                            points="14 2 14 8 20 8" />
                                                                                        <line x1="9"
                                                                                            y1="13" x2="15"
                                                                                            y2="13" />
                                                                                        <line x1="9"
                                                                                            y1="17" x2="15"
                                                                                            y2="17" />
                                                                                        <line x1="9"
                                                                                            y1="9" x2="11"
                                                                                            y2="9" />
                                                                                    </svg>
                                                                                </a>
                                                                            @else
                                                                                <button type="button"
                                                                                    @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $dvPath) }}' })"
                                                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                                                    title="Ver imagen">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                                        class="w-3.5 h-3.5"
                                                                                        viewBox="0 0 24 24" fill="none"
                                                                                        stroke="currentColor"
                                                                                        stroke-width="2"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round">
                                                                                        <rect x="3" y="3" width="18"
                                                                                            height="18" rx="2"
                                                                                            ry="2" />
                                                                                        <circle cx="8.5"
                                                                                            cy="8.5" r="1.5" />
                                                                                        <polyline
                                                                                            points="21 15 16 10 5 21" />
                                                                                    </svg>
                                                                                </button>
                                                                            @endif
                                                                        @else
                                                                            <span
                                                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                                                title="Sin comprobante">
                                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                                    class="w-3.5 h-3.5"
                                                                                    viewBox="0 0 24 24" fill="none"
                                                                                    stroke="currentColor" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round">
                                                                                    <rect x="3" y="3" width="18"
                                                                                        height="18" rx="2"
                                                                                        ry="2" />
                                                                                    <circle cx="8.5" cy="8.5"
                                                                                        r="1.5" />
                                                                                    <polyline points="21 15 16 10 5 21" />
                                                                                </svg>
                                                                            </span>
                                                                        @endif

                                                                        {{-- Delete: dispara modal Livewire separado (no abrir detalle) --}}
                                                                        <button type="button"
                                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-300 text-red-700
                                                                                hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition cursor-pointer"
                                                                            title="Eliminar devolución"
                                                                            wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }})">
                                                                            {{-- icon trash --}}
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
                                                                    </div>
                                                                </td>
                                                            @endcan
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="{{ $colspanInner }}"
                                                                class="p-8 text-center bg-white dark:bg-neutral-900/10">
                                                                <div
                                                                    class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                                                    <svg class="w-10 h-10 mb-3 opacity-20"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                                                        </path>
                                                                    </svg>
                                                                    <span class="text-sm font-medium">No hay
                                                                        devoluciones registradas.</span>
                                                                </div>
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
                    @endif

                </tbody>
            @endforeach

            @if ($boletas->count() === 0)
                @php
                    $emptyColspan = 6 + (auth()->user()->can('boletas_garantia.register_return') ? 1 : 0);
                @endphp
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    <tr>
                        <td class="p-8 text-center" colspan="{{ $emptyColspan }}">
                            <div class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <span class="text-sm font-medium">Sin resultados.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endif

        </table>
    </div>
</div>
