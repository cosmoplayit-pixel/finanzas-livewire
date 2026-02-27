{{-- MOBILE --}}
<div class="md:hidden space-y-3">
    @forelse($boletas as $bg)
        @php
            $totalDev = (float) ($bg->devoluciones?->sum('monto') ?? 0);
            $rest = max(0, (float) $bg->retencion - $totalDev);
            $devuelta = $totalDev >= (float) $bg->retencion;
        @endphp

        <div x-data="{ open: false }"
            class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">

            {{-- HEADER --}}
            <button type="button" @click="open = !open"
                class="w-full px-4 py-3 flex items-start justify-between gap-3 text-left
                       hover:bg-gray-50 dark:hover:bg-neutral-900 transition">

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="text-sm font-extrabold text-gray-900 dark:text-neutral-100">
                            #{{ $bg->id }}
                        </div>

                        @if ($devuelta)
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                Devuelta
                            </span>
                        @else
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200">
                                Abierta
                            </span>
                        @endif
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-neutral-100"
                         :class="open ? 'whitespace-normal' : 'truncate'">
                        {{ $bg->proyecto?->nombre ?? '—' }}
                    </div>

                    <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-neutral-400">
                        Nro: {{ $bg->nro_boleta ?? '—' }} • {{ $bg->tipo ?? '—' }}
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
            <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="px-4 pb-4 pt-1 space-y-3">
                

                {{-- INFO EXTRA --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    {{-- Entidad y Agente --}}
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Entidad & Agente</div>
                            <div class="text-xs text-gray-500 truncate mt-0.5">{{ $bg->entidad?->nombre ?? '—' }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $bg->agenteServicio?->nombre ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Banco Egreso --}}
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <div class="flex-1 min-w-0">                           
                            <div class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate mt-0.5">{{ $bg->bancoEgreso?->nombre ?? '—' }}</div>
                            <div class="text-[11px] text-gray-500 truncate">{{ $bg->bancoEgreso?->numero_cuenta ?? '—' }} ({{ $bg->bancoEgreso?->moneda ?? '—' }})</div>
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Fechas</div>
                            <div class="flex items-center gap-3 mt-0.5">
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    Emis: <span class="font-medium text-gray-900 dark:text-white">{{ $bg->fecha_emision?->format('d/m/y') ?? '—' }}</span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    Venc: <span class="font-medium text-gray-900 dark:text-white">{{ $bg->fecha_vencimiento?->format('d/m/y') ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Comprobante (Si existe) --}}
                    @if ($bg->foto_comprobante)
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Respaldo</div>
                                @php
                                    $ext = strtolower(pathinfo($bg->foto_comprobante, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                @endphp
                                <div class="mt-0.5">
                                    @if ($isImage)
                                        <button type="button" 
                                            class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                            @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $bg->foto_comprobante) }}' })">
                                            Ver comprobante adjunto
                                        </button>
                                    @else
                                        <a href="{{ asset('storage/' . $bg->foto_comprobante) }}" target="_blank" rel="noopener noreferrer" 
                                           class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                           @click.stop>
                                            Ver comprobante (PDF)
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Total Egreso --}}
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Total Egreso</div>
                            <div class="text-sm font-extrabold tabular-nums text-gray-900 dark:text-white mt-0.5">
                                {{ $bg->moneda === 'USD' ? '$' : 'Bs' }} {{ number_format((float) $bg->total, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($bg->observacion))
                   <div class="text-xs text-gray-500 dark:text-neutral-400 italic bg-gray-50 dark:bg-neutral-800 p-2 rounded border border-gray-100 dark:border-neutral-700">
                       <span class="font-semibold">Obs:</span> {{ $bg->observacion }}
                     </div>
                @endif
                
                {{-- DEVOLUCIONES COMPACTAS --}}
                @if($bg->devoluciones && $bg->devoluciones->count() > 0)
                <div class="mt-2 bg-gray-50 dark:bg-neutral-800/50 rounded-lg p-2.5 border border-gray-100 dark:border-neutral-700">
                    <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2 flex justify-between items-center">
                        <span>Devoluciones ({{ $bg->devoluciones->count() }})</span>
                        <span class="text-[11px] font-normal text-gray-500">Restante: {{ number_format((float) $rest, 2, ',', '.') }}</span>
                    </div>
                    <div class="space-y-1.5">
                        @foreach($bg->devoluciones as $dv)
                        <div class="flex items-center justify-between text-xs bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 p-2 rounded shadow-sm">
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $dv->banco?->nombre ?? '—' }} <span class="text-gray-400 font-normal">({{ $dv->banco?->moneda ?? '' }})</span></div>
                                <div class="text-gray-500 text-[10px] truncate mt-0.5 flex flex-col items-start gap-1">
                                    <span>{{ $dv->fecha_devolucion?->format('d/m/Y H:i') }} • Op: {{ $dv->nro_transaccion ?? 'S/N' }}</span>
                                    
                                    @if($dv->foto_comprobante)
                                        @php
                                            $ext = strtolower(pathinfo($dv->foto_comprobante, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
                                        @endphp
                                        @if($isImage)
                                            <button type="button" class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium hover:underline"
                                                @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $dv->foto_comprobante) }}' })">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Ver respaldo
                                            </button>
                                        @else
                                            <a href="{{ asset('storage/' . $dv->foto_comprobante) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[10px] text-red-600 dark:text-red-400 font-medium hover:underline">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> Ver PDF
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 border-l border-gray-100 dark:border-neutral-700 pl-2">
                                <div class="font-bold tabular-nums text-gray-900 dark:text-white">
                                    {{ $bg->moneda === 'USD' ? '$' : 'Bs' }} {{ number_format((float) $dv->monto, 2, ',', '.') }}
                                </div>
                                <button type="button" @click.stop wire:click="confirmDeleteDevolucion({{ $bg->id }}, {{ $dv->id }})"
                                    class="text-red-400 hover:text-red-600 dark:text-red-500 dark:hover:text-red-400 transition ml-1" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                {{-- BOTON ACCIONES --}}
                <div class="pt-1">
                    <button type="button" @click.stop wire:click="openDevolucion({{ $bg->id }})"
                        @disabled($rest <= 0)
                        class="w-full px-4 py-2.5 rounded-lg border text-sm font-semibold transition flex justify-center items-center gap-2
                            {{ $rest <= 0
                                ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-500 dark:border-neutral-700'
                                : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700 shadow-sm' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        Registrar Devolución
                    </button>
                </div>

            </div>
        </div>

    @empty
        <div class="p-4 text-center text-gray-500 dark:text-neutral-400">
            Sin resultados.
        </div>
    @endforelse
</div>
