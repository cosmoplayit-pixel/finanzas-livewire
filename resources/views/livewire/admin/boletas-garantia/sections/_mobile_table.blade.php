{{-- MOBILE --}}
<div class="md:hidden space-y-3" x-data="{
    boletaId: @entangle('highlight_boleta_id'),
    devolucionId: @entangle('highlight_devolucion_id'),
    scroll() {
        const devolucionEl = document.getElementById(`devolucion-mob-panel-target-${this.devolucionId}`);
        const boletaEl = document.getElementById(`boleta-mob-row-target-${this.boletaId}`);
        const el = devolucionEl || boletaEl;
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}" x-init="if (boletaId || devolucionId) {
    setTimeout(() => scroll(), 600);
}
$watch('boletaId', val => { if (val) setTimeout(() => scroll(), 300) });
$watch('devolucionId', val => { if (val) setTimeout(() => scroll(), 300) });">

    @forelse($boletas as $bg)
        @php
            $isOpen = (bool) ($panelsOpen[$bg->id] ?? false);
            $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
            $rest = max(0, (float) $bg->retencion - $totalDev);
            $devuelta = $totalDev >= (float) $bg->retencion;

            $tipoLabel = match ($bg->tipo) {
                'SERIEDAD' => 'Seriedad de Propuesta',
                'CUMPLIMIENTO' => 'Cumplimiento de Contrato',
                default => $bg->tipo ?? '—',
            };
            $tipoColor = match ($bg->tipo) {
                'SERIEDAD'
                    => 'bg-blue-100/60 text-blue-800 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                'CUMPLIMIENTO'
                    => 'bg-amber-100/60 text-amber-800 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                default
                    => 'bg-gray-100/60 text-gray-800 border-gray-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700',
            };
        @endphp

        @php
            $isTargetBoleta = isset($highlight_boleta_id) && $highlight_boleta_id == $bg->id;
        @endphp
        <div @if ($isTargetBoleta) id="boleta-mob-row-target-{{ $bg->id }}" @endif
            class="rounded-xl border dark:bg-neutral-900/40 overflow-hidden
            {{ $isTargetBoleta ? 'bg-indigo-50/60 border-indigo-400 dark:border-indigo-400/50' : 'bg-white border-gray-200 dark:border-neutral-700' }}"
            wire:key="boleta-mob-{{ $bg->id }}">


            {{-- HEADER --}}
            <button type="button" @click="$wire.togglePanel({{ $bg->id }})"
                class="w-full cursor-pointer px-4 py-3 flex items-start justify-between gap-3 text-left
                       hover:bg-gray-50 dark:hover:bg-neutral-900 transition">

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">

                        @if ($devuelta)
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                Devuelta
                            </span>
                        @else
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                ACTIVO
                            </span>
                        @endif
                    </div>

                    <div
                        class="mt-1 text-sm text-gray-900 dark:text-neutral-100 {{ $isOpen ? 'whitespace-normal' : 'truncate' }}">
                        {{ $bg->proyecto?->nombre ?? '—' }}
                    </div>

                    <div
                        class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-gray-500 dark:text-neutral-400">
                        <span>Nro: {{ $bg->nro_boleta ?? '—' }}</span>
                        <span class="text-gray-300">|</span>
                        <span class="px-1.5 py-0.5 rounded font-bold border leading-none {{ $tipoColor }}"
                            style="font-size: 9.5px;">
                            {{ $tipoLabel }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-1">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Devuelto</div>
                    <div class="text-sm font-bold tabular-nums text-gray-900 dark:text-neutral-100">
                        {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                        {{ number_format((float) $totalDev, 2, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-neutral-400">
                        Rest: {{ number_format((float) $rest, 2, ',', '.') }}
                    </div>
                </div>
            </button>

            {{-- BODY --}}
            @if ($isOpen)
                <div x-transition.opacity.duration.150ms class="px-4 pb-4 pt-1 space-y-3">


                    {{-- INFO EXTRA --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        {{-- Entidad y Agente --}}
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Entidad & Agente
                                </div>
                                <div class="text-xs text-gray-500 truncate mt-0.5">{{ $bg->entidad?->nombre ?? '—' }}
                                </div>
                                @if ($bg->proyecto?->codigo)
                                    <div class="text-[10px] text-gray-400 truncate italic">
                                        Cód: {{ $bg->proyecto->codigo }}
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500 truncate">{{ $bg->agenteServicio?->nombre ?? '—' }}
                                </div>
                            </div>
                        </div>

                        {{-- Banco Egreso --}}
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate mt-0.5">
                                    {{ $bg->bancoEgreso?->nombre ?? '—' }}</div>
                                <div class="text-[11px] text-gray-500 truncate italic mt-0.5">
                                    Titular: {{ $bg->bancoEgreso?->titular ?? '—' }}</div>
                                <div class="text-[11px] text-gray-500 truncate">
                                    {{ $bg->bancoEgreso?->numero_cuenta ?? '—' }}
                                    ({{ $bg->bancoEgreso?->moneda ?? '—' }})</div>
                            </div>
                        </div>

                        {{-- Fechas --}}
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Fechas</div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Emis: <span
                                            class="font-medium text-gray-900 dark:text-white">{{ $bg->fecha_emision?->format('d/m/y') ?? '—' }}</span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Venc: <span
                                            class="font-medium text-gray-900 dark:text-white">{{ $bg->fecha_vencimiento?->format('d/m/y') ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Observación --}}
                        @if ($bg->observacion)
                            @php
                                $obs = $bg->observacion;
                                $isLongObs = mb_strlen($obs) > 40;
                            @endphp
                            <div class="col-span-full border-t border-gray-100 dark:border-neutral-800 pt-3 flex items-start gap-2.5"
                                x-data="{ showFullObs: false }">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Observaciones
                                    </div>
                                    @if ($isLongObs)
                                        <div x-show="!showFullObs" class="mt-1">
                                            <div class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                                {{ $obs }}
                                            </div>
                                            <button type="button" @click.stop="showFullObs = true"
                                                class="mt-1 text-xs font-medium text-blue-600 hover:underline cursor-pointer">
                                                Ver más
                                            </button>
                                        </div>
                                        <div x-show="showFullObs" x-cloak class="mt-1">
                                            <div class="text-xs text-gray-500 whitespace-pre-line leading-relaxed">
                                                {{ $obs }}
                                            </div>
                                            <button type="button" @click.stop="showFullObs = false"
                                                class="mt-1 text-xs font-medium text-blue-600 hover:underline cursor-pointer">
                                                Ver menos
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 mt-1 whitespace-pre-line leading-relaxed">
                                            {{ $obs }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif



                        {{-- Total Egreso --}}
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Total Egreso
                                </div>
                                <div class="text-sm font-extrabold tabular-nums text-gray-900 dark:text-white mt-0.5">
                                    {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                    {{ number_format((float) $bg->total, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!empty($bg->observacion))
                        <div
                            class="text-xs text-gray-500 dark:text-neutral-400 italic bg-gray-50 dark:bg-neutral-800 p-2 rounded border border-gray-100 dark:border-neutral-700">
                            <span class="font-semibold">Obs:</span> {{ $bg->observacion }}
                        </div>
                    @endif

                    {{-- DEVOLUCIONES COMPACTAS --}}
                    @if ($bg->devoluciones && $bg->devoluciones->count() > 0)
                        <div
                            class="mt-2 bg-gray-50 dark:bg-neutral-800/50 rounded-lg p-2.5 border border-gray-100 dark:border-neutral-700">
                            <div
                                class="text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2 flex justify-between items-center">
                                <span>Devoluciones ({{ $bg->devoluciones->count() }})</span>
                                <span class="text-[11px] font-normal text-gray-500">Restante:
                                    {{ number_format((float) $rest, 2, ',', '.') }}</span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach ($bg->devoluciones as $dv)
                                    @php
                                        $isDevHighlighted =
                                            isset($highlight_devolucion_id) &&
                                            (int) $highlight_devolucion_id === (int) $dv->id;
                                    @endphp
                                    <div wire:key="boleta-mob-{{ $bg->id }}-dev-{{ $dv->id }}"
                                        @if ($isDevHighlighted) id="devolucion-mob-panel-target-{{ $dv->id }}" @endif
                                        class="flex items-center justify-between text-xs dark:bg-neutral-900 border p-2 rounded shadow-sm transition-colors
                                        {{ $isDevHighlighted ? 'bg-amber-50 border-amber-400 dark:border-amber-400/50' : 'bg-white border-gray-200 dark:border-neutral-700' }}">
                                        <div class="min-w-0 flex-1 pr-2">
                                            <div class="font-medium text-gray-800 dark:text-neutral-200 truncate">
                                                {{ $dv->banco?->nombre ?? '—' }} <span
                                                    class="text-gray-400 font-normal">({{ $dv->banco?->moneda ?? '' }})</span>
                                            </div>
                                            <div class="text-[10px] text-gray-500 italic truncate">
                                                Titular: {{ $dv->banco?->titular ?? '—' }}
                                            </div>
                                            <div
                                                class="text-gray-500 text-[10px] truncate mt-0.5 flex flex-col items-start gap-1">
                                                <span>{{ $dv->fecha_devolucion?->format('d/m/Y H:i') }} • Op:
                                                    {{ $dv->nro_transaccion ?? 'S/N' }}</span>


                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-2 shrink-0 border-l border-gray-100 dark:border-neutral-700 pl-2">
                                            <div class="flex items-center gap-1.5">
                                                {{-- VER COMPROBANTE DEVOLUCION (MOBILE LIST) --}}
                                                @php
                                                    $dvPath = $dv->foto_comprobante ?? null;
                                                    $dvExt = $dvPath
                                                        ? strtolower(pathinfo($dvPath, PATHINFO_EXTENSION))
                                                        : '';
                                                    $dvIsPdf = $dvExt === 'pdf';
                                                @endphp

                                                @if ($dvPath)
                                                    @if ($dvIsPdf)
                                                        <a href="{{ asset('storage/' . $dvPath) }}" target="_blank"
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                                            title="Ver PDF">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
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
                                                    @else
                                                        <button type="button"
                                                            @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $dvPath) }}' })"
                                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                            title="Ver imagen">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <rect x="3" y="3" width="18" height="18"
                                                                    rx="2" ry="2" />
                                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                                <polyline points="21 15 16 10 5 21" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                @else
                                                    <span
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                                        title="Sin comprobante">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
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

                                                <div class="font-bold tabular-nums text-gray-900 dark:text-white px-1">
                                                    {{ $bg->moneda === 'USD' ? '$' : 'Bs' }}
                                                    {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                                </div>

                                                @can('boletas_garantia.delete')
                                                    <button type="button" @click.stop
                                                        wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }}, 'Se eliminará la devolución de {{ $bg->moneda === 'USD' ? '$' : 'Bs' }} {{ number_format((float) $dv->monto, 2, ',', '.') }} asociada a la Boleta Nro. {{ $bg->nro_boleta ?? '' }}. Esta acción no se puede deshacer.')"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-red-300 text-red-700
                                                            hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition cursor-pointer"
                                                        title="Eliminar">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @php
                        $hasDevoluciones = ($bg->devoluciones?->count() ?? 0) > 0;
                    @endphp
                    @canany(['boletas_garantia.register_return', 'boletas_garantia.delete'])
                        {{-- BOTONES ACCIONES --}}
                        <div class="pt-1 flex gap-2">
                            @can('boletas_garantia.register_return')
                                <button type="button" @click.stop wire:click="openDevolucion({{ $bg->id }})"
                                    @disabled($rest <= 0)
                                    class="flex-1 px-4 py-2.5 rounded-lg border text-sm font-semibold transition flex justify-center items-center gap-2
                                    {{ $rest <= 0
                                        ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-500 dark:border-neutral-700'
                                        : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700 shadow-sm cursor-pointer' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    Registrar Devolución
                                </button>
                            @endcan

                            {{-- VER COMPROBANTE BOLETA --}}
                            @php
                                $fPath = $bg->foto_comprobante ?? null;
                                $fExt = $fPath ? strtolower(pathinfo($fPath, PATHINFO_EXTENSION)) : '';
                                $fIsPdf = $fExt === 'pdf';
                            @endphp

                            @if ($fPath)
                                @if ($fIsPdf)
                                    <a href="{{ asset('storage/' . $fPath) }}" target="_blank"
                                        class="px-3.5 py-2.5 rounded-lg border text-sm font-semibold transition flex justify-center items-center bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                        title="Ver PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="9" y1="13" x2="15" y2="13" />
                                            <line x1="9" y1="17" x2="15" y2="17" />
                                            <line x1="9" y1="9" x2="11" y2="9" />
                                        </svg>
                                    </a>
                                @else
                                    <button type="button" @click.stop
                                        @click="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $fPath) }}' })"
                                        class="px-3.5 py-2.5 rounded-lg border text-sm font-semibold transition flex justify-center items-center bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                        title="Ver imagen">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2"
                                                ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
                                        </svg>
                                    </button>
                                @endif
                            @else
                                <span
                                    class="px-3.5 py-2.5 rounded-lg border text-sm font-semibold transition flex justify-center items-center bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
                                    title="Sin comprobante">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                </span>
                            @endif

                            @can('boletas_garantia.delete')
                                <button type="button" @click.stop wire:click="abrirEliminarBoletaModal({{ $bg->id }})"
                                    @disabled($hasDevoluciones)
                                    class="px-3.5 py-2.5 rounded-lg border text-sm font-semibold transition flex justify-center items-center gap-2
                                    {{ $hasDevoluciones
                                        ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-500 dark:border-neutral-700'
                                        : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100 hover:border-red-300 shadow-sm dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/40 dark:hover:bg-red-900/40 cursor-pointer' }}"
                                    title="Eliminar Boleta">
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
                    @endcanany

                </div>
            @endif
        </div>

    @empty
        <div class="p-4 text-center text-gray-500 dark:text-neutral-400">
            Sin resultados.
        </div>
    @endforelse
</div>
