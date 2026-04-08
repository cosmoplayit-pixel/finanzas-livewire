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

        {{-- TABLAS --}}
        @if ($hasMovs)
            <div class="grid grid-cols-1 gap-4">

                {{-- DEVOLUCIONES --}}
                @if ($hasDevoluciones)
                    <div
                        class="rounded-xl border border-emerald-200/60 dark:border-emerald-800/30 bg-white dark:bg-neutral-900/40 overflow-hidden shadow-sm mb-4">
                        <div
                            class="px-4 py-3 border-b border-emerald-100 dark:border-emerald-800/30 bg-emerald-50/50 dark:bg-emerald-900/10 flex items-center justify-between">
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
                                        <th class="p-3 text-center w-[50px]">Nro</th>
                                        <th class="p-3 text-center w-[90px]">Fecha</th>
                                        <th class="p-3">Banco</th>
                                        <th class="p-3">Transacción</th>
                                        <th class="p-3">Observación</th>
                                        <th class="p-3 text-right">Monto</th>
                                        <th class="p-3 text-right">Base</th>
                                        <th class="p-3 text-center w-[110px]">Acc.</th>
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
                                        <tr @if ($isDevHighlighted) id="devolucion-highlight-{{ $m->id }}" @endif
                                            class="transition group
                                            {{ $isDevHighlighted ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10' }}">
                                            <td
                                                class="p-3 text-center font-medium
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
                                            <td
                                                class="p-3 text-gray-600 dark:text-neutral-300 whitespace-nowrap text-center">
                                                <div class="text-xs">{{ $m->fecha?->format('d/m/Y') ?? '—' }}</div>
                                                <div class="text-[10px] text-gray-400">
                                                    {{ $m->fecha?->format('H:i') ?? '' }}</div>
                                            </td>
                                            <td
                                                class="p-3 text-gray-700 dark:text-neutral-200 font-medium whitespace-nowrap">
                                                <div class="text-sm font-semibold">
                                                    {{ $m->banco?->nombre ?? '—' }}
                                                </div>
                                                @if ($m->banco?->titular)
                                                    <div class="text-[10px] text-gray-500 font-normal">
                                                        Titular: {{ $m->banco->titular }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-gray-600 dark:text-neutral-300 whitespace-nowrap">
                                                {{ $m->nro_transaccion ?? '—' }}
                                            </td>
                                            <td class="p-3 text-gray-500 dark:text-neutral-400 text-xs"
                                                title="{{ $m->observacion }}">
                                                <div class="line-clamp-2 min-w-[120px]">{{ $m->observacion ?: '—' }}
                                                </div>
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100 font-medium whitespace-nowrap">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                <span class="text-[10px] text-gray-400">{{ $m->moneda }}</span>
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-emerald-700 dark:text-emerald-400 font-semibold whitespace-nowrap">
                                                <div>
                                                    {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                    <span
                                                        class="text-[10px] opacity-70">{{ $editorMonedaBase ?? 'BOB' }}</span>
                                                </div>
                                                @if ($m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                    <div class="text-[10px] text-gray-400 font-normal">
                                                        TC: {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center">
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
                                <div
                                    @if ($isDevHighlightedMob) id="mob-devolucion-highlight-{{ $m->id }}" @endif
                                    class="rounded-xl border-l-4 border border-gray-200 dark:border-neutral-700 p-3 transition-colors
                                        {{ $isDevHighlightedMob
                                            ? 'border-l-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                                            : 'border-l-transparent bg-white dark:bg-neutral-900/20' }}">

                                    {{-- CABECERA --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                                #{{ $i + 1 }} · {{ $m->banco?->nombre ?? '—' }}
                                            </div>
                                            @if ($m->banco?->titular)
                                                <div class="text-[10px] text-gray-400 font-normal">
                                                    Titular: {{ $m->banco->titular }}
                                                </div>
                                            @endif

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->fecha?->format('d/m/Y H:i') ?? '-' }}
                                                <span class="mx-1">•</span>
                                                Tx: {{ $m->nro_transaccion ?? '—' }}
                                            </div>
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

                                    {{-- OBSERVACIÓN (ABAJO, FULL WIDTH) --}}
                                    <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                                        <span class="font-medium text-gray-700 dark:text-neutral-200">Obs:</span>
                                        <span
                                            class="{{ empty($m->observacion) ? 'text-gray-400 dark:text-neutral-500' : '' }}">
                                            {{ $m->observacion ?: '—' }}
                                        </span>
                                    </div>

                                    {{-- MONTOS --}}
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Monto
                                            </div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Base
                                            </div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </div>
                                            @if ($m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                <div class="text-[10px] text-gray-400 font-normal">
                                                    TC: {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

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
                            class="px-4 py-3 border-b border-blue-100 dark:border-blue-800/30 bg-blue-50/50 dark:bg-blue-900/10 flex items-center justify-between">
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
                                        <th class="p-3 text-center w-[50px]">Nro</th>
                                        <th class="p-3 text-center w-[90px]">Fecha</th>
                                        <th class="p-3 min-w-[150px]">Entidad / Proyecto</th>
                                        <th class="p-3">Comprobante</th>
                                        <th class="p-3">Observación</th>
                                        <th class="p-3 text-right">Monto</th>
                                        <th class="p-3 text-right">Base</th>
                                        <th class="p-3 text-center w-[110px]">Acc.</th>
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
                                        <tr @if ($isMovHighlighted) id="movimiento-highlight-{{ $m->id }}" @endif
                                            class="transition group
                                            {{ $isMovHighlighted ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-blue-50/30 dark:hover:bg-blue-900/10' }}">
                                            <td
                                                class="p-3 text-center font-medium
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
                                            <td
                                                class="p-3 text-gray-600 dark:text-neutral-300 whitespace-nowrap text-center">
                                                <div class="text-xs">{{ $m->fecha?->format('d/m/Y') ?? '—' }}</div>
                                                <div class="text-[10px] text-gray-400">
                                                    {{ $m->fecha?->format('H:i') ?? '' }}</div>
                                            </td>
                                            <td class="p-3 pr-4">
                                                <div class="text-sm font-medium text-gray-800 dark:text-neutral-200 line-clamp-1 truncate max-w-[200px]"
                                                    title="{{ $m->entidad?->nombre }}">
                                                    {{ $m->entidad?->nombre ?? '—' }}</div>
                                                <div class="text-[11px] text-gray-500 dark:text-neutral-400 line-clamp-1 truncate max-w-[200px]"
                                                    title="{{ $m->proyecto?->nombre }}">
                                                    {{ $m->proyecto?->nombre ?? '—' }}</div>
                                            </td>
                                            <td class="p-3 text-gray-600 dark:text-neutral-300 text-xs">
                                                <div class="font-medium text-gray-700 dark:text-neutral-200">
                                                    {{ $m->tipo_comprobante ?? '—' }}</div>
                                                @if (!empty($m->nro_comprobante))
                                                    <div class="text-gray-500">{{ $m->nro_comprobante }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-gray-500 dark:text-neutral-400 text-xs"
                                                title="{{ $m->observacion }}">
                                                <div class="line-clamp-2 min-w-[120px]">{{ $m->observacion ?: '—' }}
                                                </div>
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100 font-medium whitespace-nowrap">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                <span class="text-[10px] text-gray-400">{{ $m->moneda }}</span>
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-blue-700 dark:text-blue-400 font-semibold whitespace-nowrap">
                                                <div>
                                                    {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                    <span
                                                        class="text-[10px] opacity-70">{{ $editorMonedaBase ?? 'BOB' }}</span>
                                                </div>
                                                @if ($m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                    <div class="text-[10px] text-gray-400 font-normal">
                                                        TC: {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center">
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
                                <div
                                    @if ($isMovHighlightedMob) id="mob-movimiento-highlight-{{ $m->id }}" @endif
                                    class="rounded-xl border-l-4 border border-gray-200 dark:border-neutral-700 p-3 transition-colors
                                        {{ $isMovHighlightedMob
                                            ? 'border-l-blue-400 bg-blue-50 dark:bg-blue-900/20'
                                            : 'border-l-transparent bg-white dark:bg-neutral-900/20' }}">

                                    {{-- Header: # + Proyecto + acciones --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                                #{{ $i + 1 }}
                                                <span class="text-gray-400 dark:text-neutral-600">·</span>
                                                {{ $m->proyecto?->nombre ?? '—' }}
                                            </div>

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->entidad?->nombre ?? '—' }}
                                                <span class="mx-1">•</span>
                                                {{ $m->fecha?->format('d/m/Y H:i') ?? '-' }}
                                            </div>

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->tipo_comprobante ?? '—' }}
                                                @if (!empty($m->nro_comprobante))
                                                    <span class="text-gray-400">•</span> {{ $m->nro_comprobante }}
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Acciones (editar + foto + eliminar) --}}
                                        <div class="shrink-0 flex items-center gap-2">
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

                                    <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                                        <span class="font-medium text-gray-700 dark:text-neutral-200">Obs:</span>
                                        <span
                                            class="{{ empty($m->observacion) ? 'text-gray-400 dark:text-neutral-500' : '' }}">
                                            {{ $m->observacion ?: '—' }}
                                        </span>
                                    </div>

                                    {{-- Montos --}}
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Monto</div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Base</div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </div>
                                            @if ($m->moneda !== ($editorMonedaBase ?? 'BOB'))
                                                <div class="text-[10px] text-gray-400 font-normal">
                                                    TC: {{ number_format((float) $m->tipo_cambio, 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

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
