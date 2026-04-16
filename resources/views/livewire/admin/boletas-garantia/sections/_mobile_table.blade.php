{{-- MOBILE --}}
<div class="md:hidden space-y-2" x-data="{
    boletaId: @entangle('highlight_boleta_id'),
    devolucionId: @entangle('highlight_devolucion_id'),
    scroll() {
        const el = document.getElementById(`devolucion-mob-panel-target-${this.devolucionId}`) ||
            document.getElementById(`boleta-mob-row-target-${this.boletaId}`);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}" x-init="const clearBG = () => { $wire.set('highlight_boleta_id', null);
    $wire.set('highlight_devolucion_id', null); };
if (boletaId || devolucionId) { setTimeout(() => scroll(), 600);
    setTimeout(clearBG, 4000); }
$watch('boletaId', val => { if (val) { setTimeout(() => scroll(), 300);
        setTimeout(clearBG, 4000); } });
$watch('devolucionId', val => { if (val) { setTimeout(() => scroll(), 300);
        setTimeout(clearBG, 4000); } });"
    @bg:start-removal-timer.window="setTimeout(() => $wire.clearPendingRemoval($event.detail.boletaId), 3500)">

    @forelse($boletas as $bg)
        @php
            $isOpen = (bool) ($panelsOpen[$bg->id] ?? false);
            $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
            $rest = max(0, (float) $bg->retencion - $totalDev);
            $devuelta = $totalDev >= (float) $bg->retencion;
            $hasDev = ($bg->devoluciones?->count() ?? 0) > 0;
            $cur = $bg->moneda === 'USD' ? '$' : 'Bs';

            $fPath = $bg->foto_comprobante ?? null;

            $tipoShort = match ($bg->tipo) {
                'SERIEDAD' => 'Seriedad',
                'CUMPLIMIENTO' => 'Cumplimiento',
                default => $bg->tipo ?? '—',
            };
            // left-border + badge colors per tipo
            $accentBorder = match ($bg->tipo) {
                'SERIEDAD' => 'border-t-blue-400 dark:border-t-blue-500',
                'CUMPLIMIENTO' => 'border-t-amber-400 dark:border-t-amber-500',
                default => 'border-t-gray-300 dark:border-t-neutral-600',
            };
            $tipoBadge = match ($bg->tipo) {
                'SERIEDAD'
                    => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                'CUMPLIMIENTO'
                    => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                default
                    => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700',
            };

            $isTargetBoleta = isset($highlight_boleta_id) && $highlight_boleta_id == $bg->id;
        @endphp

        <div @if ($isTargetBoleta) id="boleta-mob-row-target-{{ $bg->id }}" @endif
            wire:key="boleta-mob-{{ $bg->id }}"
            class="rounded-xl border-t-4 border border-gray-200 dark:border-neutral-700 overflow-hidden
                {{ $accentBorder }}
                {{ $isTargetBoleta ? 'bg-indigo-50/50 border-indigo-300 dark:border-indigo-500/40 dark:bg-indigo-900/10' : 'bg-white dark:bg-neutral-900/50' }}">

            {{-- ── CARD HEADER (tap to expand) ── --}}
            <button type="button" @click="$wire.togglePanel({{ $bg->id }})"
                class="w-full text-left px-3 py-2.5 flex flex-col gap-1 cursor-pointer
                       hover:bg-gray-50/80 dark:hover:bg-white/5 transition-colors">

                {{-- Top row: badges + amounts --}}
                <div class="flex items-start justify-between gap-2">
                    {{-- Badges --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        {{-- Pending removal state --}}
                        @if (isset($pendingRemoval[(string) $bg->id]))
                            <div x-data="{ seconds: 3, progress: 100 }" x-init="let s = Date.now();
                            setInterval(() => { if (seconds > 0) seconds-- }, 1000);
                            let iv = setInterval(() => {
                                progress = Math.max(0, 100 - ((Date.now() - s) / 3000 * 100));
                                if (progress <= 0) {
                                    clearInterval(iv);
                                    $wire.dispatch('bg:clear-pending-removal', { boletaId: {{ $bg->id }} })
                                }
                            }, 50);" class="flex items-center gap-1.5">
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 animate-pulse dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800">
                                    MOVIENDO…
                                </span>
                                <div class="flex items-center gap-1">
                                    <div class="w-14 h-1 bg-gray-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full transition-all"
                                            :style="'width:' + progress + '%'">
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                        x-text="seconds+'s'"></span>
                                </div>
                            </div>
                        @else
                            {{-- Status --}}
                            @if ($devuelta)
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                    Devuelta
                                </span>
                            @else
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20">
                                    Activo
                                </span>
                            @endif
                            {{-- Tipo --}}
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $tipoBadge }}">
                                {{ $tipoShort }}
                            </span>
                            {{-- Moneda --}}
                            <span
                                class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700">
                                {{ $bg->moneda ?? 'BOB' }}
                            </span>
                        @endif
                    </div>

                    {{-- Amounts (solo contraído) --}}
                    @if (!$isOpen)
                        <div class="shrink-0 text-right">
                            <div
                                class="text-[12px] tabular-nums mt-0.5 leading-none
                                {{ $rest > 0 ? 'font-bold text-gray-500 dark:text-gray-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $rest > 0 ? 'Saldo: ' . number_format($rest, 2, ',', '.') : '✓ Completo' }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sub-row: nro · entidad --}}
                <div class="flex items-center gap-1 text-[12px] text-gray-400 dark:text-neutral-500 flex-wrap">
                    <span class="font-medium text-gray-500 dark:text-neutral-400">Nro
                        Boleta: {{ $bg->nro_boleta ?? '—' }}</span>
                    @if ($bg->entidad?->nombre)
                        <span>·</span>
                        <span class="truncate">{{ $bg->entidad->nombre }}</span>
                    @endif
                </div>

                {{-- Project name (full width) --}}
                <div
                    class="text-[13px] font-semibold leading-snug text-gray-900 dark:text-neutral-100
                    {{ $isOpen ? '' : 'line-clamp-2' }}">
                    {{ $bg->proyecto?->nombre ?? '—' }}
                </div>
            </button>

            {{-- ── EXPANDED BODY ── --}}
            @if ($isOpen)
                <div x-transition.opacity.duration.150ms>

                    {{-- Metrics strip --}}
                    <div
                        class="mx-3 mb-2.5 grid grid-cols-3 divide-x divide-gray-100 dark:divide-neutral-800 bg-gray-50 dark:bg-neutral-800/50 rounded-lg border border-gray-100 dark:border-neutral-700 text-center">
                        <div class="py-2 px-1">
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Retención</div>
                            <div class="text-xs font-bold tabular-nums text-gray-800 dark:text-neutral-200">
                                {{ $cur }} {{ number_format((float) $bg->retencion, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="py-2 px-1">
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Devuelto</div>
                            <div class="text-xs font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                                {{ $cur }} {{ number_format($totalDev, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="py-2 px-1">
                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 mb-0.5">Saldo</div>
                            <div
                                class="text-xs font-bold tabular-nums {{ $rest > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-gray-400' }}">
                                {{ $cur }} {{ number_format($rest, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Detail rows --}}
                    <div class="px-3 space-y-1.5 text-xs pb-2.5">

                        {{-- Agente --}}
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                <span class="text-gray-400 dark:text-neutral-500 shrink-0">Agente</span>
                                <span class="font-medium text-gray-700 dark:text-neutral-300 truncate text-right">
                                    {{ $bg->agenteServicio?->nombre ?? '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Banco --}}
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                <span class="text-gray-400 dark:text-neutral-500 shrink-0">Banco</span>
                                <span class="font-medium text-gray-700 dark:text-neutral-300 truncate text-right">
                                    {{ $bg->bancoEgreso?->nombre ?? '—' }}
                                    @if ($bg->bancoEgreso?->titular)
                                        <span class="font-normal text-gray-400"> ·
                                            {{ $bg->bancoEgreso->titular }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Fechas --}}
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                <span class="text-gray-400 dark:text-neutral-500 shrink-0">Fechas</span>
                                <span class="font-medium text-gray-700 dark:text-neutral-300 text-right">
                                    {{ $bg->fecha_emision?->format('d/m/y') ?? '—' }}
                                    <span class="text-gray-400 font-normal"> → </span>
                                    {{ $bg->fecha_vencimiento?->format('d/m/y') ?? '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Código proyecto --}}
                        @if ($bg->proyecto?->codigo)
                            <div class="flex items-start gap-2">
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                <div class="min-w-0 flex-1 flex items-baseline justify-between gap-2">
                                    <span class="text-gray-400 dark:text-neutral-500 shrink-0">Código</span>
                                    <span
                                        class="font-medium text-gray-700 dark:text-neutral-300 tabular-nums">{{ $bg->proyecto->codigo }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Observación --}}
                        @if ($bg->observacion)
                            @php $obs = $bg->observacion; @endphp
                            <div class="flex items-start gap-2 pt-1 border-t border-gray-100 dark:border-neutral-800"
                                x-data="{ open: false }">
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-neutral-600 mt-0.5 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                <div class="flex-1 min-w-0 text-gray-500 dark:text-neutral-400 leading-relaxed">
                                    @if (mb_strlen($obs) > 60)
                                        <span x-show="!open" class="line-clamp-2">{{ $obs }}</span>
                                        <span x-show="open" x-cloak
                                            class="whitespace-pre-line">{{ $obs }}</span>
                                        <button type="button" @click.stop="open=!open"
                                            class="text-blue-500 hover:underline cursor-pointer ml-1"
                                            x-text="open ? 'Ver menos' : 'Ver más'"></button>
                                    @else
                                        {{ $obs }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Devoluciones --}}
                    @if ($hasDev)
                        <div
                            class="mx-3 mb-2.5 rounded-lg border border-gray-100 dark:border-neutral-700 overflow-hidden">
                            <div
                                class="flex items-center justify-between px-2.5 py-1.5 bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-100 dark:border-neutral-700">
                                <span class="text-[11px] font-semibold text-gray-600 dark:text-neutral-300">
                                    Devoluciones ({{ $bg->devoluciones->count() }})
                                </span>
                                <span class="text-[11px] text-gray-400 dark:text-neutral-500 tabular-nums">
                                    Saldo: {{ $cur }} {{ number_format($rest, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @foreach ($bg->devoluciones as $dv)
                                    @php
                                        $dvPath = $dv->foto_comprobante ?? null;
                                        $isDevHighlighted =
                                            isset($highlight_devolucion_id) &&
                                            (int) $highlight_devolucion_id === (int) $dv->id;
                                    @endphp
                                    <div wire:key="boleta-mob-{{ $bg->id }}-dev-{{ $dv->id }}"
                                        @if ($isDevHighlighted) id="devolucion-mob-panel-target-{{ $dv->id }}" @endif
                                        class="flex items-center gap-2 px-2.5 py-2 text-xs transition-colors
                                        {{ $isDevHighlighted ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-white dark:bg-neutral-900/40' }}">

                                        {{-- Info --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-gray-800 dark:text-neutral-200 truncate">
                                                {{ $dv->banco?->nombre ?? '—' }}
                                                <span
                                                    class="text-gray-400 font-normal">({{ $dv->banco?->moneda ?? '' }})</span>
                                            </div>
                                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 truncate">
                                                {{ $dv->fecha_devolucion?->format('d/m/y H:i') }}
                                                @if ($dv->nro_transaccion)
                                                    · Op: {{ $dv->nro_transaccion }}
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Amount --}}
                                        <div
                                            class="shrink-0 font-bold tabular-nums text-gray-900 dark:text-white text-xs">
                                            {{ $cur }} {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                        </div>

                                        {{-- Actions --}}
                                        <div class="shrink-0 flex items-center gap-1">
                                            <x-comprobante-btn :path="$dvPath" />
                                            @can('boletas_garantia.delete')
                                                <button type="button" @click.stop
                                                    wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }}, 'Se eliminará la devolución de {{ $cur }} {{ number_format((float) $dv->monto, 2, ',', '.') }} asociada a la Boleta Nro. {{ $bg->nro_boleta ?? '' }}. Esta acción no se puede deshacer.')"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-red-200 text-red-500
                                                        hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30 transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Action bar --}}
                    @canany(['boletas_garantia.register_return', 'boletas_garantia.delete', 'boletas_garantia.view'])
                        <div class="px-3 pb-3 grid grid-cols-3 gap-1.5">
                            @can('boletas_garantia.register_return')
                                <button type="button" @click.stop wire:click="openDevolucion({{ $bg->id }})"
                                    @disabled($rest <= 0) title="Registrar Devolución"
                                    class="flex items-center justify-center py-1 rounded-md border transition
                                    {{ $rest <= 0
                                        ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                        : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 shadow-sm cursor-pointer' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </button>
                            @endcan

                            <x-comprobante-btn :path="$fPath"
                                size-class="w-full py-1 rounded-md border transition flex items-center justify-center"
                                svg-class="w-4 h-4" />

                            @can('boletas_garantia.delete')
                                <button type="button" @click.stop wire:click="abrirEliminarBoletaModal({{ $bg->id }})"
                                    @disabled($hasDev) title="Eliminar boleta"
                                    class="flex items-center justify-center py-1 rounded-md border transition
                                    {{ $hasDev
                                        ? 'bg-gray-100 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700'
                                        : 'bg-white text-red-500 border-red-200 hover:bg-red-50 hover:border-red-300 shadow-sm cursor-pointer dark:bg-neutral-900 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/20' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2" />
                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                </button>
                            @endcan
                        </div>
                    @endcanany

                </div>
            @endif
        </div>

    @empty
        <div class="py-8 text-center text-sm text-gray-400 dark:text-neutral-500">
            Sin resultados.
        </div>
    @endforelse
</div>
