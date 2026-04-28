    <div
        class="bg-white dark:bg-neutral-900 rounded-2xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden ">

        {{-- ===================== CARDS MOBILE ===================== --}}
        <div class="md:hidden space-y-3 p-3 bg-gray-50/30 dark:bg-neutral-800/10">
            @forelse($prestamosCalculados as $nro => $datos)
                <div
                    class="rounded-xl border {{ $datos->estadoGlobal === 'vencido' ? 'border-red-200 dark:border-red-900/40 bg-red-50/30 dark:bg-red-900/5' : 'border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/60' }} shadow-sm overflow-hidden flex flex-col">

                    {{-- Cabecera Card (Compacta) --}}
                    <div
                        class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-neutral-800/60">
                        <div class="flex flex-col">
                            <span
                                class="font-black text-indigo-600 dark:text-indigo-400 leading-none uppercase text-[12px]">{{ $nro }}</span>
                            <span class="text-[9px] text-gray-400 mt-0.5 uppercase font-bold">{{ $datos->count }}
                                Equipo(s)</span>
                        </div>
                        <div>
                            @if ($datos->estadoGlobal === 'finalizado')
                                <span
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Devuelto</span>
                            @elseif($datos->estadoGlobal === 'vencido')
                                <span
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 animate-pulse">Vencido</span>
                            @else
                                <span
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400">En
                                    Obra</span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Central --}}
                    <div class="p-3">
                        <div class="mb-2">
                            <span
                                class="block font-bold text-gray-900 dark:text-neutral-100 truncate text-[13px] leading-tight">{{ $datos->first->entidad?->nombre ?? '—' }}</span>
                            <span
                                class="text-[11px] text-gray-500 truncate block">{{ $datos->first->proyecto?->nombre ?? '—' }}</span>
                            <div
                                class="flex items-center gap-1 mt-1 text-[10px] text-indigo-600 dark:text-indigo-400 font-medium">
                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ $datos->first->agente?->nombre ?? ($datos->first->receptor_manual ?: 'No especificado') }}</span>
                            </div>
                        </div>

                        {{-- Fechas (Horizontal Compacta) --}}
                        <div
                            class="flex items-center justify-between gap-2 text-[10px] py-1.5 px-2 bg-gray-50 dark:bg-neutral-800/40 rounded-lg border border-gray-100 dark:border-neutral-800 mb-3 text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <svg class="size-3 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-semibold">{{ $datos->first->fecha_prestamo->format('d/m/Y') }}</span>
                            </div>
                            <div class="w-px h-3 bg-gray-200 dark:bg-neutral-700"></div>
                            <div
                                class="flex items-center gap-1.5 {{ $datos->estadoGlobal === 'vencido' ? 'text-red-600' : '' }}">
                                <svg class="size-3 {{ $datos->estadoGlobal === 'vencido' ? 'text-red-400' : 'text-gray-400' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span
                                    class="font-semibold">{{ $datos->first->fecha_vencimiento ? $datos->first->fecha_vencimiento->format('d/m/Y') : 'Venc. Abierto' }}</span>
                            </div>
                        </div>

                        {{-- Progreso Compacto --}}
                        <div class="space-y-1.5">
                            <div
                                class="flex items-center justify-between text-[10px] uppercase font-bold tracking-wider">
                                <span class="text-gray-400">Progreso Devolución</span>
                                <span class="text-gray-900 dark:text-gray-200">{{ $datos->totalDevueltas }} /
                                    {{ $datos->totalPrestadas }}</span>
                            </div>
                            <div
                                class="h-1.5 w-full bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden flex shadow-inner">
                                <div class="h-full bg-emerald-500 transition-all duration-500"
                                    style="width: {{ $datos->pctDevuelto }}%"></div>
                                <div class="h-full bg-amber-500 transition-all duration-500"
                                    style="width: {{ $datos->pctPendiente }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Acciones Mobile --}}
                    <div class="grid grid-cols-4 gap-1.5 px-3 pb-3">
                        @can('prestamos.view')
                            <button wire:click="openVer('{{ $nro }}')" title="Ver"
                                class="flex items-center justify-center p-2 rounded-lg border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 hover:bg-indigo-600 hover:text-white transition cursor-pointer">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        @endcan
                        @can('prestamos.export_pdf')
                            <button wire:click="exportPdf('{{ $nro }}')" title="PDF"
                                class="flex items-center justify-center p-2 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-500 hover:text-red-500 transition cursor-pointer">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>
                        @endcan
                        @if ($datos->totalPendientes > 0)
                            @can('prestamos.devolucion')
                                <button wire:click="openDevolucion('{{ $nro }}')" title="Retorno"
                                    wire:loading.attr="disabled"
                                    class="flex items-center justify-center p-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer shadow-sm disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="openDevolucion('{{ $nro }}')"
                                        class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    <svg wire:loading wire:target="openDevolucion('{{ $nro }}')"
                                        class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </button>
                            @endcan
                            @can('prestamos.baja')
                                <button wire:click="openBaja('{{ $nro }}')" title="Baja"
                                    wire:loading.attr="disabled"
                                    class="flex items-center justify-center p-2 rounded-lg border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-600 hover:text-white transition cursor-pointer">
                                    <svg wire:loading.remove wire:target="openBaja('{{ $nro }}')" class="size-4"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <svg wire:loading wire:target="openBaja('{{ $nro }}')"
                                        class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
            @empty
                <div
                    class="rounded-xl border border-gray-200 dark:border-neutral-800 p-8 text-center text-sm text-gray-400 bg-white dark:bg-neutral-900">
                    Sin resultados.
                </div>
            @endforelse
        </div>

        {{-- ===================== TABLA DESKTOP ===================== --}}
        <div class="hidden md:block overflow-x-auto ">
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
                    @forelse($prestamosCalculados as $nro => $datos)
                        <tr
                            class="hover:bg-gray-50/30 dark:hover:bg-neutral-800/20 transition-colors {{ $datos->estadoGlobal === 'vencido' ? 'bg-red-50/20' : '' }}">

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
                                    <span class="text-[10px] text-gray-500 mt-1 uppercase">{{ $datos->count }}
                                        Equipo(s)</span>
                                </div>
                            </td>

                            {{-- Entidad / Proyecto --}}
                            <td class="px-4 py-4 max-w-[200px]">
                                <span
                                    class="block font-semibold text-gray-900 dark:text-neutral-100 truncate">{{ $datos->first->entidad?->nombre ?? '—' }}</span>
                                <span
                                    class="text-[11px] text-gray-500 truncate block">{{ $datos->first->proyecto?->nombre ?? '—' }}</span>
                                <span
                                    class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase block mt-1">
                                    Recibe:
                                    {{ $datos->first->agente?->nombre ?? ($datos->first->receptor_manual ?: 'No especificado') }}
                                </span>
                            </td>

                            {{-- Cantidades (Total / Pendiente) --}}
                            <td class="px-4 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span
                                        class="inline-flex h-6 px-2.5 items-center justify-center rounded-lg bg-gray-100 dark:bg-neutral-800 font-bold text-xs ring-1 ring-inset ring-gray-200 dark:ring-neutral-700 text-gray-600 dark:text-neutral-400">
                                        {{ $datos->totalPrestadas }} Total
                                    </span>
                                    @if ($datos->totalPendientes > 0)
                                        <span
                                            class="text-[10px] font-bold text-amber-600 dark:text-amber-500 mt-1 uppercase">{{ $datos->totalPendientes }}
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
                                        {{ $datos->first->fecha_prestamo->format('d/m/Y') }}
                                    </div>
                                    <div
                                        class="flex items-center gap-1.5 {{ $datos->estadoGlobal === 'vencido' ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $datos->first->fecha_vencimiento ? $datos->first->fecha_vencimiento->format('d/m/Y') : 'Abierto' }}
                                    </div>
                                </div>
                            </td>

                            {{-- Estado Global --}}
                            <td class="px-4 py-4 text-center">
                                @if ($datos->estadoGlobal === 'finalizado')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 text-[10px] font-black uppercase ring-1 ring-emerald-200">Devuelto</span>
                                @elseif($datos->estadoGlobal === 'vencido')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 text-[10px] font-black uppercase ring-1 ring-red-200 animate-pulse">Vencido</span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400 text-[10px] font-black uppercase ring-1 ring-blue-200">En
                                        Obra</span>
                                @endif
                            </td>

                            {{-- Ver Foto Global del Préstamo --}}
                            <td class="px-4 py-4 text-center">
                                @if ($datos->first->fotos_salida && count($datos->first->fotos_salida) > 0)
                                    <button
                                        @click="$dispatch('open-viewer', { title: '{{ $nro }}', photos: {{ json_encode(collect($datos->first->fotos_salida)->map(fn($f) => Storage::url($f))->toArray()) }} })"
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

                            {{-- Foto Retorno --}}
                            <td class="px-4 py-4 text-center">
                                @if (count($datos->fotosRetorno) > 0)
                                    <button
                                        @click="$dispatch('open-viewer', { title: '{{ $nro }} - Retorno', photos: {{ json_encode(collect($datos->fotosRetorno)->map(fn($f) => Storage::url($f))->toArray()) }} })"
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
                                <div wire:loading.class="opacity-50 grayscale pointer-events-none"
                                    class="flex items-center justify-center gap-2 {{ $anyModalOpen ? 'opacity-50 grayscale pointer-events-none' : '' }}">
                                    {{-- Ver detalle --}}
                                    @can('prestamos.view')
                                        <button title="Ver detalle del préstamo"
                                            wire:click="openVer('{{ $nro }}')" wire:loading.attr="disabled"
                                            @disabled($anyModalOpen)
                                            class="cursor-pointer size-8 rounded-lg border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition flex items-center justify-center shadow-sm disabled:!bg-gray-100 disabled:!text-gray-400 disabled:!border-gray-200 dark:disabled:!bg-neutral-800">
                                            <svg wire:loading.remove wire:target="openVer('{{ $nro }}')"
                                                class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg wire:loading wire:target="openVer('{{ $nro }}')"
                                                class="size-4 animate-spin text-indigo-600" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                        </button>
                                    @endcan

                                    @can('prestamos.export_pdf')
                                        <button title="Exportar Movimiento a PDF"
                                            wire:click="exportPdf('{{ $nro }}')" wire:loading.attr="disabled"
                                            @disabled($anyModalOpen)
                                            class="cursor-pointer size-8 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-500 hover:text-red-500 hover:border-red-200 transition flex items-center justify-center shadow-sm disabled:!bg-gray-100 disabled:!text-gray-400 disabled:!border-gray-200">
                                            <svg wire:loading.remove wire:target="exportPdf('{{ $nro }}')"
                                                class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <svg wire:loading wire:target="exportPdf('{{ $nro }}')"
                                                class="size-4 animate-spin text-red-500" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                        </button>
                                    @endcan

                                    @if ($datos->totalPendientes > 0)
                                        @can('prestamos.devolucion')
                                            <button wire:click="openDevolucion('{{ $nro }}')"
                                                title="Registrar devolución" wire:loading.attr="disabled"
                                                @disabled($anyModalOpen)
                                                class="cursor-pointer size-8 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center justify-center shadow-md shadow-indigo-500/20 disabled:!bg-gray-400 disabled:!shadow-none">
                                                <svg wire:loading.remove
                                                    wire:target="openDevolucion('{{ $nro }}')" class="size-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                                <svg wire:loading wire:target="openDevolucion('{{ $nro }}')"
                                                    class="size-4 animate-spin text-white" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </button>
                                        @endcan

                                        @can('prestamos.baja')
                                            <button wire:click="openBaja('{{ $nro }}')"
                                                title="Dar de baja (perdido / destruido)" wire:loading.attr="disabled"
                                                @disabled($anyModalOpen)
                                                class="cursor-pointer size-8 rounded-lg border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 hover:bg-red-600 hover:text-white hover:border-red-600 dark:hover:bg-red-600 dark:hover:border-red-600 transition flex items-center justify-center shadow-sm disabled:!bg-gray-100 disabled:!text-gray-400 disabled:!border-gray-200">
                                                <svg wire:loading.remove wire:target="openBaja('{{ $nro }}')"
                                                    class="size-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <svg wire:loading wire:target="openBaja('{{ $nro }}')"
                                                    class="size-4 animate-spin text-red-600" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </button>
                                        @endcan
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
