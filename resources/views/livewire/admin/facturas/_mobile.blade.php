{{-- ============================
    MOBILE: RESUMEN + CARDS
    (Respeta filtros porque usa $totales calculado en el backend)
    ============================ --}}



{{-- MOBILE: CARDS (md:hidden) --}}
<div class="space-y-3 md:hidden mt-3">
    @forelse ($facturas as $f)
        @php
            // Finanzas
            $saldo = \App\Services\FacturaFinance::saldo($f);
            $retPend = \App\Services\FacturaFinance::retencionPendiente($f);

            // Estado
            $cerrada = \App\Services\FacturaFinance::estaCerrada($f);
            $estadoPago = \App\Services\FacturaFinance::estadoPagoLabel($f);
            $estadoRet = \App\Services\FacturaFinance::estadoRetencionLabel($f);

            // % pago (si usas esa función)
            $pct = 0;
            if (!$cerrada && $estadoPago === 'Parcial') {
                $pct = \App\Services\FacturaFinance::porcentajePago($f);
            }

            // Bloqueo acciones si está 100% cerrada (saldo neto 0 y retención pendiente 0)
            $bloqueado = $saldo <= 0 && $retPend <= 0;
        @endphp

        @php $isOpen = $panelsOpen[$f->id] ?? false; @endphp
        <div x-data="{ showFullProject: false, showFullDetalle: false }" wire:key="factura-card-{{ $f->id }}"
            class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800 transition-all
             {{ isset($factura_id) && $factura_id == $f->id ? 'ring-2 ring-indigo-500 shadow-md' : '' }}"
            @if (isset($factura_id) && $factura_id == $f->id) id="factura-mobile-target-{{ $f->id }}" @endif>
            {{-- Header card: Proyecto + Monto --}}
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 font-medium">
                    {{-- Proyecto --}}
                    @php
                        $proyNombre = $f->proyecto?->nombre ?? '—';
                        $proyLong = mb_strlen($proyNombre) > 40;
                    @endphp
                    @if ($proyLong)
                        <div class="font-semibold leading-tight text-gray-900 dark:text-neutral-100">
                            <div x-show="!showFullProject">
                                {{ mb_substr($proyNombre, 0, 40) }}...
                                <button type="button" @click.stop="showFullProject = true"
                                    class="text-xs text-blue-600 hover:underline ml-1 cursor-pointer">Ver más</button>
                            </div>
                            <div x-show="showFullProject" x-cloak>
                                {{ $proyNombre }}
                                <button type="button" @click.stop="showFullProject = false"
                                    class="text-xs text-blue-600 hover:underline ml-1 cursor-pointer">Ver menos</button>
                            </div>
                        </div>
                    @else
                        <div class="font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $proyNombre }}
                        </div>
                    @endif

                    {{-- Entidad --}}
                    <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1"
                        title="{{ $f->proyecto?->entidad?->nombre ?? '-' }}">
                        Entidad: {{ $f->proyecto?->entidad?->nombre ?? '—' }}
                    </div>

                    {{-- Retención % + Contrato --}}
                    <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                        Retención:
                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                            {{ number_format((float) ($f->proyecto?->retencion ?? 0), 2, ',', '.') }}%
                        </span>
                        <span class="mx-1">|</span>
                        Contrato:
                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                            Bs {{ number_format((float) ($f->proyecto?->monto ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="shrink-0 text-right">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        {{ $f->fecha_emision ? $f->fecha_emision->format('Y-m-d') : '—' }}
                    </div>
                </div>
            </div>

            {{-- Factura (agrupado) --}}
            <div class="mt-3 border-t border-gray-200 dark:border-neutral-800 pt-3 space-y-1">
                <div class="text-sm font-medium truncate text-gray-800 dark:text-gray-100"
                    title="{{ $f->numero ?? '-' }}">
                    Nro: {{ $f->numero ?? 'Factura #' . $f->id }}
                </div>

                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Ret. Factura:
                    <span class="font-semibold text-gray-700 dark:text-neutral-200">
                        Bs {{ number_format((float) ($f->retencion ?? 0), 2, ',', '.') }}
                    </span>
                </div>

                @php
                    $detText = $f->observacion ?? '—';
                    $detLong = mb_strlen($detText) > 60;
                @endphp
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    <span class="font-medium">Detalle:</span>
                    @if ($detLong)
                        <span x-show="!showFullDetalle">
                            {{ mb_substr($detText, 0, 60) }}...
                            <button type="button" @click.stop="showFullDetalle = true"
                                class="text-blue-600 hover:underline ml-1 cursor-pointer">Ver más</button>
                        </span>
                        <span x-show="showFullDetalle" x-cloak>
                            {{ $detText }}
                            <button type="button" @click.stop="showFullDetalle = false"
                                class="text-blue-600 hover:underline ml-1 cursor-pointer">Ver menos</button>
                        </span>
                    @else
                        <span>{{ $detText }}</span>
                    @endif
                </div>
            </div>

            {{-- Estado + Saldo --}}
            <div class="mt-3 flex items-start justify-between gap-3">
                {{-- Estado (2 etiquetas) --}}
                <div class="flex flex-wrap gap-2 min-h-[44px]">
                    @if (isset($pendingRemoval[(string) $f->id]))
                        <div class="flex flex-col items-center gap-1 w-full" x-data="{ seconds: 5, progress: 100 }"
                            x-init="let start = Date.now();
                            setInterval(() => { if (seconds > 0) seconds-- }, 1000);
                            let interval = setInterval(() => {
                                progress = Math.max(0, 100 - ((Date.now() - start) / 5000 * 100));
                                if (progress <= 0) {
                                    clearInterval(interval);
                                    $wire.dispatch('factura:clear-pending-removal', { facturaId: {{ $f->id }} });
                                }
                            }, 50);">
                            <span
                                class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 animate-pulse border border-amber-200 dark:border-amber-800">
                                MOVIENDO A CERRADAS
                            </span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <div class="w-20 h-1 bg-gray-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full" :style="'width: ' + progress + '%'">
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                    x-text="seconds + 's'"></span>
                            </div>
                        </div>
                    @else
                        {{-- Estado principal --}}
                        @if ($cerrada)
                            <span
                                class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                Completado
                            </span>
                        @else
                            @if ($estadoPago === 'Pendiente')
                                <span
                                    class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200">
                                    Pagos 0%
                                </span>
                            @elseif ($estadoPago === 'Parcial')
                                <span
                                    class="px-2 py-1 rounded text-xs font-semibold
                           {{ $pct == 100
                               ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200'
                               : ($pct > 0
                                   ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200'
                                   : 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200') }}">
                                    Pagos {{ $pct }}%
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200">
                                    Pagada (Neto)
                                </span>
                            @endif
                        @endif

                        {{-- Badge retención (si aplica) --}}
                        @if ($estadoRet)
                            @if ($estadoRet === 'Retención pendiente')
                                <span
                                    class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200">
                                    {{ $estadoRet }}
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-xs bg-lime-100 text-lime-800 dark:bg-lime-500/20 dark:text-lime-200">
                                    {{ $estadoRet }}
                                </span>
                            @endif
                        @endif
                    @endif
                </div>

                {{-- Saldo --}}
                <div class="text-right shrink-0">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                        Bs {{ number_format((float) $saldo, 2, ',', '.') }}
                    </div>

                    @if ($retPend > 0)
                        <div class="text-xs text-yellow-700 dark:text-yellow-300">
                            Ret.: Bs {{ number_format((float) $retPend, 2, ',', '.') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Acciones --}}
            <div class="mt-4 flex flex-col gap-2">
                <div class="grid grid-cols-2 gap-2">
                    {{-- Respaldo --}}
                    @if ($f->foto_comprobante)
                        @php
                            $extFactMob = strtolower(pathinfo($f->foto_comprobante, PATHINFO_EXTENSION));
                            $isImageFactMob = in_array($extFactMob, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                        @endphp
                        @if ($isImageFactMob)
                            <button type="button"
                                @click.stop="$wire.openFotoComprobante('{{ asset('storage/' . $f->foto_comprobante) }}')"
                                class="flex items-center justify-center gap-2 px-3 py-2 rounded border border-indigo-200 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-800 dark:text-indigo-300 font-medium text-sm cursor-pointer transition hover:bg-indigo-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                                Ver Factura
                            </button>
                        @else
                            <a href="{{ asset('storage/' . $f->foto_comprobante) }}" target="_blank"
                                class="flex items-center justify-center gap-2 px-3 py-2 rounded border border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 font-medium text-sm transition hover:bg-rose-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                </svg>
                                Abrir PDF
                            </a>
                        @endif
                    @else
                        <div
                            class="flex items-center justify-center gap-2 px-3 py-2 rounded border border-gray-200 bg-gray-50 text-gray-400 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-500 font-medium text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                <line x1="3" y1="3" x2="21" y2="21" />
                            </svg>
                            Sin Respaldo
                        </div>
                    @endif

                    @can('facturas.pay')
                        <button type="button"
                            @if (!$bloqueado) wire:click="openPago({{ $f->id }})" @endif
                            @disabled($bloqueado)
                            class="px-3 py-2 rounded border transition flex items-center justify-center gap-2 font-medium text-sm
                           {{ $bloqueado
                               ? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-500 border-gray-300 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                               : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 cursor-pointer' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1" />
                                <path d="M21 12H17a2 2 0 0 0 0 4h4v-4Z" />
                            </svg>
                            {{ $bloqueado ? 'Completo' : 'Registrar' }}
                        </button>
                    @endcan
                </div>


                @can('facturas.pay')
                    @php $sinPagos = $f->pagos->isEmpty(); @endphp
                    <div class="grid grid-cols-1">
                        <button type="button"
                            @if ($sinPagos) wire:click="openEditFactura({{ $f->id }})" @endif
                            @disabled(!$sinPagos)
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded border transition font-medium text-sm
                            {{ $sinPagos
                                ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-900/20 dark:border-amber-700 dark:text-amber-300 cursor-pointer'
                                : 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                <path d="m15 5 4 4" />
                            </svg>
                            {{ $sinPagos ? 'Editar factura' : 'Editar (tiene pagos)' }}
                        </button>
                    </div>
                @endcan


                <div class="grid grid-cols-1">
                    <button type="button" wire:click="togglePanel({{ $f->id }})"
                        class="w-full px-3 py-2 rounded text-sm font-medium
                      border border-gray-300 text-gray-700 hover:bg-gray-50
                      dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer">
                        {{ $isOpen ? 'Ocultar pagos' : 'Ver pagos' }}
                    </button>
                </div>
            </div>

            {{-- Pagos (detalle) --}}
            @if ($isOpen)
                <div class="mt-4 space-y-2">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Pagos realizados: {{ $f->pagos?->count() ?? 0 }}
                    </div>

                    @forelse(($f->pagos ?? collect()) as $pg)
                        @php
                            $bancoNombre = $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—');
                            $cuenta = $pg->destino_numero_cuenta_snapshot ?? ($pg->banco?->numero_cuenta ?? null);
                            $moneda = $pg->destino_moneda_snapshot ?? ($pg->banco?->moneda ?? null);
                            $titular = $pg->destino_titular_snapshot ?? null;

                            $tipoLabel = $pg->tipo === 'normal' ? 'Pago Normal' : 'Pago de Retención';
                            $tipoBadge =
                                $pg->tipo === 'normal'
                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200'
                                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200';
                        @endphp

                        <div class="border rounded-lg p-3 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                            {{-- Header: Fecha izquierda | Tipo derecha --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[11px] font-mono text-gray-500 dark:text-neutral-400">
                                        #{{ $loop->iteration }}
                                    </span>
                                    <div class="text-sm font-semibold truncate">
                                        {{ $pg->fecha_pago ? $pg->fecha_pago->format('Y-m-d H:i') : '—' }}
                                    </div>
                                </div>

                                {{-- Tipo --}}
                                @if ($pg->tipo === 'normal')
                                    <span
                                        class="shrink-0 px-2 py-1 rounded text-xs bg-blue-100 text-blue-800
                  dark:bg-blue-500/20 dark:text-blue-200">
                                        Pago Normal
                                    </span>
                                @else
                                    <span
                                        class="shrink-0 px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800
                  dark:bg-yellow-500/20 dark:text-yellow-200">
                                        Pago de Retención
                                    </span>
                                @endif
                            </div>

                            {{-- Detalle: filas label / value --}}
                            <div class="mt-3 grid grid-cols-1 gap-2">
                                {{-- Monto --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Monto</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                        Bs {{ number_format((float) $pg->monto, 2, ',', '.') }}
                                    </div>
                                </div>

                                {{-- Método --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Método</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200">
                                        {{ $pg->metodo_pago ?? '—' }}
                                    </div>
                                </div>

                                {{-- Banco --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Banco</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200 truncate"
                                        title="{{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}">
                                        {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}
                                    </div>
                                </div>

                                {{-- Cuenta / Moneda --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Cuenta</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200">
                                        {{ $pg->destino_numero_cuenta_snapshot ?? ($pg->banco?->numero_cuenta ?? '—') }}
                                        @php
                                            $moneda = $pg->destino_moneda_snapshot ?? ($pg->banco?->moneda ?? null);
                                        @endphp
                                        @if ($moneda)
                                            <span class="text-gray-500 dark:text-neutral-400">|
                                                {{ $moneda }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Operación --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Operación</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200">
                                        {{ $pg->nro_operacion ?? '—' }}
                                    </div>
                                </div>

                                {{-- Titular --}}
                                @if ($pg->destino_titular_snapshot)
                                    <div class="grid grid-cols-[110px,1fr] gap-2">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Titular</div>
                                        <div class="text-xs text-gray-800 dark:text-neutral-200 truncate"
                                            title="{{ $pg->destino_titular_snapshot }}">
                                            {{ $pg->destino_titular_snapshot }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Observación --}}
                                @if ($pg->observacion)
                                    <div class="grid grid-cols-[110px,1fr] gap-2">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Obs.</div>
                                        <div class="text-xs text-gray-800 dark:text-neutral-200 line-clamp-2"
                                            title="{{ $pg->observacion }}">
                                            {{ $pg->observacion }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Respaldo Pago --}}
                                @if ($pg->foto_comprobante)
                                    @php
                                        $extPagoMob = strtolower(pathinfo($pg->foto_comprobante, PATHINFO_EXTENSION));
                                        $isImagePagoMob = in_array($extPagoMob, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                                    @endphp
                                    <div class="grid grid-cols-[110px,1fr] gap-2 mt-1">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Respaldo</div>
                                        <div class="text-xs">
                                            @if ($isImagePagoMob)
                                                <button type="button"
                                                    class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1 cursor-pointer"
                                                    @click.stop="$wire.openFotoComprobante('{{ asset('storage/' . $pg->foto_comprobante) }}')">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Ver Imagen
                                                </button>
                                            @else
                                                <a href="{{ asset('storage/' . $pg->foto_comprobante) }}"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Abrir PDF
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div
                            class="border rounded p-3 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                            No hay pagos registrados.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    @empty
        <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
            Sin resultados.
        </div>
    @endforelse
</div>
