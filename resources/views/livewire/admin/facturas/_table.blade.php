{{-- TABLET + DESKTOP: TABLA (COMPACTA) --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table wire:key="facturas-table" class="w-full table-fixed text-sm min-w-[1100px] lg:min-w-0">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-700">
                <tr class="text-left">
                    <th class="w-[5%] text-center p-2 select-none whitespace-nowrap">
                        <div x-data="{ allOpen: false }" class="flex items-center justify-center gap-2">
                            <button type="button"
                                class="w-7 h-7 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100 hover:text-gray-800
                                dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white transition cursor-pointer"
                                title="Desplegar / Ocultar todos"
                                @click="allOpen = !allOpen; window.dispatchEvent(new CustomEvent('facturas:toggle-all', { detail: { open: allOpen } }));">
                                <span x-show="!allOpen">⇵</span>
                                <span x-show="allOpen" x-cloak>×</span>
                            </button>
                        </div>
                    </th>

                    <th class="w-[30%] p-2 select-none whitespace-nowrap">Proyecto</th>
                    <th class="w-[30%] p-2 select-none whitespace-nowrap">Factura</th>
                    <th class="w-[10%] p-2 select-none whitespace-nowrap text-center">Estado</th>
                    <th class="w-[10%] p-2 select-none whitespace-nowrap text-center">Saldo / Ret.</th>

                    @can('facturas.pay')
                        <th class="w-[8%] p-2 select-none whitespace-nowrap text-center">Acc.</th>
                    @endcan
                </tr>
            </thead>

            @foreach ($rows as $r)
                <tbody x-data="{ open: false, showFullProject: false, showFullDetalle: false }"
                    x-on:facturas:toggle-all.window="if (@js((bool) ($r['pagos'] ?? false))) { open = $event.detail.open }"
                    class="divide-y divide-gray-200 dark:divide-neutral-200" wire:key="factura-row-{{ $r['id'] }}">

                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900/60 text-gray-700 dark:text-neutral-200
                         {{ $r['pagos'] ?? false ? 'cursor-pointer' : 'cursor-default opacity-90' }}"
                        @click="if (@js((bool) ($r['pagos'] ?? false))) open = !open">

                        {{-- Toggle --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" @disabled(!($r['pagos'] ?? false))
                                    class="w-6 h-6 inline-flex items-center justify-center rounded border transition
                               {{ $r['pagos'] ?? false
                                   ? 'border-gray-300 text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white cursor-pointer'
                                   : 'border-gray-200 text-gray-300 dark:border-neutral-800 dark:text-neutral-600 cursor-not-allowed' }}"
                                    @click.stop="if (@js((bool) ($r['pagos'] ?? false))) open = !open" :aria-expanded="open">

                                    @if (!($r['pagos'] ?? false))
                                        <span>—</span>
                                    @else
                                        <span x-show="!open">+</span>
                                        <span x-show="open" x-cloak>−</span>
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
                                        <div x-show="!showFullProject" class="min-w-0 flex items-center gap-2">
                                            <span class="min-w-0 flex-1 truncate whitespace-nowrap"
                                                title="{{ $r['proyecto_nombre'] }}">
                                                {{ $r['proyecto_nombre'] }}
                                            </span>
                                            <button type="button"
                                                class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                @click.stop="showFullProject = true">
                                                Ver más
                                            </button>
                                        </div>

                                        <div x-show="showFullProject" x-cloak class="min-w-0 leading-snug">
                                            <span class="break-words font-semibold">{{ $r['proyecto_nombre'] }}</span>
                                            <button type="button"
                                                class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                @click.stop="showFullProject = false">
                                                Ver menos
                                            </button>
                                        </div>
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
                                    <span
                                        class="font-semibold text-amber-700 dark:text-amber-300">{{ $r['retencion_pct'] }}</span>
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
                                    <span
                                        class="font-semibold text-gray-800 dark:text-neutral-200">{{ $r['contrato'] }}</span>
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
                                        <span
                                            class="font-semibold text-gray-800 dark:text-neutral-200">{{ $r['monto_facturado'] }}</span>

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2l7 4v6c0 5-3 9-7 10C8 21 5 17 5 12V6l7-4z" />
                                        </svg>
                                        <span>Ret.:</span>
                                        <span
                                            class="font-semibold text-amber-700 dark:text-amber-300">{{ $r['retencion_monto'] }}</span>
                                    </div>
                                </div>

                                {{-- Detalle --}}
                                @if (($r['detalle'] ?? '—') !== '—')
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400"
                                        x-data="{ showFullDetalle: false }">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4h16v16H4z" />
                                            <path d="M8 8h8" />
                                            <path d="M8 12h8" />
                                            <path d="M8 16h6" />
                                        </svg>

                                        <span class="shrink-0">Detalle:</span>

                                        <div class="min-w-0 flex-1">
                                            <div x-show="!showFullDetalle" class="min-w-0 flex items-center gap-2">
                                                <span class="min-w-0 flex-1 truncate whitespace-nowrap"
                                                    title="{{ $r['detalle'] }}">
                                                    {{ $r['detalle'] }}
                                                </span>

                                                <button type="button"
                                                    class="shrink-0 text-[11px] font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                    @click.stop="showFullDetalle = true">
                                                    Ver más
                                                </button>
                                            </div>

                                            <div x-show="showFullDetalle" x-cloak class="min-w-0 leading-snug">
                                                <span
                                                    class="break-words font-medium text-gray-700 dark:text-neutral-200">
                                                    {{ $r['detalle'] }}
                                                </span>

                                                <button type="button"
                                                    class="inline-flex align-baseline ml-2 text-[11px] font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                    @click.stop="showFullDetalle = false">
                                                    Ver menos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Respaldo factura --}}
                                @if ($r['factura_file'])
                                    <div class="mt-1 flex items-center gap-1 text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-500"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                        </svg>

                                        @if ($r['factura_file']['is_image'])
                                            <button type="button"
                                                class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline"
                                                @click.stop="$dispatch('open-image-modal', { url: '{{ $r['factura_file']['url'] }}' })">
                                                Ver Respaldo Factura
                                            </button>
                                        @else
                                            <a href="{{ $r['factura_file']['url'] }}" target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                                                Abrir PDF Factura
                                            </a>
                                        @endif
                                    </div>
                                @endif

                            </div>
                        </td>

                        {{-- Estado --}}
                        <td class="p-2 align-top">
                            <div class="flex text-center items-center justify-center gap-2 flex-wrap">
                                @foreach ($r['estado_badges'] as $b)
                                    <span
                                        class="px-2 py-1 rounded text-xs {{ $b['class'] }}">{{ $b['text'] }}</span>
                                @endforeach
                            </div>
                        </td>

                        {{-- Saldo --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            <div class="flex flex-col items-center justify-center gap-0.5">
                                <div class="text-xs font-semibold text-gray-800 dark:text-neutral-200">
                                    {{ $r['saldo'] }}</div>
                                @if ($r['ret_pendiente'])
                                    <div class="text-xs text-amber-700 dark:text-amber-300">Ret.:
                                        {{ $r['ret_pendiente'] }}</div>
                                @endif
                            </div>
                        </td>

                        {{-- Acciones --}}
                        @can('facturas.pay')
                            <td class="p-2 whitespace-nowrap align-middle" @click.stop>
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        @if (!$r['cerrado_acc']) wire:click="openPago({{ $r['id'] }})" @endif
                                        wire:loading.attr="disabled" wire:target="openPago({{ $r['id'] }})"
                                        wire:loading.class="cursor-not-allowed opacity-50"
                                        wire:loading.class.remove="cursor-pointer hover:bg-blue-700 hover:border-blue-700"
                                        @disabled($r['cerrado_acc'])
                                        class="w-9 h-9 inline-flex items-center justify-center rounded-lg border transition
                                        {{ $r['cerrado_acc']
                                            ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                            : 'bg-blue-600 cursor-pointer text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 dark:bg-blue-500 dark:border-blue-500 dark:hover:bg-blue-400 dark:hover:border-blue-400' }}"
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
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path
                                                        d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1" />
                                                    <path d="M21 12H17a2 2 0 0 0 0 4h4v-4Z" />
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
                                </div>
                            </td>
                        @endcan
                    </tr>

                    {{-- Detalle pagos --}}
                    <tr x-show="open && @js((bool) ($r['pagos'] ?? false))" x-cloak
                        class="bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">

                        <td class="px-5 py-2" colspan="{{ 5 + (auth()->user()->can('facturas.pay') ? 1 : 0) }}">
                            <div class="space-y-3 text-sm">
                                <div
                                    class="border rounded bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                                    <table class="w-full text-sm text-left align-middle">
                                        <thead
                                            class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-800">
                                            <tr>
                                                <th class="p-2 text-center font-medium whitespace-nowrap">#</th>
                                                <th class="p-2 font-medium whitespace-nowrap">Destino de Banco</th>
                                                <th class="p-2 font-medium whitespace-nowrap">Pago</th>
                                                <th class="p-2 font-medium whitespace-nowrap">Observación</th>
                                                <th class="p-2 text-center font-medium whitespace-nowrap">Resp.</th>
                                                <th class="p-2 text-center font-medium whitespace-nowrap">Tipo</th>

                                                @can('facturas.delete')
                                                    <th class="p-2 text-center font-medium whitespace-nowrap">Acc.</th>
                                                @endcan
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                            @foreach ($r['pagos'] as $i => $pg)
                                                <tr wire:key="factura-{{ $r['id'] }}-pago-{{ $pg['id'] }}"
                                                    class="text-gray-700 dark:text-neutral-200 align-top">

                                                    <td class="p-2 align-middle">
                                                        <div
                                                            class="flex items-center justify-center text-xs font-medium text-gray-700 dark:text-neutral-200">
                                                            {{ $i + 1 }}
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
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <rect x="2" y="5" width="20" height="14"
                                                                        rx="2" />
                                                                    <line x1="2" y1="10"
                                                                        x2="22" y2="10" />
                                                                </svg>
                                                                <span>Nro. Cuenta: {{ $pg['destino_cuenta'] }}
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
                                                                        <circle cx="12" cy="7" r="4" />
                                                                    </svg>
                                                                    <span>Titular: {{ $pg['destino_titular'] }}</span>
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
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <rect x="3" y="4" width="18" height="18"
                                                                        rx="2" />
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
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <circle cx="12" cy="12" r="9" />
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
                                                                    class="inline-flex items-center justify-center w-7 h-7 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition"
                                                                    title="Ver imagen"
                                                                    @click.stop="$dispatch('open-image-modal', { url: '{{ $pg['file']['url'] }}' })">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                </button>
                                                            @else
                                                                <a href="{{ $pg['file']['url'] }}" target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    class="inline-flex items-center justify-center w-7 h-7 rounded bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition"
                                                                    title="Ver PDF">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                </a>
                                                            @endif
                                                        @else
                                                            <span class="text-gray-400 dark:text-neutral-500">—</span>
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

                </tbody>
            @endforeach

            @if (count($rows) === 0)
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400"
                            colspan="{{ 5 + (auth()->user()->can('facturas.pay') ? 1 : 0) }}">
                            Sin resultados.
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
