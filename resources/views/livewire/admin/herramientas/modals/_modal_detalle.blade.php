    {{-- ===================== MODAL VER DETALLE ===================== --}}
    <x-ui.modal wire:key="detail-herramienta-modal" model="detailModal" title="Ficha de Herramienta"
        maxWidth="sm:max-w-lg" onClose="closeDetail">

        @if (!empty($detail))
            <div class="space-y-4">
                {{-- Cabecera --}}
                <div class="flex gap-4 items-start">
                    @if ($detail['imagen'])
                        <img src="{{ Storage::url($detail['imagen']) }}" alt="{{ $detail['nombre'] }}"
                            class="w-20 h-20 rounded-xl object-cover border border-gray-200 dark:border-neutral-700 shrink-0 cursor-pointer hover:opacity-80 transition"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($detail['imagen']) }}' }))">
                    @else
                        <div
                            class="w-20 h-20 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 shrink-0">
                            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                        <h3 class="font-bold text-gray-900 dark:text-neutral-100 text-base leading-tight mt-0.5">
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

                {{-- Stocks con skill bars --}}
                <div class="rounded-xl border border-gray-100 dark:border-neutral-800 overflow-hidden">
                    {{-- Totalizador --}}
                    <div class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-neutral-800/60">
                        <span
                            class="text-[10px] uppercase font-bold tracking-widest text-gray-400 dark:text-neutral-500">Stock
                            Total</span>
                        <span
                            class="text-lg font-black text-gray-800 dark:text-neutral-100">{{ $detail['stock_total'] }}
                            @if ($detail['unidad'])
                                <span class="text-xs font-normal text-gray-400">{{ $detail['unidad'] }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-neutral-800">
                        {{-- Disponible --}}
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                    <span
                                        class="text-xs font-semibold text-gray-700 dark:text-neutral-300">Disponible</span>
                                </div>
                                <span
                                    class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $detail['stock_disponible'] }}
                                    <span class="text-[10px] font-normal text-gray-400">/
                                        {{ $detail['pct_disponible'] }}%</span>
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-neutral-700 overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500 transition-all"
                                    style="width: {{ $detail['pct_disponible'] }}%"></div>
                            </div>
                        </div>
                        {{-- Prestado --}}
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-neutral-300">En
                                        préstamo</span>
                                </div>
                                <span
                                    class="text-sm font-black text-amber-600 dark:text-amber-400">{{ $detail['stock_prestado'] }}
                                    <span class="text-[10px] font-normal text-gray-400">/
                                        {{ $detail['pct_prestado'] }}%</span>
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-neutral-700 overflow-hidden">
                                <div class="h-full rounded-full bg-amber-500 transition-all"
                                    style="width: {{ $detail['pct_prestado'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Datos adicionales --}}
                <div
                    class="rounded-xl border border-gray-100 dark:border-neutral-800 divide-y divide-gray-100 dark:divide-neutral-800 overflow-hidden text-sm">
                    @if ($detail['unidad'])
                        <div class="flex px-3 py-2">
                            <span
                                class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Unidad</span>
                            <span
                                class="text-gray-800 dark:text-neutral-200 font-medium">{{ $detail['unidad'] }}</span>
                        </div>
                    @endif
                    <div class="flex px-3 py-2">
                        <span class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">P.
                            Unitario</span>
                        <span class="text-gray-800 dark:text-neutral-200 font-semibold tabular-nums">Bs.
                            {{ $detail['precio_unitario'] }}</span>
                    </div>
                    <div class="flex px-3 py-2">
                        <span class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Inversión
                            Total</span>
                        <span class="text-gray-800 dark:text-neutral-200 font-semibold tabular-nums">Bs.
                            {{ $detail['precio_total'] }}</span>
                    </div>
                    @if ($detail['descripcion'])
                        <div class="px-3 py-2">
                            <span
                                class="block text-xs text-gray-400 dark:text-neutral-500 font-medium mb-1">Descripción</span>
                            <p class="text-gray-700 dark:text-neutral-300 text-xs leading-relaxed">
                                {{ $detail['descripcion'] }}</p>
                        </div>
                    @endif
                    <div class="flex px-3 py-2 bg-gray-50/50 dark:bg-neutral-900/20">
                        <span
                            class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Registro</span>
                        <span class="text-gray-500 dark:text-neutral-500 text-xs">{{ $detail['created_at'] }}</span>
                    </div>
                    <div class="flex px-3 py-2 bg-gray-50/50 dark:bg-neutral-900/20">
                        <span class="w-36 text-xs text-gray-400 dark:text-neutral-500 font-medium shrink-0">Últ.
                            modificación</span>
                        <span class="text-gray-500 dark:text-neutral-500 text-xs">{{ $detail['updated_at'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Historial de Bajas --}}
            @if (isset($detail['bajas']) && count($detail['bajas']) > 0)
                <div class="mt-4">
                    <div class="text-[10px] uppercase font-bold text-gray-500 mb-2 px-1">Historial de Bajas</div>
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
                                    <div class="mb-2 pl-3.5">
                                        <img src="{{ Storage::url($baja['evidencia']) }}"
                                            class="w-16 h-12 object-cover rounded shadow-sm border border-red-200 dark:border-red-800 cursor-pointer"
                                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($baja['evidencia']) }}' }))">
                                    </div>
                                @endif
                                @if ($baja['observaciones'])
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 pl-3.5">
                                        {{ $baja['observaciones'] }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-1 pl-3.5 italic">Por: {{ $baja['usuario'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @endif

        @slot('footer')
            <div class="flex justify-end gap-2">
                @can('herramientas.update')
                    <button type="button" wire:click="openEditFromDetail({{ $detail['id'] ?? 0 }})"
                        class="px-4 py-2 rounded-lg cursor-pointer bg-amber-600 text-white hover:bg-amber-700 transition text-sm font-bold">
                        Editar
                    </button>
                @endcan
                <button type="button" wire:click="closeDetail"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>

