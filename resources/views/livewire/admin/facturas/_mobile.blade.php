{{-- MOBILE: CARDS (md:hidden) --}}
<div class="md:hidden space-y-2 mt-3">
    @forelse ($facturas as $f)
        @php
            $saldo = \App\Services\FacturaFinance::saldo($f);
            $retPend = \App\Services\FacturaFinance::retencionPendiente($f);
            $cerrada = \App\Services\FacturaFinance::estaCerrada($f);
            $estadoPago = \App\Services\FacturaFinance::estadoPagoLabel($f);
            $estadoRet = \App\Services\FacturaFinance::estadoRetencionLabel($f);

            $pct = 0;
            if (!$cerrada && $estadoPago === 'Parcial') {
                $pct = \App\Services\FacturaFinance::porcentajePago($f);
            }

            $bloqueado = $saldo <= 0 && $retPend <= 0;
            $sinPagos = $f->pagos->isEmpty();
            $isOpen = $panelsOpen[$f->id] ?? false;

            $fPath = $f->foto_comprobante ?? null;

            // Accent border based on state
            $accentBorder = match (true) {
                $cerrada     => 'border-t-emerald-400 dark:border-t-emerald-500',
                $saldo > 0   => 'border-t-blue-400 dark:border-t-blue-500',
                $retPend > 0 => 'border-t-amber-400 dark:border-t-amber-500',
                default      => 'border-t-gray-300 dark:border-t-neutral-600',
            };

            $isTarget = isset($factura_id) && $factura_id == $f->id;
        @endphp

        <div @if ($isTarget) id="factura-mobile-target-{{ $f->id }}" @endif
            wire:key="factura-card-{{ $f->id }}"
            class="rounded-xl border-t-4 border border-gray-200 dark:border-neutral-700 overflow-hidden
                   {{ $accentBorder }}
                   {{ $isTarget ? 'bg-indigo-50/50 border-indigo-300 dark:border-indigo-500/40 dark:bg-indigo-900/10' : 'bg-white dark:bg-neutral-900/50' }}">

            {{-- ── CARD HEADER (tap to expand) ── --}}
            <button type="button" wire:click="togglePanel({{ $f->id }})"
                class="w-full text-left px-3 py-2.5 flex flex-col gap-1 cursor-pointer
                       hover:bg-gray-50/80 dark:hover:bg-white/5 transition-colors">

                {{-- Top row: badges + amounts --}}
                <div class="flex items-start justify-between gap-2">
                    {{-- Badges --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if (isset($pendingRemoval[(string) $f->id]))
                            <div x-data="{ seconds: 4, progress: 100 }" x-init="let s = Date.now();
                            setInterval(() => { if (seconds > 0) seconds-- }, 1000);
                            let iv = setInterval(() => {
                                progress = Math.max(0, 100 - ((Date.now() - s) / 4000 * 100));
                                if (progress <= 0) {
                                    clearInterval(iv);
                                    $wire.dispatch('factura:clear-pending-removal', { facturaId: {{ $f->id }} });
                                }
                            }, 50);" class="flex items-center gap-1.5">
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 animate-pulse dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800">
                                    MOVIENDO…
                                </span>
                                <div class="flex items-center gap-1">
                                    <div class="w-14 h-1 bg-gray-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full transition-all"
                                            :style="'width:' + progress + '%'"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                        x-text="seconds+'s'"></span>
                                </div>
                            </div>
                        @else
                            {{-- Estado pago --}}
                            @if ($cerrada)
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                    Completado
                                </span>
                            @elseif ($estadoPago === 'Pendiente')
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700">
                                    Pagos 0%
                                </span>
                            @elseif ($estadoPago === 'Parcial')
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20">
                                    Pagos {{ $pct }}%
                                </span>
                            @else
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                    Pagada
                                </span>
                            @endif

                            {{-- Badge retención --}}
                            @if ($estadoRet === 'Retención pendiente')
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                    Ret. pendiente
                                </span>
                            @elseif ($estadoRet)
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-lime-50 text-lime-700 border border-lime-200 dark:bg-lime-500/10 dark:text-lime-400 dark:border-lime-500/20">
                                    {{ $estadoRet }}
                                </span>
                            @endif
                        @endif
                    </div>

                    {{-- Amounts (solo contraído) --}}
                    @if (!$isOpen)
                        <div class="shrink-0 text-right">
                            <div
                                class="text-[12px] font-bold tabular-nums text-gray-700 dark:text-neutral-300 leading-none">
                                Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mt-0.5">
                                {{ $f->fecha_emision?->format('d/m/y') ?? '—' }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sub-row: nro · entidad --}}
                <div class="flex items-center gap-1 text-[12px] text-gray-400 dark:text-neutral-500 flex-wrap">
                    <span class="font-medium text-gray-500 dark:text-neutral-400">Nro Factura:
                        {{ $f->numero ?? '#' . $f->id }}</span>
                    @if ($f->proyecto?->entidad?->nombre)
                        <span>·</span>
                        <span class="truncate">{{ $f->proyecto->entidad->nombre }}</span>
                    @endif
                </div>

                {{-- Project name (full width) --}}
                <div
                    class="text-[13px] font-semibold leading-snug text-gray-900 dark:text-neutral-100 {{ $isOpen ? '' : 'line-clamp-2' }}">
                    {{ $f->proyecto?->nombre ?? '—' }}
                </div>
            </button>

            {{-- ── EXPANDED BODY ── --}}
            @if ($isOpen)
                <div x-transition.opacity.duration.150ms>

                    {{-- Metrics strip --}}
                    <div
                        class="mx-3 mb-2.5 grid grid-cols-3 divide-x divide-gray-100 dark:divide-neutral-800 bg-gray-50 dark:bg-neutral-800/50 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                        <div class="py-2 px-1">
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Facturado</div>
                            <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="py-2 px-1">
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Retención</div>
                            <div class="text-xs font-bold tabular-nums text-amber-600 dark:text-amber-400">
                                Bs {{ number_format((float) ($f->retencion ?? 0), 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="py-2 px-1">
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo</div>
                            <div
                                class="text-xs font-bold tabular-nums {{ $saldo > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-gray-400' }}">
                                Bs {{ number_format((float) $saldo, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Detail rows --}}
                    <div class="px-3 space-y-1.5 text-xs pb-2.5">

                        {{-- Fecha emisión --}}
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                <span class="text-gray-400 dark:text-neutral-500 shrink-0">Emisión</span>
                                <span class="font-medium text-gray-700 dark:text-neutral-300 text-right">
                                    {{ $f->fecha_emision?->format('d/m/Y H:i') ?? '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Retención % --}}
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                            <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                <span class="text-gray-400 dark:text-neutral-500 shrink-0">Retención</span>
                                <span class="font-medium text-gray-700 dark:text-neutral-300 text-right">
                                    {{ number_format((float) ($f->proyecto?->retencion ?? 0), 2, ',', '.') }}%
                                </span>
                            </div>
                        </div>

                        {{-- Contrato --}}
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                <span class="text-gray-400 dark:text-neutral-500 shrink-0">Contrato</span>
                                <span class="font-medium text-gray-700 dark:text-neutral-300 text-right">
                                    Bs {{ number_format((float) ($f->proyecto?->monto ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Detalle / observación --}}
                        @if ($f->observacion)
                            <div class="flex items-start gap-2 pt-1 border-t border-gray-100 dark:border-neutral-800"
                                x-data="{ open: false }">
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                <div class="flex-1 min-w-0 text-gray-500 dark:text-neutral-400 leading-relaxed">
                                    @if (mb_strlen($f->observacion) > 60)
                                        <span x-show="!open" class="line-clamp-2">{{ $f->observacion }}</span>
                                        <span x-show="open" x-cloak class="whitespace-pre-line">
                                            {{ $f->observacion }}</span>
                                        <button type="button" @click.stop="open=!open"
                                            class="text-blue-500 hover:underline cursor-pointer ml-1"
                                            x-text="open ? 'Ver menos' : 'Ver más'"></button>
                                    @else
                                        Observación: {{ $f->observacion }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Pagos list --}}
                    @if (!$f->pagos->isEmpty())
                        <div
                            class="mx-3 mb-2.5 rounded-lg border border-gray-100 dark:border-neutral-700 overflow-hidden">
                            <div
                                class="flex items-center justify-between px-2.5 py-1.5 bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-100 dark:border-neutral-700">
                                <span class="text-[11px] font-semibold text-gray-600 dark:text-neutral-300">
                                    Pagos ({{ $f->pagos->count() }})
                                </span>
                                <span class="text-[11px] text-gray-400 dark:text-neutral-500 tabular-nums">
                                    Saldo: Bs {{ number_format((float) $saldo, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @foreach ($f->pagos as $pg)
                                    @php
                                        $pgPath = $pg->foto_comprobante ?? null;
                                        $tipoLabel = $pg->tipo === 'normal' ? 'Normal' : 'Retención';
                                        $tipoBadgePg =
                                            $pg->tipo === 'normal'
                                                ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20'
                                                : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20';
                                    @endphp
                                    <div wire:key="factura-mob-{{ $f->id }}-pg-{{ $pg->id }}"
                                        class="flex items-center gap-2 px-2.5 py-2 text-xs bg-white dark:bg-neutral-900/40">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-gray-800 dark:text-neutral-200 truncate">
                                                {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}
                                                @if ($pg->destino_moneda_snapshot ?? $pg->banco?->moneda)
                                                    <span
                                                        class="text-gray-400 font-normal">({{ $pg->destino_moneda_snapshot ?? $pg->banco->moneda }})</span>
                                                @endif
                                            </div>
                                            <div
                                                class="text-[10px] text-gray-400 dark:text-neutral-500 flex items-center gap-1">
                                                {{ $pg->fecha_pago?->format('d/m/y H:i') ?? '—' }}
                                                @if ($pg->nro_operacion)
                                                    · Op: {{ $pg->nro_operacion }}
                                                @endif
                                            </div>
                                        </div>
                                        <span
                                            class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $tipoBadgePg }}">
                                            {{ $tipoLabel }}
                                        </span>
                                        <div
                                            class="shrink-0 font-bold tabular-nums text-gray-900 dark:text-white text-xs">
                                            Bs {{ number_format((float) $pg->monto, 2, ',', '.') }}
                                        </div>
                                        <div class="shrink-0 flex items-center gap-1">
                                            <x-comprobante-btn :path="$pgPath" />
                                            @can('facturas.delete')
                                                <button type="button" @click.stop
                                                    wire:click="confirmDeletePago({{ $pg->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="confirmDeletePago({{ $pg->id }})"
                                                    title="Eliminar pago"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-red-200 text-red-500
                                                        hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30 transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M3 6h18" />
                                                        <path d="M8 6V4h8v2" />
                                                        <path d="M6 6l1 16h10l1-16" />
                                                        <path d="M10 11v6" />
                                                        <path d="M14 11v6" />
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Action bar: mismo orden que desktop --}}
                    <div class="px-3 pb-3 grid grid-cols-4 gap-1.5">

                        {{-- 1. Registrar pago --}}
                        @can('facturas.pay')
                            <button type="button"
                                @if (!$bloqueado) wire:click="openPago({{ $f->id }})" @endif
                                @disabled($bloqueado)
                                title="{{ $bloqueado ? 'Factura completa' : 'Registrar pago' }}"
                                class="flex items-center justify-center py-1 rounded-md border transition
                                    {{ $bloqueado
                                        ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                        : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 shadow-sm cursor-pointer' }}">
                                @if ($bloqueado)
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 14 4 9l5-5" />
                                        <path d="M20 20v-7a4 4 0 0 0-4-4H4" />
                                    </svg>
                                @endif
                            </button>
                        @endcan

                        {{-- 2. Comprobante factura --}}
                        <x-comprobante-btn :path="$fPath"
                            size-class="w-full py-1 rounded-md border transition flex items-center justify-center"
                            svg-class="w-4 h-4" />

                        {{-- 3. Editar --}}
                        @can('facturas.pay')
                            <button type="button"
                                @if ($sinPagos) wire:click="openEditFactura({{ $f->id }})" @endif
                                @disabled(!$sinPagos)
                                title="{{ $sinPagos ? 'Editar factura' : 'Tiene pagos: no se puede editar' }}"
                                class="flex items-center justify-center py-1 rounded-md border transition
                                    {{ !$sinPagos
                                        ? 'bg-gray-100 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                        : 'bg-white text-amber-600 border-amber-300 hover:bg-amber-50 hover:border-amber-400 shadow-sm cursor-pointer dark:bg-neutral-900 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/20' }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                    <path d="m15 5 4 4" />
                                </svg>
                            </button>
                        @endcan

                        {{-- 4. Eliminar --}}
                        @can('facturas.delete')
                            <button type="button"
                                @if ($sinPagos) wire:click="abrirEliminarFacturaModal({{ $f->id }})" @endif
                                @disabled(!$sinPagos)
                                title="{{ $sinPagos ? 'Eliminar factura' : 'Tiene pagos: no se puede eliminar' }}"
                                class="flex items-center justify-center py-1 rounded-md border transition
                                    {{ !$sinPagos
                                        ? 'bg-gray-100 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                        : 'bg-white text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400 shadow-sm cursor-pointer dark:bg-neutral-900 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20' }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M6 6l1 16h10l1-16" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                </svg>
                            </button>
                        @endcan
                    </div>

                </div>
            @endif
        </div>

    @empty
        <div class="py-8 text-center text-sm text-gray-400 dark:text-neutral-500">
            Sin resultados.
        </div>
    @endforelse
</div>
