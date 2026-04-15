    {{-- TABLA --}}
    <div
        class="bg-white dark:bg-neutral-900 rounded-2xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden ">
        <div class="overflow-x-auto ">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr
                        class="bg-gray-50/50 dark:bg-neutral-800/30 text-gray-500 dark:text-neutral-400 border-b border-gray-200 dark:border-neutral-700">
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center w-12">#</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px]">Préstamo</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px]">Cliente / Obra</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center">Cant. Herr.</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px]">Salida / Vence</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center">Estado</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center">Foto Salida</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center">Foto Retorno</th>
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse($paginatedNros as $nroModel)
                        @php
                            $nro = $nroModel->nro_prestamo;
                            $prestamoItems = $prestamosAgrupados[$nro] ?? collect();
                            if ($prestamoItems->isEmpty()) {
                                continue;
                            }

                            $first = $prestamoItems->first();
                            $isVencido = $prestamoItems->contains(
                                fn($i) => $i->estado !== 'finalizado' &&
                                    $i->fecha_vencimiento &&
                                    $i->fecha_vencimiento->isPast(),
                            );

                            $totalPrestadas = $prestamoItems->sum('cantidad_prestada');
                            $totalPendientes = $prestamoItems->sum('cantidad_pendiente');
                            $estadoGlobal = $totalPendientes == 0 ? 'finalizado' : ($isVencido ? 'vencido' : 'activo');
                        @endphp
                        <tr
                            class="hover:bg-gray-50/30 dark:hover:bg-neutral-800/20 transition-colors {{ $estadoGlobal === 'vencido' ? 'bg-red-50/20' : '' }}">

                            {{-- # Num --}}
                            <td class="px-4 py-4 text-center">
                                <span class="font-mono text-[11px] text-gray-400 font-bold">
                                    {{ ($paginatedNros->currentPage() - 1) * $paginatedNros->perPage() + $loop->iteration }}
                                </span>
                            </td>

                            {{-- Nro Préstamo / Items --}}
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="font-black text-indigo-600 dark:text-indigo-400 leading-tight uppercase">{{ $nro }}</span>
                                    <span
                                        class="text-[10px] text-gray-500 mt-1 uppercase">{{ $prestamoItems->count() }}
                                        Equipo(s)</span>
                                </div>
                            </td>

                            {{-- Entidad / Proyecto --}}
                            <td class="px-4 py-4 max-w-[200px]">
                                <span
                                    class="block font-semibold text-gray-900 dark:text-neutral-100 truncate">{{ $first->entidad?->nombre ?? '—' }}</span>
                                <span
                                    class="text-[11px] text-gray-500 truncate block">{{ $first->proyecto?->nombre ?? '—' }}</span>
                                <span
                                    class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase block mt-1">
                                    Recibe:
                                    {{ $first->agente?->nombre ?? ($first->receptor_manual ?: 'No especificado') }}
                                </span>
                            </td>

                            {{-- Cantidades (Total / Pendiente) --}}
                            <td class="px-4 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span
                                        class="inline-flex h-6 px-2.5 items-center justify-center rounded-lg bg-gray-100 dark:bg-neutral-800 font-bold text-xs ring-1 ring-inset ring-gray-200 dark:ring-neutral-700 text-gray-600 dark:text-neutral-400">
                                        {{ $totalPrestadas }} Total
                                    </span>
                                    @if ($totalPendientes > 0)
                                        <span
                                            class="text-[10px] font-bold text-amber-600 dark:text-amber-500 mt-1 uppercase">{{ $totalPendientes }}
                                            Pend.</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Fechas --}}
                            <td class="px-4 py-4 text-[12px]">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-gray-500">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $first->fecha_prestamo->format('d/m/Y') }}
                                    </div>
                                    <div
                                        class="flex items-center gap-1.5 {{ $estadoGlobal === 'vencido' ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $first->fecha_vencimiento ? $first->fecha_vencimiento->format('d/m/Y') : 'Abierto' }}
                                    </div>
                                </div>
                            </td>

                            {{-- Estado Global --}}
                            <td class="px-4 py-4 text-center">
                                @if ($estadoGlobal === 'finalizado')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 text-[10px] font-black uppercase ring-1 ring-emerald-200">Devuelto</span>
                                @elseif($estadoGlobal === 'vencido')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 text-[10px] font-black uppercase ring-1 ring-red-200 animate-pulse">Vencido</span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400 text-[10px] font-black uppercase ring-1 ring-blue-200">En
                                        Obra</span>
                                @endif
                            </td>

                            {{-- Ver Foto Global del Préstamo (siempre se guarda en el primer ítem, o todos tienen la misma ruta) --}}
                            <td class="px-4 py-4 text-center">
                                @if ($first->fotos_salida && count($first->fotos_salida) > 0)
                                    <button
                                        @click="$dispatch('open-viewer', { title: '{{ $nro }}', photos: {{ json_encode(collect($first->fotos_salida)->map(fn($f) => Storage::url($f))->toArray()) }} })"
                                        class="mx-auto size-8 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 transition flex items-center justify-center cursor-pointer shadow-sm"
                                        title="Ver foto(s)">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                @else
                                    <div class="mx-auto size-8 rounded-lg border border-gray-100 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900 text-gray-300 dark:text-neutral-700 flex items-center justify-center cursor-not-allowed"
                                        title="Sin foto">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            @php
                                // Recopilar todas las fotos de retorno de las devoluciones
                                $fotosRetorno = $prestamoItems
                                    ->flatMap(function ($item) {
                                        return $item->devoluciones->flatMap(fn($d) => $d->fotos_entrada ?? []);
                                    })
                                    ->filter()
                                    ->values()
                                    ->all();
                            @endphp

                            {{-- Foto Retorno --}}
                            <td class="px-4 py-4 text-center">
                                @if (count($fotosRetorno) > 0)
                                    <button
                                        @click="$dispatch('open-viewer', { title: '{{ $nro }} - Retorno', photos: {{ json_encode(collect($fotosRetorno)->map(fn($f) => Storage::url($f))->toArray()) }} })"
                                        class="mx-auto size-8 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-500 transition flex items-center justify-center cursor-pointer shadow-sm"
                                        title="Ver foto(s) de retorno">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                @else
                                    <div class="mx-auto size-8 rounded-lg border border-gray-100 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900 text-gray-300 dark:text-neutral-700 flex items-center justify-center cursor-not-allowed"
                                        title="Sin foto de retorno">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Ver detalle --}}
                                    <button title="Ver detalle del préstamo"
                                        wire:click="openVer('{{ $nro }}')"
                                        class="cursor-pointer size-8 rounded-lg border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition flex items-center justify-center shadow-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <button title="Exportar Movimiento a PDF"
                                        wire:click="exportPdf('{{ $nro }}')"
                                        class="cursor-pointer size-8 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-500 hover:text-red-500 hover:border-red-200 transition flex items-center justify-center shadow-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </button>

                                    @if ($totalPendientes > 0)
                                        <button wire:click="openDevolucion('{{ $nro }}')"
                                            title="Registrar devolución"
                                            class="cursor-pointer size-8 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center justify-center shadow-md shadow-indigo-500/20">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                        </button>

                                        <button wire:click="openBaja('{{ $nro }}')"
                                            title="Dar de baja (perdido / destruido)"
                                            class="cursor-pointer size-8 rounded-lg border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 hover:bg-red-600 hover:text-white hover:border-red-600 dark:hover:bg-red-600 dark:hover:border-red-600 transition flex items-center justify-center shadow-sm">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div
                                        class="size-16 rounded-full bg-gray-50 dark:bg-neutral-800 flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-neutral-700">
                                        <svg class="size-8 text-gray-300" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-400 text-sm font-medium">No se encontraron movimientos con
                                        estos filtros.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-neutral-800 bg-gray-50/30 dark:bg-neutral-900">
            {{ $paginatedNros->links() }}
        </div>
    </div>
