{{-- TABLET + DESKTOP: TABLA (COMPACTA) --}}
<div class="hidden md:block border border-gray-100 rounded bg-white dark:bg-neutral-800 overflow-hidden shadow-sm"
    @if (isset($factura_id) && $factura_id) x-data
    x-init="setTimeout(() => {
        const targetId = {{ (int) ($pago_id ?? 0) }} > 0 ? 'pago-highlight-{{ (int) ($pago_id ?? 0) }}' : 'factura-row-target-{{ (int) ($factura_id ?? 0) }}';
        const el = document.getElementById(targetId);
        if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    }, 600)" @endif>
    <div class="overflow-x-auto">
        <table wire:key="facturas-table" class="w-full table-fixed text-sm min-w-[1100px] lg:min-w-0">
            <thead
                class="bg-slate-50/50 text-slate-600 dark:bg-neutral-900/50 dark:text-neutral-400 border-b border-gray-100 dark:border-neutral-800">
                <tr class="text-left text-[11px] uppercase tracking-wider font-semibold">
                    <th class="w-[5%] text-center p-3 select-none whitespace-nowrap">
                        <div x-data="{ allOpen: false }" class="flex items-center justify-center gap-2">
                            <button type="button"
                                class="w-6 h-6 inline-flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:bg-white hover:border-gray-300 hover:text-gray-700 hover:shadow-sm dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 transition-all cursor-pointer"
                                title="Desplegar / Ocultar todos"
                                @click="
                                    allOpen = !allOpen;
                                    $wire.toggleAllPanels(allOpen);
                                ">
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
                        </div>
                    </th>

                    <th class="w-[30%] p-3 select-none whitespace-nowrap">Proyecto</th>
                    <th class="w-[30%] p-3 select-none whitespace-nowrap">Factura</th>
                    <th class="w-[10%] p-3 select-none whitespace-nowrap text-center">Estado</th>
                    <th class="w-[10%] p-3 select-none whitespace-nowrap text-center">Saldo / Ret.</th>
                    <th class="w-[8%] p-3 select-none whitespace-nowrap text-center">Acc.</th>
                </tr>
            </thead>

            @foreach ($rows as $r)
                @php
                    $isOpen = $panelsOpen[$r['id']] ?? false;
                @endphp
                <tbody x-data="{
                    showFullProject: false,
                    showFullDetalle: false
                }" class="divide-y divide-gray-100 dark:divide-neutral-800"
                    wire:key="factura-tbody-{{ $r['id'] }}">

                    <tr class="border-t border-gray-300 dark:border-neutral-800 hover:bg-slate-50/50 dark:hover:bg-neutral-900/60 transition-colors text-gray-700 dark:text-neutral-200
                         {{ $r['pagos'] ?? false ? 'cursor-pointer' : 'cursor-default' }}
                         {{ isset($factura_id) && $factura_id == $r['id'] ? 'bg-indigo-50/60 dark:bg-indigo-900/20' : '' }}"
                        @click="if (@js((bool) ($r['pagos'] ?? false))) $wire.togglePanel({{ $r['id'] }})"
                        wire:key="factura-row-{{ $r['id'] }}"
                        @if (isset($factura_id) && $factura_id == $r['id']) id="factura-row-target-{{ $r['id'] }}" @endif>

                        {{-- Toggle (con borde izquierdo de resaltado si es la factura objetivo) --}}
                        <td
                            class="p-3 whitespace-nowrap align-middle
                            {{ isset($factura_id) && $factura_id == $r['id'] ? 'border-l-4 border-indigo-400' : 'border-l-4 border-transparent' }}">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" @disabled(!($r['pagos'] ?? false))
                                    class="w-6 h-6 inline-flex items-center justify-center rounded border transition-all
                                    {{ $r['pagos'] ?? false
                                        ? 'border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-700 hover:shadow-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:text-neutral-200 cursor-pointer'
                                        : 'border-transparent text-transparent bg-transparent cursor-default' }}"
                                    @click.stop="if (@js((bool) ($r['pagos'] ?? false))) $wire.togglePanel({{ $r['id'] }})"
                                    :aria-expanded="{{ $isOpen ? 'true' : 'false' }}">

                                    @if ($r['pagos'] ?? false)
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
                            </div>
                        </td>

                        {{-- Proyecto --}}
                        <td class="p-2 align-top">
                            <div class="min-w-0 space-y-0.5 leading-snug">

                                <div class="flex gap-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400 shrink-0 mt-0.5"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                    </svg>

                                    <div class="min-w-0 flex-1">
                                        @php
                                            $proyNombre = $r['proyecto_nombre'] ?? '—';
                                            $isLongProy = mb_strlen($proyNombre) > 45;
                                        @endphp

                                        @if ($isLongProy)
                                            <div x-show="!showFullProject" class="min-w-0 flex items-center gap-2">
                                                <span class="min-w-0 flex-1 truncate whitespace-nowrap font-semibold"
                                                    title="{{ $proyNombre }}">
                                                    {{ $proyNombre }}
                                                </span>
                                                <button type="button"
                                                    class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                                    @click.stop="showFullProject = true">
                                                    Ver más
                                                </button>
                                            </div>

                                            <div x-show="showFullProject" x-cloak class="min-w-0 leading-snug">
                                                <span class="break-words font-semibold">{{ $proyNombre }}</span>
                                                <button type="button"
                                                    class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                                    @click.stop="showFullProject = false">
                                                    Ver menos
                                                </button>
                                            </div>
                                        @else
                                            <div class="truncate text-sm font-semibold text-gray-900 dark:text-neutral-100"
                                                title="{{ $proyNombre }}">
                                                {{ $proyNombre }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                    title="{{ $r['entidad_nombre'] }}">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <path
                                            d="M7 7h.01M7 11h.01M7 15h.01M11 7h.01M11 11h.01M11 15h.01M15 7h.01M15 11h.01M15 15h.01" />
                                    </svg>
                                    <span>Entidad: {{ $r['entidad_nombre'] }}</span>
                                </div>

                                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 2l7 4v6c0 5-3 9-7 10C8 21 5 17 5 12V6l7-4z" />
                                    </svg>
                                    <span>Retención:</span>
                                    <span class="font-semibold text-amber-700 dark:text-amber-300">
                                        {{ $r['retencion_pct'] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v10" />
                                        <path d="M8 11h8" />
                                    </svg>
                                    <span>Contrato:</span>
                                    <span class="font-semibold text-gray-800 dark:text-neutral-200">
                                        {{ $r['contrato'] }}
                                    </span>
                                </div>

                            </div>
                        </td>

                        {{-- Factura --}}
                        <td class="p-2 align-top">
                            <div class="min-w-0 space-y-0.5 leading-snug">

                                <div class="flex items-center gap-1 truncate text-sm font-medium text-gray-800 dark:text-neutral-200"
                                    title="{{ $r['numero_raw'] }}">
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
                                    <span>{{ $r['numero'] }}</span>
                                </div>

                                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <span>{{ $r['fecha'] }}</span>
                                </div>

                                <div
                                    class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-neutral-400">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M12 7v10" />
                                            <path d="M8 11h8" />
                                        </svg>
                                        <span>Monto:</span>
                                        <span class="font-semibold text-gray-800 dark:text-neutral-200">
                                            {{ $r['monto_facturado'] }}
                                        </span>

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2l7 4v6c0 5-3 9-7 10C8 21 5 17 5 12V6l7-4z" />
                                        </svg>
                                        <span>Ret.:</span>
                                        <span class="font-semibold text-amber-700 dark:text-amber-300">
                                            {{ $r['retencion_monto'] }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Detalle --}}
                                @if (($r['detalle'] ?? '—') !== '—')
                                    <div class="flex items-start gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 mt-0.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4h16v16H4z" />
                                            <path d="M8 8h8" />
                                            <path d="M8 12h8" />
                                            <path d="M8 16h6" />
                                        </svg>

                                        <span class="shrink-0">Detalle:</span>

                                        <div class="min-w-0 flex-1">
                                            @php
                                                $detText = $r['detalle'] ?? '—';
                                                $isLongDet = mb_strlen($detText) > 60;
                                            @endphp

                                            @if ($isLongDet)
                                                <div x-show="!showFullDetalle"
                                                    class="min-w-0 flex items-center gap-2">
                                                    <span class="min-w-0 flex-1 truncate whitespace-nowrap"
                                                        title="{{ $detText }}">
                                                        {{ $detText }}
                                                    </span>

                                                    <button type="button"
                                                        class="shrink-0 text-[11px] font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                                        @click.stop="showFullDetalle = true">
                                                        Ver más
                                                    </button>
                                                </div>

                                                <div x-show="showFullDetalle" x-cloak class="min-w-0 leading-snug">
                                                    <span class="break-words text-gray-700 dark:text-neutral-200">
                                                        {{ $detText }}
                                                    </span>

                                                    <button type="button"
                                                        class="inline-flex align-baseline ml-2 text-[11px] font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                                        @click.stop="showFullDetalle = false">
                                                        Ver menos
                                                    </button>
                                                </div>
                                            @else
                                                <div class="truncate" title="{{ $detText }}">
                                                    {{ $detText }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </td>

                        {{-- Estado --}}
                        <td class="p-2 align-top">
                            <div class="flex text-center items-center justify-center gap-2 flex-wrap">
                                @foreach ($r['estado_badges'] as $b)
                                    <span class="px-2 py-1 rounded text-xs {{ $b['class'] }}">
                                        {{ $b['text'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        {{-- Saldo --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            <div class="flex flex-col items-center justify-center gap-0.5">
                                <div class="text-xs font-semibold text-gray-800 dark:text-neutral-200">
                                    {{ $r['saldo'] }}
                                </div>
                                @if ($r['ret_pendiente'])
                                    <div class="text-xs text-amber-700 dark:text-amber-300">
                                        Ret.: {{ $r['ret_pendiente'] }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Acciones --}}
                        <td class="p-2 whitespace-nowrap align-middle" @click.stop>
                            <div class="flex items-center justify-center gap-2">
                                @can('facturas.pay')
                                    {{-- Botón pagar --}}
                                    <button type="button"
                                        @if (!$r['cerrado_acc']) wire:click="openPago({{ $r['id'] }})" @endif
                                        wire:loading.attr="disabled" wire:target="openPago({{ $r['id'] }})"
                                        wire:loading.class="cursor-not-allowed opacity-50"
                                        wire:loading.class.remove="cursor-pointer hover:bg-emerald-700 hover:border-emerald-700"
                                        @disabled($r['cerrado_acc'])
                                        class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition
                                        {{ $r['cerrado_acc']
                                            ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                            : 'bg-emerald-600 cursor-pointer text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }}"
                                        title="{{ $r['cerrado_acc'] ? 'Factura completa' : 'Registrar pago' }}">

                                        <span wire:loading.remove wire:target="openPago({{ $r['id'] }})"
                                            class="inline-flex">
                                            @if ($r['cerrado_acc'])
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 6 9 17l-5-5" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9 14 4 9l5-5" />
                                                    <path d="M20 20v-7a4 4 0 0 0-4-4H4" />
                                                </svg>
                                            @endif
                                        </span>

                                        @if (!$r['cerrado_acc'])
                                            <span wire:loading wire:target="openPago({{ $r['id'] }})"
                                                class="inline-flex">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 animate-spin"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 12a9 9 0 1 1-3-6.7" />
                                                </svg>
                                            </span>
                                        @endif
                                    </button>
                                @endcan

                                {{-- Respaldo Factura --}}
                                @if ($r['factura_file'])
                                    @if ($r['factura_file']['is_image'])
                                        <button type="button"
                                            @click.stop="$wire.openFotoComprobante('{{ $r['factura_file']['url'] }}')"
                                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                            title="Ver factura (Imagen)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2"
                                                    ry="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21 15 16 10 5 21" />
                                            </svg>
                                        </button>
                                    @else
                                        <a href="{{ $r['factura_file']['url'] }}" target="_blank"
                                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                            title="Ver factura (PDF)">
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
                                    @endif
                                @else
                                    <div class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-400 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-600 shadow-sm"
                                        title="Sin respaldo">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2"
                                                ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
                                        </svg>
                                    </div>
                                @endif

                                {{-- Botón eliminar factura (solo si no tiene pagos) --}}
                                @can('facturas.delete')
                                    <button type="button"
                                        @if ($r['sin_pagos']) wire:click="abrirEliminarFacturaModal({{ $r['id'] }})" @endif
                                        wire:loading.attr="disabled"
                                        wire:target="abrirEliminarFacturaModal({{ $r['id'] }})"
                                        @disabled(!$r['sin_pagos'])
                                        class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition
                                        {{ !$r['sin_pagos']
                                            ? 'bg-gray-100 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                            : 'bg-white text-red-600 border-red-300 cursor-pointer hover:bg-red-50 hover:border-red-400 dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20' }}"
                                        title="{{ !$r['sin_pagos'] ? 'Tiene pagos: no se puede eliminar' : 'Eliminar factura' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4h8v2" />
                                            <path d="M6 6l1 16h10l1-16" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                        </svg>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    {{-- Detalle pagos --}}
                    @if ($r['pagos'] ?? false)
                        @if ($isOpen)
                            <tr wire:key="factura-pagos-row-{{ $r['id'] }}"
                                class="border-t border-gray-300 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-900/40 border-b border-gray-100 dark:border-neutral-800">

                                <td class="px-4 py-3
                                    {{ isset($factura_id) && $factura_id == $r['id'] ? 'border-l-4 border-indigo-400' : '' }}"
                                    colspan="6">
                                    <div class="space-y-3 text-sm">
                                        <div
                                            class="border border-gray-100 rounded-lg bg-white shadow-sm dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                                            <table class="w-full text-sm text-left align-middle">
                                                <thead
                                                    class="bg-slate-50/50 text-slate-600 dark:bg-neutral-900/50 dark:text-neutral-400 border-b border-gray-100 dark:border-neutral-800">
                                                    <tr class="text-[11px] uppercase tracking-wider font-semibold">
                                                        <th class="p-3 text-center whitespace-nowrap">#</th>
                                                        <th class="p-3 whitespace-nowrap">Destino de Banco</th>
                                                        <th class="p-3 whitespace-nowrap">Pago</th>
                                                        <th class="p-3 whitespace-nowrap">Observación</th>
                                                        <th class="p-3 text-center whitespace-nowrap">Resp.</th>
                                                        <th class="p-3 text-center whitespace-nowrap">Tipo</th>

                                                        @can('facturas.delete')
                                                            <th class="p-3 text-center whitespace-nowrap">Acc.</th>
                                                        @endcan
                                                    </tr>
                                                </thead>

                                                <tbody class="divide-y divide-gray-300 dark:divide-neutral-800">
                                                    @foreach ($r['pagos'] as $i => $pg)
                                                        @php
                                                            $isHighlighted = isset($pago_id) && $pago_id == $pg['id'];
                                                        @endphp
                                                        <tr wire:key="factura-{{ $r['id'] }}-pago-{{ $pg['id'] }}"
                                                            @if ($isHighlighted) id="pago-highlight-{{ $pg['id'] }}" @endif
                                                            class="text-gray-700 dark:text-neutral-200 align-top transition-colors
                                                            {{ $isHighlighted ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}">

                                                            <td
                                                                class="p-2 align-middle
                                                                {{ $isHighlighted ? 'border-l-4 border-amber-400' : 'border-l-4 border-transparent' }}">
                                                                <div
                                                                    class="flex items-center justify-center text-xs font-medium text-gray-700 dark:text-neutral-200">
                                                                    @if ($isHighlighted)
                                                                        <span
                                                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-400 text-white font-bold text-[10px] animate-pulse"
                                                                            title="Este es el pago de la transacción">{{ $i + 1 }}</span>
                                                                    @else
                                                                        {{ $i + 1 }}
                                                                    @endif
                                                                </div>
                                                            </td>

                                                            <td class="p-2 align-middle">
                                                                <div class="min-w-0 space-y-0.5 leading-snug">
                                                                    <div class="truncate text-sm font-medium text-gray-800 dark:text-neutral-200"
                                                                        title="{{ $pg['destino_tooltip'] }}">
                                                                        {{ $pg['destino_nombre'] }}
                                                                    </div>

                                                                    <div
                                                                        class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <rect x="2" y="5" width="20"
                                                                                height="14" rx="2" />
                                                                            <line x1="2" y1="10"
                                                                                x2="22" y2="10" />
                                                                        </svg>
                                                                        <span>
                                                                            Nro. Cuenta: {{ $pg['destino_cuenta'] }}
                                                                            @if ($pg['destino_moneda'])
                                                                                | {{ $pg['destino_moneda'] }}
                                                                            @endif
                                                                        </span>
                                                                    </div>

                                                                    @if ($pg['destino_titular'])
                                                                        <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                                                            title="{{ $pg['destino_titular'] }}">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                                viewBox="0 0 24 24" fill="none"
                                                                                stroke="currentColor" stroke-width="2"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                                <path
                                                                                    d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                                                                <circle cx="12" cy="7"
                                                                                    r="4" />
                                                                            </svg>
                                                                            <span>Titular:
                                                                                {{ $pg['destino_titular'] }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </td>

                                                            <td class="p-2 align-middle">
                                                                <div class="min-w-0 space-y-0.5 leading-snug">
                                                                    <div
                                                                        class="flex items-center gap-1 truncate text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-4 h-4 text-gray-400 dark:text-neutral-400"
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
                                                                        <span>{{ $pg['fecha'] }}</span>
                                                                    </div>

                                                                    <div
                                                                        class="flex items-center gap-1 truncate text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-4 h-4 text-gray-400 dark:text-neutral-400"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <circle cx="12" cy="12"
                                                                                r="9" />
                                                                            <path d="M12 7v10" />
                                                                            <path d="M8 11h8" />
                                                                        </svg>
                                                                        <span>{{ $pg['monto'] }}</span>
                                                                    </div>

                                                                    <div
                                                                        class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                                                        Método: {{ $pg['metodo'] }} | Nro.Op:
                                                                        {{ $pg['nro_operacion'] }}
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td class="p-2 align-middle">
                                                                <p
                                                                    class="text-sm leading-snug text-gray-700 dark:text-neutral-200 break-words">
                                                                    {{ $pg['observacion'] }}
                                                                </p>
                                                            </td>

                                                            <td class="p-2 align-middle text-center">
                                                                @if ($pg['file'])
                                                                    @if ($pg['file']['is_image'])
                                                                        <button type="button"
                                                                            class="inline-flex items-center justify-center w-7 h-7 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition cursor-pointer"
                                                                            title="Ver imagen"
                                                                            @click.stop="$wire.openFotoComprobante('{{ $pg['file']['url'] }}')">
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                viewBox="0 0 24 24"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                        </button>
                                                                    @else
                                                                        <a href="{{ $pg['file']['url'] }}"
                                                                            target="_blank" rel="noopener noreferrer"
                                                                            class="inline-flex items-center justify-center w-7 h-7 rounded bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition"
                                                                            title="Ver PDF">
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                viewBox="0 0 24 24"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                            </svg>
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    <span
                                                                        class="text-gray-400 dark:text-neutral-500">—</span>
                                                                @endif
                                                            </td>

                                                            <td class="p-2 align-middle whitespace-nowrap">
                                                                <div class="flex items-center justify-center">
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-1 rounded text-xs {{ $pg['tipo_class'] }}">
                                                                        {{ $pg['tipo_text'] }}
                                                                    </span>
                                                                </div>
                                                            </td>

                                                            @can('facturas.delete')
                                                                <td class="p-2 align-middle whitespace-nowrap">
                                                                    <div class="flex items-center justify-center">
                                                                        <button type="button"
                                                                            class="inline-flex items-center justify-center w-9 h-9 rounded border border-red-300 text-red-700
                                                                        hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition cursor-pointer"
                                                                            title="Eliminar pago"
                                                                            wire:click="confirmDeletePago({{ $pg['id'] }})"
                                                                            wire:loading.attr="disabled"
                                                                            wire:target="confirmDeletePago({{ $pg['id'] }})">
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
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            @endforeach

            @if (count($rows) === 0)
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    <tr>
                        <td class="p-8 text-center" colspan="6">
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

{{-- PAGINACIÓN --}}
<div>
    {{ $facturas->links() }}
</div>
