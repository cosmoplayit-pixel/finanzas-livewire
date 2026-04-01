<section class="section">
    @section('title', 'Préstamos y Devoluciones')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                Préstamos y Devoluciones
            </h1>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Gestión de equipos en obra y registro de
                retornos.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('herramientas.update')
                <button wire:click="openCreate"
                    class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-black text-white hover:bg-neutral-800 transition shadow-sm font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Préstamo
                </button>
            @endcan
        </div>
    </div>

    {{-- ALERTA VENCIDOS --}}
    @if ($countVencidos > 0)
        <div
            class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 flex items-center gap-4">
            <div
                class="size-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-600 shrink-0">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-800 dark:text-red-400">Hay {{ $countVencidos }} préstamo(s) fuera
                    de fecha</p>
                <p class="text-xs text-red-600 dark:text-red-500/80 italic">Contactar a los responsables para coordinar
                    el retorno.</p>
            </div>
        </div>
    @endif

    {{-- FILTROS --}}
    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div
            class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            <div class="py-3 px-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6 lg:col-span-8">
                        <label
                            class="block text-[11px] font-bold uppercase mb-1 text-gray-500 dark:text-neutral-400">Búsqueda</label>
                        <div class="relative">
                            <input type="search" wire:model.live.debounce.300ms="search"
                                placeholder="Herramienta, código, proyecto, entidad..."
                                class="w-full rounded-lg border px-3 py-2 pl-10 bg-white dark:bg-neutral-900 border-gray-200 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition text-sm" />
                            <svg class="absolute left-3 top-2.5 size-4 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2">
                        <label
                            class="block text-[11px] font-bold uppercase mb-1 text-gray-500 dark:text-neutral-400">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-200 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer text-sm font-medium">
                            <option value="10">10 Filas</option>
                            <option value="25">25 Filas</option>
                            <option value="50">50 Filas</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2">
                        <label
                            class="block text-[11px] font-bold uppercase mb-1 text-gray-500 dark:text-neutral-400">Más
                            Filtros</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-200 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 transition cursor-pointer text-sm font-bold shadow-sm">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Opciones
                            @php $activeFilters = count($f_estado) + ($f_proyecto_id !== 'all' ? 1 : 0) + ($f_entidad_id !== 'all' ? 1 : 0) + ($f_fecha_desde ? 1 : 0); @endphp
                            @if ($activeFilters > 0)
                                <span
                                    class="inline-flex items-center justify-center size-5 rounded-full bg-indigo-600 text-white text-[10px] font-black">{{ $activeFilters }}</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL FLOTANTE --}}
        <div x-show="openFilters" x-cloak @click.outside="openFilters = false"
            @keydown.escape.window="openFilters = false"
            class="absolute right-0 top-full mt-2 w-full sm:w-[380px] z-50 rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
            wire:ignore.self>
            <div
                class="px-5 py-4 border-b border-gray-100 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-800/50">
                <span class="font-bold text-gray-800 dark:text-neutral-100">Filtros Avanzados</span>
            </div>
            <div class="p-5 space-y-5 max-h-[70vh] overflow-y-auto">
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase text-gray-500">Estado del Préstamo</label>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach (['activo' => 'En Obra/Uso', 'finalizado' => 'Devuelto Total'] as $val => $label)
                            <label
                                class="flex items-center gap-3 p-2 rounded-lg border border-gray-100 dark:border-neutral-800 cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" wire:model.live="f_estado" value="{{ $val }}"
                                    class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase text-gray-500">Fecha de Préstamo (Rango)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model.live="f_fecha_desde"
                            class="w-full text-sm rounded-lg border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                        <input type="date" wire:model.live="f_fecha_hasta"
                            class="w-full text-sm rounded-lg border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase text-gray-500">Proyecto</label>
                    <select wire:model.live="f_proyecto_id"
                        class="w-full text-sm rounded-lg border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="all">Cualquiera</option>
                        @foreach ($proyectosFiltro as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase text-gray-500">Entidad / Cliente</label>
                    <select wire:model.live="f_entidad_id"
                        class="w-full text-sm rounded-lg border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="all">Cualquiera</option>
                        @foreach ($entidades as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-bold uppercase text-gray-500">Herramienta Específica</label>
                    <select wire:model.live="f_herramienta_id"
                        class="w-full text-sm rounded-lg border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="all">Todas</option>
                        @foreach ($herramientas as $h)
                            <option value="{{ $h->id }}">{{ $h->nombre }} ({{ $h->codigo }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div
                class="p-4 bg-gray-50 dark:bg-neutral-800/50 border-t border-gray-100 dark:border-neutral-800 flex justify-between items-center gap-3">
                <button wire:click="clearFilters" @click="openFilters = false"
                    class="text-xs font-bold text-red-500 hover:text-red-600 transition">Limpiar Todo</button>
                <button @click="openFilters = false"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold shadow-md shadow-indigo-500/20">Aplicar</button>
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div
        class="bg-white dark:bg-neutral-900 rounded-2xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden min-h-[400px]">
        <div class="overflow-x-auto min-h-[400px]">
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
                        <th class="px-4 py-4 font-bold uppercase text-[11px] text-center">Foto</th>
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
                                        class="mx-auto size-8 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 transition flex items-center justify-center cursor-pointer shadow-sm"
                                        title="Ver foto">
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

                            {{-- Acciones --}}
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button title="Exportar Movimiento a PDF"
                                        class="cursor-pointer size-8 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-500 hover:text-red-500 hover:border-red-200 transition flex items-center justify-center shadow-sm">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </button>

                                    @if ($totalPendientes > 0)
                                        {{-- El botón Devolver enviará el IDs o abrirá un modal especial --}}
                                        <button wire:click="openDevolucion({{ $first->id }})"
                                            title="Recibir devolución"
                                            class="cursor-pointer size-8 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center justify-center shadow-md shadow-indigo-500/20">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-20 text-center">
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

    {{-- ===================== MODAL NUEVO PRÉSTAMO ===================== --}}
    <x-ui.modal wire:key="prestamo-create-{{ $openModalPrestamo ? 'open' : 'closed' }}" model="openModalPrestamo"
        title="Nuevo Registro de Préstamo" maxWidth="sm:max-w-2xl md:max-w-5xl"
        onClose="$set('openModalPrestamo', false)">

        <div class="space-y-5">

            {{-- SECCIÓN 1: DESTINO DEL PRÉSTAMO --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        1</div>
                    <span class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Destino
                        del Préstamo</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

                    {{-- Entidad con autocomplete --}}
                    <div class="col-span-2 lg:col-span-2" x-data="{
                        query: '',
                        open: false,
                        entidades: @js($entidades->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()),
                        get suggestions() {
                            if (!this.query.trim()) return this.entidades.slice(0, 8);
                            const q = this.query.toUpperCase();
                            return this.entidades.filter(e => e.nombre.toUpperCase().includes(q)).slice(0, 8);
                        }
                    }" x-init="$watch('$wire.entidad_id', v => {
                        if (!v) { query = ''; return; }
                        const found = entidades.find(e => e.id == v);
                        if (found && query !== found.nombre) query = found.nombre;
                    })">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Entidad /
                            Cliente <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" x-model="query" @focus="open = true"
                                @blur="setTimeout(() => open = false, 200)"
                                @input="open = true; $wire.set('entidad_id', '')"
                                placeholder="Buscar entidad o cliente..."
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm">
                            <div x-show="open && suggestions.length > 0" x-cloak
                                class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-52 overflow-y-auto">
                                <template x-for="item in suggestions" :key="item.id">
                                    <div @mousedown="query = item.nombre; $wire.set('entidad_id', item.id); open = false"
                                        class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-sm border-b dark:border-neutral-800 last:border-0 transition">
                                        <span class="font-semibold text-gray-900 dark:text-white"
                                            x-text="item.nombre"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('entidad_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Proyecto (Select2-style Autocomplete) --}}
                    <div class="col-span-2 lg:col-span-2" wire:key="proyecto-search-{{ $entidad_id }}"
                        x-data="{
                            query: '',
                            open: false,
                            proyectos: @js($this->proyectosFiltrados->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values()),
                            get suggestions() {
                                if (!this.query.trim()) return this.proyectos.slice(0, 8);
                                const q = this.query.toUpperCase();
                                return this.proyectos.filter(p => p.nombre.toUpperCase().includes(q)).slice(0, 8);
                            }
                        }" x-init="$watch('$wire.proyecto_id', v => {
                            if (!v) { query = ''; return; }
                            const found = proyectos.find(p => p.id == v);
                            if (found && query !== found.nombre) query = found.nombre;
                        });
                        if ($wire.proyecto_id) {
                            const found = proyectos.find(p => p.id == $wire.proyecto_id);
                            if (found) query = found.nombre;
                        }">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Proyecto
                            Destino <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" x-model="query" @focus="open = true"
                                @blur="setTimeout(() => open = false, 200)"
                                @input="open = true; $wire.set('proyecto_id', '')"
                                placeholder="{{ $entidad_id ? 'Buscar proyecto...' : 'Primero seleccione entidad' }}"
                                {{ !$entidad_id ? 'disabled' : '' }}
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm disabled:opacity-50 disabled:bg-gray-50">

                            <div x-show="open && suggestions.length > 0" x-cloak
                                class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-52 overflow-y-auto">
                                <template x-for="item in suggestions" :key="item.id">
                                    <div @mousedown="query = item.nombre; $wire.set('proyecto_id', item.id); open = false"
                                        class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-sm border-b dark:border-neutral-800 last:border-0 transition">
                                        <span class="font-semibold text-gray-900 dark:text-white"
                                            x-text="item.nombre"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('proyecto_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                        @if ($entidad_id && $this->proyectosFiltrados->isEmpty())
                            <p class="text-amber-600 text-[10px] mt-1 italic">Esta entidad no tiene proyectos activos.
                            </p>
                        @endif
                    </div>

                    {{-- Fecha Salida --}}
                    <div class="col-span-1 lg:col-span-2">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Fecha de
                            Salida <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="fecha_prestamo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm" />
                    </div>

                    {{-- Retorno Estimado --}}
                    <div class="col-span-1 lg:col-span-2">
                        <label class="block text-sm mb-1 font-bold text-indigo-600 dark:text-indigo-400">Retorno
                            Estimado</label>
                        <input type="date" wire:model="fecha_vencimiento"
                            class="w-full rounded-lg border px-3 py-2 bg-indigo-50/30 dark:bg-indigo-900/10 border-indigo-200 dark:border-indigo-800 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm" />
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: AGREGAR HERRAMIENTAS --}}
            <div class="border-t border-gray-100 dark:border-neutral-800 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        2</div>
                    <span
                        class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Herramientas
                        a Prestar</span>
                </div>

                {{-- Buscador de herramienta (select2-style) --}}
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end" x-data="{
                    query: '',
                    open: false,
                    selectedName: '',
                    herramientas: @js(isset($herramientas) ? $herramientas->map(fn($h) => ['id' => $h->id, 'nombre' => $h->nombre, 'codigo' => $h->codigo, 'disponible' => $h->stock_disponible, 'imagen' => $h->imagen])->values() : []),
                    get suggestions() {
                        if (!this.query.trim()) return this.herramientas.filter(h => h.disponible > 0).slice(0, 8);
                        const q = this.query.toUpperCase();
                        return this.herramientas.filter(h =>
                            h.disponible > 0 && (h.nombre.toUpperCase().includes(q) || (h.codigo && h.codigo.toUpperCase().includes(q)))
                        ).slice(0, 10);
                    },
                    get selectedHerramienta() {
                        const id = $wire.item_herramienta_id;
                        return id ? this.herramientas.find(h => h.id == id) : null;
                    }
                }"
                    x-init="$watch('$wire.item_herramienta_id', v => {
                        if (!v) { query = ''; return; }
                        const found = herramientas.find(h => h.id == v);
                        if (found) query = found.nombre + (found.codigo ? ' (' + found.codigo + ')' : '');
                    })">

                    {{-- Input búsqueda herramienta --}}
                    <div class="sm:col-span-6 relative">
                        <label
                            class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Herramienta</label>
                        <input type="text" x-model="query" @focus="open = true"
                            @blur="setTimeout(() => open = false, 200)"
                            @input="open = true; $wire.set('item_herramienta_id', '')"
                            placeholder="Buscar por nombre o código..."
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">

                        {{-- Dropdown con preview --}}
                        <div x-show="open && suggestions.length > 0" x-cloak
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-2xl max-h-64 overflow-y-auto">
                            <template x-for="h in suggestions" :key="h.id">
                                <div @mousedown="$wire.set('item_herramienta_id', h.id); open = false"
                                    class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800 transition border-b dark:border-neutral-800 last:border-0">
                                    <div
                                        class="shrink-0 size-9 rounded-lg bg-gray-100 dark:bg-neutral-800 overflow-hidden flex items-center justify-center">
                                        <template x-if="h.imagen">
                                            <img :src="'/storage/' + h.imagen" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!h.imagen">
                                            <svg class="size-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white text-[13px] truncate"
                                            x-text="h.nombre"></div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-[10px] text-indigo-500 uppercase"
                                                x-text="h.codigo || '—'"></span>
                                            <span class="text-[10px] text-gray-400">·</span>
                                            <span class="text-[10px]"
                                                :class="h.disponible > 0 ? 'text-emerald-600 font-bold' :
                                                    'text-red-500 font-bold'"
                                                x-text="'Disp: ' + h.disponible"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Preview imagen de la herramienta seleccionada --}}
                        <template x-if="selectedHerramienta && selectedHerramienta.imagen">
                            <div
                                class="mt-2 flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-neutral-800 border dark:border-neutral-700">
                                <img :src="'/storage/' + selectedHerramienta.imagen"
                                    class="size-12 rounded-lg object-cover border border-gray-200 dark:border-neutral-700">
                                <div>
                                    <div class="text-xs font-bold text-gray-900 dark:text-white"
                                        x-text="selectedHerramienta.nombre"></div>
                                    <div class="text-[10px] text-emerald-600 font-bold"
                                        x-text="'Disponible: ' + selectedHerramienta.disponible"></div>
                                </div>
                            </div>
                        </template>
                        @error('item_herramienta_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div class="sm:col-span-3">
                        <label
                            class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Cantidad</label>
                        <input type="number" wire:model.live="item_cantidad" min="1"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-center font-bold text-sm">
                        @error('item_cantidad')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botón agregar --}}
                    <div class="sm:col-span-3">
                        <label class="block text-sm mb-1 opacity-0">Agregar</label>
                        <button type="button" wire:click="addItem"
                            class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition text-sm font-bold cursor-pointer flex items-center justify-center gap-2 shadow-md shadow-indigo-500/20">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Agregar
                        </button>
                    </div>
                </div>

                {{-- Error global items --}}
                @error('items')
                    <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                @enderror

                {{-- TABLA DE ÍTEMS --}}
                @if (count($items) > 0)
                    <div wire:key="items-table-container"
                        class="mt-4 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                        <div
                            class="px-4 py-2 bg-gray-50 dark:bg-neutral-800/50 border-b border-gray-200 dark:border-neutral-700 flex justify-between items-center">
                            <span
                                class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-neutral-400">Herramientas
                                a prestar</span>
                            <span
                                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">{{ count($items) }}
                                ítem(s)</span>
                        </div>
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50/50 dark:bg-neutral-900/50 text-gray-500 dark:text-neutral-400 text-[11px] uppercase">
                                <tr>
                                    <th class="px-4 py-2 w-14 font-bold text-center">
                                        <svg class="size-4 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </th>
                                    <th class="px-4 py-2 font-bold text-left">Herramienta</th>
                                    <th class="px-4 py-2 font-bold text-center">Disp.</th>
                                    <th class="px-4 py-2 font-bold text-center">Cant.</th>
                                    <th class="px-4 py-2 font-bold text-center">Nuevo Disp.</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @foreach ($items as $idx => $it)
                                    <tr wire:key="herramienta-item-{{ $idx }}-{{ $it['herramienta_id'] ?? 'nuevo' }}"
                                        class="hover:bg-gray-50/50 dark:hover:bg-neutral-800/20">
                                        <td class="px-4 py-2.5">
                                            @if (isset($it['imagen']) && $it['imagen'])
                                                <img src="{{ Storage::url($it['imagen']) }}"
                                                    alt="{{ $it['nombre'] }}"
                                                    class="size-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-neutral-700 bg-white">
                                            @else
                                                <div
                                                    class="size-10 rounded-lg bg-gray-100 dark:bg-neutral-800 flex items-center justify-center ring-1 ring-gray-200 dark:ring-neutral-700">
                                                    <svg class="size-6 text-gray-400 dark:text-neutral-500"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <div class="font-semibold text-gray-900 dark:text-neutral-100 text-[13px]">

                                                {{ $it['nombre'] }}</div>
                                            <div class="font-mono text-[10px] text-indigo-500 uppercase">
                                                {{ $it['codigo'] }}</div>
                                        </td>
                                        <td
                                            class="px-4 py-2.5 text-center text-gray-500 dark:text-neutral-400 font-medium">
                                            {{ $it['disponible'] }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 font-black text-sm">{{ $it['cantidad'] }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @php $nuevo = $it['disponible'] - $it['cantidad']; @endphp
                                            <span
                                                class="font-black text-sm {{ $nuevo < 0 ? 'text-red-600 animate-pulse' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $nuevo }}</span>
                                        </td>
                                        <td class="px-2 py-2.5 text-center">
                                            <button type="button" wire:click="removeItem({{ $idx }})"
                                                class="size-7 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition flex items-center justify-center cursor-pointer">
                                                <svg class="size-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div
                        class="mt-3 rounded-xl border-2 border-dashed border-gray-200 dark:border-neutral-700 p-6 text-center">
                        <p class="text-[13px] text-gray-400 dark:text-neutral-500">Busque y agregue herramientas al
                            préstamo</p>
                    </div>
                @endif
            </div>

            {{-- SECCIÓN 3: EVIDENCIA FOTOGRÁFICA --}}
            <div class="border-t border-gray-100 dark:border-neutral-800 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        3</div>
                    <span
                        class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Evidencia
                        de Salida</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <x-ui.scanner model="foto_salida" label="Foto del estado al salir" :file="$foto_salida"
                            accept=".jpg,.jpeg,.png" />
                    </div>
                </div>
            </div>
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
                <button type="button" @click="openModalPrestamo = false"
                    class="w-full sm:w-auto px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="savePrestamo" wire:loading.attr="disabled"
                    class="px-8 py-2 rounded-lg cursor-pointer bg-black text-white hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-neutral-900/10 tracking-wide flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="savePrestamo">Confirmar Salida
                        ({{ count($items) ?? 0 }})</span>
                    <span wire:loading wire:target="savePrestamo">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- ===================== MODAL DEVOLUCIÓN ===================== --}}
    <x-ui.modal wire:key="prestamo-return-{{ $openModalDevolucion ? 'open' : 'closed' }}" model="openModalDevolucion"
        title="Recepción de Herramientas" maxWidth="sm:max-w-xl md:max-w-2xl"
        onClose="$set('openModalDevolucion', false)">

        <div class="space-y-4">
            <div
                class="p-3 bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/40 rounded-xl flex items-start gap-3">
                <div
                    class="shrink-0 p-1.5 bg-emerald-100 dark:bg-emerald-800/50 rounded-lg text-emerald-600 dark:text-emerald-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-400 uppercase tracking-tight">
                        Registro de Retorno</h4>
                    <p class="text-xs text-emerald-700/70 dark:text-emerald-500/70 leading-normal">Indique la cantidad
                        exacta que ingresa a bodega y el estado físico del equipo.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-gray-700 dark:text-neutral-300 font-medium">Cantidad a
                        Devolver <span class="text-red-500">*</span></label>
                    <input type="number" wire:model.live="cantidad_a_devolver"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 font-bold" />
                    @error('cantidad_a_devolver')
                        <span class="text-[10px] text-red-500 font-bold mt-1 block italic">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-700 dark:text-neutral-300 font-medium">Estado
                        Físico</label>
                    <select wire:model="estado_fisico_devolucion"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-bold cursor-pointer">
                        <option value="bueno">✅ Óptimo Estado</option>
                        <option value="regular">⚠️ Desgaste Regular</option>
                        <option value="malo">❌ Dañado / Requiere Rep.</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm mb-1 text-gray-700 dark:text-neutral-300 font-medium">Observaciones de
                        Recepción</label>
                    <textarea wire:model="observaciones_devolucion" rows="2"
                        placeholder="Describa faltantes o daños si los hubiera..."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm"></textarea>
                </div>
                <div class="col-span-2">
                    <x-ui.scanner model="foto_entrada" label="Foto del estado al volver" :file="$foto_entrada"
                        accept=".jpg,.jpeg,.png" />
                </div>
            </div>
        </div>

        @slot('footer')
            <div class="w-full flex justify-end gap-3">
                <button type="button" @click="openModalDevolucion = false"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-500 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cerrar
                </button>
                <button type="button" wire:click="saveDevolucion" wire:loading.attr="disabled"
                    class="px-8 py-2 rounded-lg cursor-pointer bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-emerald-600/10 uppercase tracking-wide">
                    <span wire:loading.remove wire:target="saveDevolucion">Registrar Entrada</span>
                    <span wire:loading wire:target="saveDevolucion">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>

</section>
