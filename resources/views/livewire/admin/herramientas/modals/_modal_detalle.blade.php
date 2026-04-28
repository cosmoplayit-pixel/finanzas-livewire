    {{-- ===================== MODAL VER DETALLE ===================== --}}
    <x-ui.modal wire:key="detail-herramienta-modal" model="detailModal" title="Ficha de Herramienta" maxWidth="sm:max-w-4xl"
        onClose="closeDetail">

        @if (!empty($detail))

            {{-- Cabecera fullwidth --}}
            <div class="flex gap-4 items-start mb-4">
                @if ($detail['imagen'])
                    @php
                        $imageUrl = Storage::url($detail['imagen']);
                        $isPdf = \Illuminate\Support\Str::contains(strtolower($imageUrl), '.pdf');
                    @endphp
                    @if ($isPdf)
                        <div class="w-20 h-20 rounded-xl bg-red-50 dark:bg-red-900/20 flex flex-col items-center justify-center border border-red-100 dark:border-red-800 shrink-0 cursor-pointer hover:opacity-80 transition"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $imageUrl }}' }))">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-[10px] font-bold text-red-600 mt-1 uppercase leading-none">PDF</span>
                        </div>
                    @else
                        <img src="{{ $imageUrl }}" alt="{{ $detail['nombre'] }}"
                            class="w-20 h-20 rounded-xl object-cover border border-gray-200 dark:border-neutral-700 shrink-0 cursor-pointer hover:opacity-80 transition"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $imageUrl }}' }))">
                    @endif
                @else
                    <div
                        class="w-20 h-20 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 shrink-0">
                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    @if ($detail['codigo'])
                        <span
                            class="font-mono text-xs text-indigo-600 dark:text-indigo-400 font-semibold">{{ $detail['codigo'] }}</span>
                    @endif
                    <h3 class="font-bold text-gray-900 dark:text-neutral-100 text-lg leading-tight mt-0.5">
                        {{ $detail['nombre'] }}</h3>
                    @if ($detail['marca'] || $detail['modelo'])
                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                            {{ implode(' — ', array_filter([$detail['marca'], $detail['modelo']])) }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded border text-xs font-medium {{ $detail['estado_fisico_badge'] }}">
                            {{ $detail['estado_fisico_label'] }}
                        </span>
                        @if ($detail['active'])
                            <span
                                class="px-2 py-0.5 rounded text-[11px] font-medium bg-gray-50 text-gray-500 dark:bg-neutral-800 dark:text-neutral-400 border border-gray-200 dark:border-neutral-700">Activo</span>
                        @else
                            <span
                                class="px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 border border-red-100 dark:border-red-500/20">Inactivo</span>
                        @endif
                        @if ($detail['empresa'])
                            <span
                                class="px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">{{ $detail['empresa'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pre-calcular series activas --}}
            @php
                $seriesActivas = collect($detail['series'] ?? [])
                    ->filter(fn($s) => ($s['estado'] ?? '') !== 'baja')
                    ->values();
                $tieneSeriesActivas =
                    in_array($detail['tipo'] ?? '', ['activo', 'equipo']) && $seriesActivas->isNotEmpty();
            @endphp

            {{-- ══ STOCK — Ancho completo ══ --}}
            <div class="rounded-xl border border-gray-100 dark:border-neutral-800 overflow-hidden mb-4">
                <div
                    class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-100 dark:border-neutral-800">
                    <span
                        class="text-[10px] uppercase font-black tracking-widest text-gray-400 dark:text-neutral-500">Inventario
                        de Stock</span>
                    @if ($detail['unidad'])
                        <span
                            class="text-[10px] font-semibold text-gray-400 dark:text-neutral-500 uppercase">{{ $detail['unidad'] }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-neutral-800">
                    <div class="px-4 py-1.5 text-center">
                        <div
                            class="text-[10px] uppercase font-bold tracking-widest text-gray-400 dark:text-neutral-500 mb-1">
                            Total</div>
                        <div class="text-xl font-black text-gray-800 dark:text-neutral-100 leading-none">
                            {{ $detail['stock_total'] }}</div>
                        <div class="text-[10px] text-gray-400 dark:text-neutral-600 mt-1">unidades</div>
                    </div>
                    <div class="px-4 py-1.5 text-center bg-emerald-50/50 dark:bg-emerald-500/5">
                        <div
                            class="text-[10px] uppercase font-bold tracking-widest text-emerald-500 dark:text-emerald-400 mb-1">
                            Disponible</div>
                        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none">
                            {{ $detail['stock_disponible'] }}</div>
                        <div class="text-[10px] text-emerald-400/70 mt-1">{{ $detail['pct_disponible'] }}%</div>
                    </div>
                    <div class="px-4 py-1.5 text-center bg-amber-50/50 dark:bg-amber-500/5">
                        <div
                            class="text-[10px] uppercase font-bold tracking-widest text-amber-500 dark:text-amber-400 mb-1">
                            Préstamo</div>
                        <div class="text-xl font-black text-amber-600 dark:text-amber-400 leading-none">
                            {{ $detail['stock_prestado'] }}</div>
                        <div class="text-[10px] text-amber-400/70 mt-1">{{ $detail['pct_prestado'] }}%</div>
                    </div>
                </div>
                <div class="px-4 pb-3 pt-2">
                    <div class="h-2 rounded-full bg-gray-100 dark:bg-neutral-800 overflow-hidden flex">
                        @if ($detail['pct_disponible'] > 0)
                            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all duration-500"
                                style="width: {{ $detail['pct_disponible'] }}%"></div>
                        @endif
                        @if ($detail['pct_prestado'] > 0)
                            <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-500"
                                style="width: {{ $detail['pct_prestado'] }}%"></div>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="flex items-center gap-1 text-[10px] text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Disponible
                        </span>
                        <span class="flex items-center gap-1 text-[10px] text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span> En préstamo
                        </span>
                    </div>
                </div>
            </div>

            {{-- ══ GRID ADAPTATIVO: 2 cols si hay series, sino 1 col ══ --}}
            <div class="{{ $tieneSeriesActivas ? 'grid grid-cols-1 md:grid-cols-2 gap-4 items-start' : '' }}">

                {{-- Números de Serie — solo si hay activas --}}
                @if ($tieneSeriesActivas)
                    <div class="rounded-xl border border-gray-100 dark:border-neutral-800 overflow-hidden">
                        <div
                            class="px-4 py-2 bg-gray-50 dark:bg-neutral-800/60 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800">
                            <span
                                class="text-[10px] uppercase font-bold tracking-widest text-gray-400 dark:text-neutral-500">Números
                                de Serie</span>
                            <span
                                class="text-xs font-black text-gray-600 dark:text-neutral-300 bg-gray-200 dark:bg-neutral-700 px-2 py-0.5 rounded-full">{{ $seriesActivas->count() }}</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-neutral-800">
                            @foreach ($seriesActivas as $s)
                                @php
                                    $estadoClass = match ($s['estado'] ?? '') {
                                        'prestado'
                                            => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'mantenimiento'
                                            => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        default
                                            => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    };
                                    $estadoLabel = match ($s['estado'] ?? '') {
                                        'prestado' => 'Prestado',
                                        'mantenimiento' => 'Mant.',
                                        default => 'Disponible',
                                    };
                                @endphp
                                <div class="flex items-center justify-between px-4 py-2.5">
                                    <span
                                        class="font-mono text-xs font-semibold text-gray-800 dark:text-neutral-200">{{ $s['serie'] }}</span>
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $estadoClass }}">{{ $estadoLabel }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Datos + Historial --}}
                <div class="flex flex-col gap-4">

                    {{-- Datos adicionales --}}
                    <div
                        class="rounded-xl border border-gray-100 dark:border-neutral-800 divide-y divide-gray-100 dark:divide-neutral-800 overflow-hidden text-sm">
                        @if ($detail['unidad'])
                            <div class="flex px-3 py-1.5">
                                <span
                                    class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Unidad</span>
                                <span
                                    class="text-gray-800 dark:text-neutral-200 font-semibold">{{ $detail['unidad'] }}</span>
                            </div>
                        @endif
                        <div class="flex px-3 py-1.5">
                            <span class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">P.
                                Unitario</span>
                            <span class="text-gray-800 dark:text-neutral-200 font-semibold tabular-nums">Bs.
                                {{ $detail['precio_unitario'] }}</span>
                        </div>
                        <div class="flex px-3 py-1.5">
                            <span
                                class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Inversión
                                Total</span>
                            <span class="text-gray-800 dark:text-neutral-200 font-semibold tabular-nums">Bs.
                                {{ $detail['precio_total'] }}</span>
                        </div>
                        @if ($detail['descripcion'])
                            <div class="flex px-3 py-1.5">
                                <span
                                    class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Descripción</span>
                                <span
                                    class="text-gray-700 dark:text-neutral-200 font-semibold">{{ $detail['descripcion'] }}</span>
                            </div>
                        @endif
                        <div class="flex px-3 py-1.5 bg-gray-50/50 dark:bg-neutral-900/20">
                            <span
                                class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Registro</span>
                            <span
                                class="text-gray-500 dark:text-neutral-500 text-xs">{{ $detail['created_at'] }}</span>
                        </div>
                        <div class="flex px-3 py-1.5 bg-gray-50/50 dark:bg-neutral-900/20">
                            <span class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Últ.
                                modificación</span>
                            <span
                                class="text-gray-500 dark:text-neutral-500 text-xs">{{ $detail['updated_at'] }}</span>
                        </div>
                    </div>

                    {{-- Historial de Préstamos Recientes --}}
                    @if (isset($detail['prestamos_recientes']) && count($detail['prestamos_recientes']) > 0)
                        <div>
                            <div class="text-[10px] uppercase font-bold text-gray-500 dark:text-neutral-400 mb-2 px-1">
                                Últimos Préstamos ({{ count($detail['prestamos_recientes']) }})
                            </div>
                            <div
                                class="rounded-xl border border-gray-100 dark:border-neutral-800 overflow-hidden divide-y divide-gray-50 dark:divide-neutral-800">
                                @foreach ($detail['prestamos_recientes'] as $p)
                                    @php
                                        $estadoBadge = match ($p['estado']) {
                                            'finalizado'
                                                => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                            'vencido' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                            default
                                                => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                        };
                                        $estadoLabel = match ($p['estado']) {
                                            'finalizado' => 'Devuelto',
                                            'vencido' => 'Vencido',
                                            default => 'En obra',
                                        };
                                    @endphp
                                    <div
                                        class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50/50 dark:hover:bg-neutral-800/20 transition">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span
                                                    class="font-mono text-[10px] font-black text-indigo-600 dark:text-indigo-400">{{ $p['nro'] }}</span>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded font-black uppercase {{ $estadoBadge }}">{{ $estadoLabel }}</span>
                                            </div>
                                            <div
                                                class="text-[11px] text-gray-600 dark:text-neutral-300 truncate mt-0.5">
                                                {{ $p['entidad'] }}</div>
                                            <div class="text-[10px] text-gray-400 dark:text-neutral-500 truncate">
                                                {{ $p['proyecto'] }}</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs font-black text-gray-700 dark:text-neutral-300">
                                                {{ $p['cantidad'] }} u.</div>
                                            <div class="text-[10px] text-gray-400 dark:text-neutral-500">
                                                {{ $p['fecha'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Historial de Bajas --}}
                    @if (isset($detail['bajas']) && count($detail['bajas']) > 0)
                        <div>
                            <div class="text-[10px] uppercase font-bold text-gray-500 mb-2 px-1">Historial de Bajas
                                ({{ count($detail['bajas']) }})</div>
                            <div
                                class="rounded-xl border border-red-100 dark:border-red-900/50 overflow-hidden divide-y divide-red-50 dark:divide-red-900/20 bg-white dark:bg-neutral-900">
                                @foreach ($detail['bajas'] as $baja)
                                    <div class="p-3 bg-red-50/30 dark:bg-red-900/10">
                                        <div class="flex justify-between items-start mb-1.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                <span
                                                    class="text-xs font-bold text-red-700 dark:text-red-400">-{{ $baja['cantidad'] }}
                                                    unidades</span>
                                            </div>
                                            <span class="text-[10px] text-gray-400">{{ $baja['fecha'] }}</span>
                                        </div>
                                        @if (isset($baja['evidencia']) && $baja['evidencia'])
                                            @php
                                                $evidenciaUrl = Storage::url($baja['evidencia']);
                                                $isEvidenciaPdf = \Illuminate\Support\Str::contains(
                                                    strtolower($evidenciaUrl),
                                                    '.pdf',
                                                );
                                            @endphp
                                            <div class="mb-2 pl-3.5">
                                                @if ($isEvidenciaPdf)
                                                    <div class="w-16 h-12 rounded bg-red-50 dark:bg-red-900/20 flex flex-col items-center justify-center border border-red-100 dark:border-red-800 cursor-pointer hover:opacity-80 transition"
                                                        onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $evidenciaUrl }}' }))">
                                                        <svg class="w-5 h-5 text-red-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @else
                                                    <img src="{{ $evidenciaUrl }}"
                                                        class="w-16 h-12 object-cover rounded shadow-sm border border-red-200 dark:border-red-800 cursor-pointer"
                                                        onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ $evidenciaUrl }}' }))">
                                                @endif
                                            </div>
                                        @endif
                                        @if ($baja['observaciones'])
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 pl-3.5">
                                                {{ $baja['observaciones'] }}</p>
                                        @endif
                                        <p class="text-[10px] text-gray-400 mt-1 pl-3.5 italic">Por:
                                            {{ $baja['usuario'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>{{-- /datos + historial --}}

            </div>{{-- /grid adaptativo --}}

        @endif

        @slot('footer')
            <div class="flex justify-end gap-2">

                <button type="button" wire:click="closeDetail"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cerrar
                </button>

                @can('herramientas.update')
                    <button type="button" wire:click="openEditFromDetail({{ $detail['id'] ?? 0 }})"
                        class="px-4 py-2 rounded-lg cursor-pointer bg-amber-600 text-white hover:bg-amber-700 transition text-sm font-bold">
                        Editar
                    </button>
                @endcan
            </div>
        @endslot
    </x-ui.modal>
