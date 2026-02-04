{{-- TABLET + DESKTOP --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table wire:key="boletas-table" class="w-full table-fixed text-sm min-w-[1100px] lg:min-w-0">

            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200
                border-b border-gray-200 dark:border-neutral-700">
                <tr class="text-left">

                    {{-- ID --}}
                    <th class="w-[5%] text-center p-2 select-none whitespace-nowrap">
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

                    <th class="w-[35%] p-2 select-none whitespace-nowrap">Proyecto</th>
                    <th class="w-[18%] p-2 select-none whitespace-nowrap">Banco</th>
                    <th class="w-[20%] p-2 select-none whitespace-nowrap">Boleta</th>
                    <th class="w-[7%]  p-2 select-none whitespace-nowrap text-center">Estado</th>
                    <th class="w-[8%]  p-2 select-none whitespace-nowrap text-center">Devuelto</th>
                    <th class="w-[7%]  p-2 whitespace-nowrap text-center">Acciones</th>
                </tr>
            </thead>

            @foreach ($boletas as $bg)
                @php
                    $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
                    $rest = max(0, (float) $bg->retencion - $totalDev);
                    $devuelta = $totalDev >= (float) $bg->retencion;
                    $colspan = 7;
                @endphp

                {{-- open: detalle devoluciones; showFullProject: ver más/menos del proyecto --}}
                <tbody x-data="{ open: false, showFullProject: false }" x-on:boletas:toggle-all.window="open = $event.detail.open"
                    class="divide-y divide-gray-200 dark:divide-neutral-700" wire:key="boleta-row-{{ $bg->id }}">

                    {{-- CLICK EN LA FILA: despliega/oculta detalle --}}
                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900/60 text-gray-700 dark:text-neutral-200 cursor-pointer"
                        @click="open = !open">

                        {{-- ID + toggle (no disparar click de fila) --}}
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

                        {{-- PROYECTO  --}}
                        <td class="p-2 align-top">
                            <div class="min-w-0 space-y-0.5 leading-snug">

                                <div class="flex gap-2 text-sm font-semibold text-gray-900 dark:text-neutral-100">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 shrink-0 mt-0.5 text-gray-400 dark:text-neutral-400"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                    </svg>

                                    <div class="min-w-0 flex-1">
                                        <div x-show="!showFullProject" class="min-w-0 flex items-center gap-2">
                                            <span class="min-w-0 flex-1 truncate whitespace-nowrap"
                                                title="{{ $bg->proyecto?->nombre ?? '-' }}">
                                                {{ $bg->proyecto?->nombre ?? '—' }}
                                            </span>

                                            <button type="button"
                                                class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                @click.stop="showFullProject = true">
                                                Ver más
                                            </button>
                                        </div>

                                        <div x-show="showFullProject" x-cloak class="min-w-0 leading-snug">
                                            <span class="break-words font-semibold">
                                                {{ $bg->proyecto?->nombre ?? '—' }}
                                            </span>

                                            <button type="button"
                                                class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                @click.stop="showFullProject = false">
                                                Ver menos
                                            </button>
                                        </div>
                                    </div>
                                </div>

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
                                        Emisión: {{ $bg->fecha_emision?->format('Y-m-d') ?? '—' }}
                                        | Venc.: {{ $bg->fecha_vencimiento?->format('Y-m-d') ?? '—' }}
                                    </span>
                                </div>

                            </div>
                        </td>

                        {{-- ESTADO (icono + badge) --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            <div class="flex items-center justify-center gap-1">
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
                                    Restante: {{ number_format((float) $rest, 2, ',', '.') }}
                                </div>
                            </div>
                        </td>

                        {{-- ACCIONES (no abrir detalle) --}}
                        <td class="p-2 whitespace-nowrap align-middle" @click.stop>
                            <div class="flex items-center justify-center gap-2">

                                <button type="button" wire:click="openDevolucion({{ $bg->id }})"
                                    @disabled($rest <= 0)
                                    class="inline-flex cursor-pointer items-center gap-1 px-3 py-1.5 rounded-lg border transition text-sm
                                        {{ $rest <= 0
                                            ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                            : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }}">
                                    {{-- icon corner-down-left (devolver) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M9 14 4 9l5-5" />
                                        <path d="M20 20v-7a4 4 0 0 0-4-4H4" />
                                    </svg>
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
                                                    <th class="p-2 w-[5%]  text-center">#</th>
                                                    <th class="p-2 w-[35%]">Banco</th>
                                                    <th class="p-2 w-[22%]">Fecha</th>
                                                    <th class="p-2 w-[18%] text-right">Monto</th>
                                                    <th class="p-2 w-[15%]">Nro Op.</th>
                                                    <th class="p-2 w-[5%]  text-center">Acc.</th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                                @forelse(($bg->devoluciones ?? collect()) as $dv)
                                                    <tr class="text-gray-700 dark:text-neutral-200">

                                                        <td class="p-2 text-center">{{ $loop->iteration }}</td>

                                                        <td class="p-2">
                                                            <div class="flex items-center gap-1 font-medium truncate">
                                                                {{-- icon building --}}
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-4 h-4 text-gray-400 dark:text-neutral-400"
                                                                    viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round">
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

                                                            <div
                                                                class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400 truncate">
                                                                {{-- icon credit-card --}}
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                    viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <rect x="2" y="5" width="20" height="14"
                                                                        rx="2" />
                                                                    <path d="M2 10h20" />
                                                                </svg>
                                                                <span>{{ $dv->banco?->numero_cuenta ?? '—' }}
                                                                    ({{ $dv->banco?->moneda ?? '—' }})
                                                                </span>
                                                            </div>
                                                        </td>

                                                        <td class="p-2 text-xs">
                                                            <div class="flex items-center gap-1">
                                                                {{-- icon calendar --}}
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
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
                                                                <span>{{ $dv->fecha_devolucion?->format('Y-m-d H:i') ?? '—' }}</span>
                                                            </div>
                                                        </td>

                                                        <td class="p-2 text-right tabular-nums font-semibold">
                                                            {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                                            {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                                        </td>

                                                        <td class="p-2 text-xs truncate">
                                                            <div class="flex items-center gap-1">
                                                                {{-- icon hash --}}
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                    viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M4 9h16" />
                                                                    <path d="M4 15h16" />
                                                                    <path d="M10 3 8 21" />
                                                                    <path d="M16 3 14 21" />
                                                                </svg>
                                                                <span>{{ $dv->nro_transaccion ?? '—' }}</span>
                                                            </div>
                                                        </td>

                                                        <td class="p-2 text-center">
                                                            {{-- Delete: dispara modal Livewire separado (no abrir detalle) --}}
                                                            <button type="button"
                                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-red-300 text-red-700
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
