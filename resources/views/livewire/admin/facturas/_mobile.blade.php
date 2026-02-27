 {{-- ============================
     MOBILE: RESUMEN + CARDS
     (Respeta filtros porque usa $totales calculado en el backend)
     ============================ --}}

 {{-- RESUMEN TOTALES (MOBILE) --}}
 <div class="md:hidden">
     <div class="border rounded-lg bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
         <div class="px-4 py-3 border-b border-gray-200 dark:border-neutral-800">
             <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                 Resumen (Totales)
             </div>
             <div class="text-xs text-gray-500 dark:text-neutral-400">
                 Calculado según los filtros actuales
             </div>
         </div>

         <div class="p-4 grid grid-cols-1 gap-3">
             {{-- Total facturado --}}
             <div class="flex items-center justify-between gap-3">
                 <div class="text-xs text-gray-500 dark:text-neutral-400">Monto total facturado</div>
                 <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                     Bs {{ number_format((float) ($totales['facturado'] ?? 0), 2, ',', '.') }}
                 </div>
             </div>

             {{-- Total pagado --}}
             <div class="flex items-center justify-between gap-3">
                 <div class="text-xs text-gray-500 dark:text-neutral-400">Monto total pagado</div>
                 <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                     Bs {{ number_format((float) ($totales['pagado_total'] ?? 0), 2, ',', '.') }}
                 </div>
             </div>

             {{-- Saldo total --}}
             <div class="flex items-center justify-between gap-3">
                 <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo total</div>
                 <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                     Bs {{ number_format((float) ($totales['saldo'] ?? 0), 2, ',', '.') }}
                 </div>
             </div>

             {{-- Retención pendiente total --}}
             <div class="flex items-center justify-between gap-3">
                 <div class="text-xs text-gray-500 dark:text-neutral-400">Retención pendiente total</div>
                 <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                     Bs {{ number_format((float) ($totales['retencion_pendiente'] ?? 0), 2, ',', '.') }}
                 </div>
             </div>
         </div>
     </div>
 </div>

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

         <div x-data="{ open: false }"
             class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
             {{-- Header card: Proyecto + Monto --}}
             <div class="flex items-start justify-between gap-3">
                 <div class="min-w-0">
                     {{-- Proyecto --}}
                     <div class="font-semibold truncate" title="{{ $f->proyecto?->nombre ?? '-' }}">
                         {{ $f->proyecto?->nombre ?? '—' }}
                     </div>

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
                     <div class="text-sm font-semibold">
                         Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                     </div>
                     <div class="text-xs text-gray-500 dark:text-neutral-400">
                         {{ $f->fecha_emision ? $f->fecha_emision->format('Y-m-d') : '—' }}
                     </div>
                 </div>
             </div>

             {{-- Factura (agrupado) --}}
             <div class="mt-3 border-t border-gray-200 dark:border-neutral-800 pt-3 space-y-1">
                 <div class="text-sm font-medium truncate" title="{{ $f->numero ?? '-' }}">
                     Nro: {{ $f->numero ?? 'Factura #' . $f->id }}
                 </div>

                 <div class="text-xs text-gray-500 dark:text-neutral-400">
                     Ret. Factura:
                     <span class="font-semibold text-gray-700 dark:text-neutral-200">
                         Bs {{ number_format((float) ($f->retencion ?? 0), 2, ',', '.') }}
                     </span>
                 </div>

                 <div class="text-xs text-gray-500 dark:text-neutral-400 truncate"
                     title="{{ $f->observacion ?? '—' }}">
                     Detalle: {{ $f->observacion ?? '—' }}
                 </div>

                 {{-- Respaldo Factura --}}
                 @if ($f->foto_comprobante)
                     @php
                         $extFactMob = strtolower(pathinfo($f->foto_comprobante, PATHINFO_EXTENSION));
                         $isImageFactMob = in_array($extFactMob, ['jpg', 'jpeg', 'png']);
                     @endphp
                     <div class="mt-1 flex items-center gap-1 text-xs">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                             <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                         </svg>
                         @if ($isImageFactMob)
                             <button type="button" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline"
                                 @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $f->foto_comprobante) }}' })">
                                 Ver Respaldo
                             </button>
                         @else
                             <a href="{{ asset('storage/' . $f->foto_comprobante) }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                                 Abrir PDF
                             </a>
                         @endif
                     </div>
                 @endif
             </div>

             {{-- Estado + Saldo --}}
             <div class="mt-3 flex items-start justify-between gap-3">
                 {{-- Estado (2 etiquetas) --}}
                 <div class="flex flex-wrap gap-2">
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
             <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                 @can('facturas.pay')
                     <button type="button"
                         @if (!$bloqueado) wire:click="openPago({{ $f->id }})" @endif
                         @disabled($bloqueado)
                         class="px-3 py-1 rounded border transition
                        {{ $bloqueado
                            ? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-500 border-gray-300 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                            : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 cursor-pointer' }}">
                         {{ $bloqueado ? 'Completo' : 'Registrar pago' }}
                     </button>
                 @endcan

                 <button type="button"
                     class="w-full px-3 py-2 rounded text-sm font-medium
                       border border-gray-300 text-gray-700 hover:bg-gray-50
                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"
                     @click="open = !open">
                     <span x-show="!open">Ver pagos</span>
                     <span x-show="open" x-cloak>Ocultar pagos</span>
                 </button>
             </div>

             {{-- Pagos (detalle) --}}
             <div x-show="open" x-cloak class="mt-4 space-y-2">
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
                                     $isImagePagoMob = in_array($extPagoMob, ['jpg', 'jpeg', 'png']);
                                 @endphp
                                 <div class="grid grid-cols-[110px,1fr] gap-2 mt-1">
                                     <div class="text-xs text-gray-500 dark:text-neutral-400">Respaldo</div>
                                     <div class="text-xs">
                                         @if ($isImagePagoMob)
                                             <button type="button" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1"
                                                 @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $pg->foto_comprobante) }}' })">
                                                 <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                 </svg>
                                                 Ver Imagen
                                             </button>
                                         @else
                                             <a href="{{ asset('storage/' . $pg->foto_comprobante) }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
         </div>
     @empty
         <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
             Sin resultados.
         </div>
     @endforelse
 </div>
