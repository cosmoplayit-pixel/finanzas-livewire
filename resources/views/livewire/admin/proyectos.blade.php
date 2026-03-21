@section('title', 'Proyectos')

<div>

    {{-- HEADER (RESPONSIVE) --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                Proyectos
            </h1>
            <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">
                Administración de proyectos vinculados a empresas y clientes.
            </p>
        </div>

        <div class="flex gap-2">
            @can('proyectos.create')
                <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span wire:loading.remove wire:target="openCreate">Nuevo Proyecto</span>
                    <span wire:loading wire:target="openCreate">Abriendo…</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms
            class="p-3 mb-4  rounded bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
            class="p-3 mb-4 rounded bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div
            class="rounded-xl border border-gray-200 bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            {{-- MOBILE (<= md): FILTROS COLAPSABLES --}}
            <div class="md:hidden" x-data="{ openMobile: false }">
                <div class="px-4 h-11 flex items-center justify-between">
                    <div class="text-[13px] font-semibold text-gray-700 dark:text-neutral-200">
                        Filtros
                    </div>
                    <button type="button" @click="openMobile = !openMobile"
                        class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg text-[13px] font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800/60 transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 4h-7" />
                            <path d="M10 4H3" />
                            <path d="M21 12h-9" />
                            <path d="M8 12H3" />
                            <path d="M21 20h-5" />
                            <path d="M12 20H3" />
                            <path d="M14 2v4" />
                            <path d="M12 10v4" />
                            <path d="M16 18v4" />
                        </svg>
                        <span x-text="openMobile ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </div>

                <div class="mt-2 space-y-3 px-4 pb-3 text-[13px]" x-show="openMobile" x-collapse x-cloak>
                    <div>
                        <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Búsqueda</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar Nombre o Código..." autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Mostrar</label>
                            <select wire:model.live="perPage"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-transparent select-none text-[13px]">&nbsp;</label>
                            <button type="button" @click.stop="openFilters = !openFilters"
                                class="w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Opciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DESKTOP (>= md): Layout extendido --}}
            <div class="hidden md:block p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6 lg:col-span-8">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                        <input type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar Nombre o Código..." autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    </div>

                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Filtros</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="w-full cursor-pointer flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Opciones
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL FLOTANTE --}}
        <div x-show="openFilters" x-cloak @click.outside="openFilters = false"
            @keydown.escape.window="openFilters = false"
            class="absolute right-0 top-full mt-2 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
            wire:ignore.self wire:key="proyectos-panel-filtros">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
            </div>

            <div class="px-4 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Cliente</label>
                    <select wire:model.live="entidadFilter"
                        class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px]">
                        <option value="all">Todas</option>
                        @foreach ($entidades as $en)
                            <option value="{{ $en->id }}">{{ \Illuminate\Support\Str::limit($en->nombre, 30) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Estado</label>
                    <select wire:model.live="status"
                        class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px]">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- MOBILE: CARDS (md:hidden) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($proyectos as $p)
            <div x-data="{ showFullProject: false, showFullEntidad: false }"
                class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="min-w-0 flex-1">
                            @php
                                $nombreMob = $p->nombre ?? '—';
                                $isLongMob = mb_strlen($nombreMob) > 45;
                            @endphp
                            @if ($isLongMob)
                                <div x-show="!showFullProject" class="min-w-0 flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate whitespace-nowrap"
                                        title="{{ $nombreMob }}">
                                        {{ $nombreMob }}
                                    </span>
                                    <button type="button"
                                        class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullProject = true">
                                        Ver más
                                    </button>
                                </div>
                                <div x-show="showFullProject" x-cloak class="min-w-0 leading-snug">
                                    <span class="break-words">
                                        {{ $nombreMob }}
                                    </span>
                                    <button type="button"
                                        class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullProject = false">
                                        Ver menos
                                    </button>
                                </div>
                            @else
                                <div class="">{{ $nombreMob }}</div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                            @php
                                $nombreEntMob = $p->entidad?->nombre ?? '—';
                                $isLongEntMob = mb_strlen($nombreEntMob) > 35;
                            @endphp
                            @if ($isLongEntMob)
                                <div x-show="!showFullEntidad" class="flex items-center gap-2">
                                    <span class="truncate flex-1"
                                        title="{{ $nombreEntMob }}">{{ $nombreEntMob }}</span>
                                    <button type="button"
                                        class="shrink-0 text-[10px] font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullEntidad = true">
                                        Ver más
                                    </button>
                                </div>
                                <div x-show="showFullEntidad" x-cloak class="leading-tight">
                                    <span class="break-words">{{ $nombreEntMob }}</span>
                                    <button type="button"
                                        class="inline-flex align-baseline text-[10px] font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullEntidad = false">
                                        Ver menos
                                    </button>
                                </div>
                            @else
                                <div class="truncate">{{ $nombreEntMob }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if ($p->active)
                            <span
                                class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                Activo
                            </span>
                        @else
                            <span
                                class="px-2 py-1 rounded text-xs bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200">
                                Inactivo
                            </span>
                        @endif

                        <div class="mt-1 text-center">
                            @if (($p->tipo ?? 'Propuesta') === 'Adjudicación')
                                <span
                                    class="px-2 py-1 rounded text-[10px] uppercase font-bold bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                                    Adjudicación
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-[10px] uppercase font-bold bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-200">
                                    Propuesta
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500 dark:text-neutral-400">ID</span>
                        <span class="font-medium">{{ $proyectos->firstItem() + $loop->index }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Código:</span>
                        <span class="truncate">{{ $p->codigo ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Monto:</span>
                        <span>Bs {{ number_format((float) $p->monto, 2, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Retención:</span>
                        <span>{{ number_format((float) ($p->retencion ?? 0), 2, ',', '.') }}%</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Inicio:</span>
                        <span>{{ $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Fin:</span>
                        <span>{{ $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '—' }}</span>
                    </div>
                </div>
                {{-- Acciones --}}
                @canany(['proyectos.update', 'proyectos.toggle'])
                    <div class="mt-4 flex gap-2">
                        {{-- EDITAR (Livewire) --}}
                        @can('proyectos.update')
                            <button wire:click="openEdit({{ $p->id }})" wire:loading.attr="disabled"
                                wire:target="openEdit({{ $p->id }})"
                                class="w-full px-3 py-1 rounded border border-gray-300
                       cursor-pointer
                       hover:bg-gray-50
                       dark:border-neutral-700 dark:hover:bg-neutral-800
                       disabled:opacity-50 disabled:cursor-not-allowed">

                                <span wire:loading.remove wire:target="openEdit({{ $p->id }})">
                                    Editar
                                </span>

                                <span wire:loading wire:target="openEdit({{ $p->id }})">
                                    Abriendo…
                                </span>
                            </button>
                        @endcan

                        {{-- TOGGLE ACTIVO (SweetAlert + Alpine loading) --}}
                        @can('proyectos.toggle')
                            <button type="button" x-data="{ loading: false }"
                                x-on:click="
                                loading = true;
                                $dispatch('swal:toggle-active-proyecto', {
                                    id: {{ $p->id }},
                                    active: @js($p->active),
                                    name: @js($p->nombre)
                                });
                            "
                                x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                class="w-full px-3 py-1 rounded text-sm font-medium
                       cursor-pointer
                       disabled:opacity-50 disabled:cursor-not-allowed
                       {{ $p->active
                           ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                           : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">

                                <span x-show="!loading">
                                    {{ $p->active ? 'Desactivar' : 'Activar' }}
                                </span>

                                <span x-show="loading" x-cloak>
                                    Procesando…
                                </span>
                            </button>
                        @endcan
                    </div>
                @endcanany

            </div>
        @empty
            <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- TABLET + DESKTOP: TABLA --}}
    <div
        class="hidden md:block border border-gray-200 rounded-xl bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden shadow-sm mt-4">
        <table class="w-full table-fixed text-[13px] text-left">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200
                   border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left text-xs uppercase tracking-wider">
                    <th class="w-[4%] text-center p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[15%] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('entidad_id')">
                        Cliente
                        @if ($sortField === 'entidad_id')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[20%] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[12%] p-2 cursor-pointer select-none whitespace-nowrap hidden 2xl:table-cell"
                        wire:click="sortBy('codigo')">
                        CUCE – PAC – Otro
                        @if ($sortField === 'codigo')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[10%] p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('monto')">
                        Monto
                        @if ($sortField === 'monto')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[8%] p-2 cursor-pointer select-none whitespace-nowrap hidden 2xl:table-cell"
                        wire:click="sortBy('retencion')">
                        Retención
                        @if ($sortField === 'retencion')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[8%] p-2 cursor-pointer select-none whitespace-nowrap hidden 2xl:table-cell"
                        wire:click="sortBy('fecha_inicio')">
                        Inicio
                        @if ($sortField === 'fecha_inicio')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="w-[8%] p-2 cursor-pointer select-none whitespace-nowrap hidden 2xl:table-cell"
                        wire:click="sortBy('fecha_fin')">
                        Fin
                        @if ($sortField === 'fecha_fin')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    <th class="text-center w-[7%] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-3.5 h-3.5 text-gray-400 dark:text-neutral-500 mb-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>

                    @canany(['proyectos.update', 'proyectos.toggle'])
                        <th class="w-[8%] p-2 whitespace-nowrap text-center">
                            Acciones
                        </th>
                    @endcanany
                </tr>
            </thead>

            @foreach ($proyectos as $p)
                <tbody wire:key="{{ $p->id }}" x-data="{ open: false, showFullProject: false, showFullEntidad: false }"
                    class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/40 transition-colors">

                        <td class="p-1 whitespace-nowrap text-center" x-data="{ showToggle: !window.matchMedia('(min-width: 1536px)').matches }"
                            x-init="const mq = window.matchMedia('(min-width: 1536px)');
                            const handler = e => showToggle = !e.matches;
                            mq.addEventListener('change', handler);">
                            <button type="button" x-show="showToggle" x-cloak
                                class="w-6 h-6 inline-flex items-center justify-center rounded-md border border-gray-200 text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-900 dark:border-neutral-700 dark:text-neutral-400 dark:bg-neutral-900 dark:hover:text-white transition-colors cursor-pointer shadow-sm"
                                @click.stop="open = !open" :aria-expanded="open">
                                <svg x-show="!open" class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <svg x-show="open" class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="ml-1">{{ $proyectos->firstItem() + $loop->index }}</span>
                        </td>

                        <td class="p-2 min-w-0">
                            @php
                                $nombreEn = $p->entidad?->nombre ?? '—';
                                $isLongEn = mb_strlen($nombreEn) > 25; // Cliente column is narrower
                            @endphp

                            @if ($isLongEn)
                                <div x-show="!showFullEntidad" class="min-w-0 flex items-center gap-2">
                                    <span class="block truncate max-w-full" title="{{ $nombreEn }}">
                                        {{ $nombreEn }}
                                    </span>
                                    <button type="button"
                                        class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullEntidad = true">
                                        Ver más
                                    </button>
                                </div>

                                <div x-show="showFullEntidad" x-cloak class="min-w-0 leading-snug">
                                    <span class="break-words">
                                        {{ $nombreEn }}
                                    </span>
                                    <button type="button"
                                        class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullEntidad = false">
                                        Ver menos
                                    </button>
                                </div>
                            @else
                                <span class="block truncate max-w-full" title="{{ $nombreEn }}">
                                    {{ $nombreEn }}
                                </span>
                            @endif
                        </td>

                        <td class="p-2 min-w-0">
                            @php
                                $nombreProyecto = $p->nombre ?? '—';
                                $isLong = mb_strlen($nombreProyecto) > 45;
                            @endphp

                            @if ($isLong)
                                <div x-show="!showFullProject" class="min-w-0 flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate whitespace-nowrap"
                                        title="{{ $nombreProyecto }}">
                                        {{ $nombreProyecto }}
                                    </span>

                                    <button type="button"
                                        class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullProject = true">
                                        Ver más
                                    </button>
                                </div>

                                <div x-show="showFullProject" x-cloak class="min-w-0 leading-snug">
                                    <span class="break-words">
                                        {{ $nombreProyecto }}
                                    </span>

                                    <button type="button"
                                        class="inline-flex align-baseline ml-2 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                                        @click.stop="showFullProject = false">
                                        Ver menos
                                    </button>
                                </div>
                            @else
                                <div class="min-w-0">
                                    <span>{{ $nombreProyecto }}</span>
                                </div>
                            @endif
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $p->codigo ?? '-' }}">
                                {{ $p->codigo ?? '-' }}
                            </span>
                        </td>

                        <td class="p-2 whitespace-nowrap">
                            Bs {{ number_format((float) $p->monto, 2, ',', '.') }}
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            {{ number_format((float) ($p->retencion ?? 0), 2, ',', '.') }}%
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            {{ $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '-' }}
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            {{ $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '-' }}
                        </td>

                        <td class="text-center p-2 whitespace-nowrap">
                            @if ($p->active)
                                <span
                                    class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                    Activo
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-xs bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200">
                                    Inactivo
                                </span>
                            @endif

                            <div class="mt-2">
                                @if (($p->tipo ?? 'Propuesta') === 'Adjudicado')
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                                        Adjudicado
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-200">
                                        Propuesta
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Acciones --}}
                        @canany(['proyectos.update', 'proyectos.toggle'])
                            <td class="p-2 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- EDITAR (Livewire) --}}
                                    @can('proyectos.update')
                                        <button wire:click="openEdit({{ $p->id }})" wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $p->id }})" title="Editar proyecto"
                                            aria-label="Editar proyecto"
                                            class="cursor-pointer rounded p-1
                                            hover:bg-gray-100 dark:hover:bg-neutral-800
                                            disabled:opacity-50 disabled:cursor-not-allowed">

                                            {{-- Ícono normal --}}
                                            <svg wire:loading.remove wire:target="openEdit({{ $p->id }})"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>

                                            {{-- Loader --}}
                                            <svg wire:loading wire:target="openEdit({{ $p->id }})"
                                                class="size-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                            </svg>
                                        </button>
                                    @endcan

                                    {{-- TOGGLE ACTIVO (SweetAlert + Alpine loading) --}}
                                    @can('proyectos.toggle')
                                        <button type="button" x-data="{ loading: false }"
                                            x-on:click="
                                                loading = true;
                                                $dispatch('swal:toggle-active-proyecto', {
                                                    id: {{ $p->id }},
                                                    active: @js($p->active),
                                                    name: @js($p->nombre)
                                                });
                                            "
                                            x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                            title="{{ $p->active ? 'Desactivar proyecto' : 'Activar proyecto' }}"
                                            aria-label="{{ $p->active ? 'Desactivar proyecto' : 'Activar proyecto' }}"
                                            class="cursor-pointer inline-flex items-center justify-center size-8 rounded
                                            disabled:opacity-50 disabled:cursor-not-allowed
                                            {{ $p->active
                                                ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">

                                            {{-- Ícono normal --}}
                                            <span x-show="!loading">
                                                @if ($p->active)
                                                    {{-- eye-slash --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 3l18 18M10.584 10.584A2.25 2.25 0 0012 14.25 2.25 2.25 0 0014.25 12c0-.5-.167-.96-.45-1.33M9.88 5.09 A9.715 9.715 0 0112 4.5c4.478 0 8.268 2.943 9.543 7.5 a9.66 9.66 0 01-2.486 3.95M6.18 6.18 C4.634 7.436 3.55 9.135 3 12 c1.275 4.557 5.065 7.5 9.543 7.5 1.79 0 3.487-.469 4.993-1.29" />
                                                    </svg>
                                                @else
                                                    {{-- eye --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 010-.639 C3.423 7.51 7.36 4.5 12 4.5 c4.638 0 8.573 3.007 9.963 7.178 .07.207.07.431 0 .639 C20.577 16.49 16.64 19.5 12 19.5 c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                @endif
                                            </span>

                                            {{-- Loader --}}
                                            <span x-show="loading" x-cloak>
                                                <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        @endcanany

                    </tr>

                    {{-- ✅ Detalle expandible SOLO cuando NO es 2xl y con 4 columnas --}}
                    @php
                        // En md (<2xl): ID, Entidad, Nombre, Monto, Estado = 5
                        // + Acciones si el usuario tiene permisos
                        $colspan =
                            5 +
                            (auth()->user()->can('proyectos.update') || auth()->user()->can('proyectos.toggle')
                                ? 1
                                : 0);
                    @endphp

                    <tr x-show="open" x-cloak
                        class="2xl:hidden bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">
                        <td class="pl-20 py-1.5" colspan="{{ $colspan }}">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        CUCE – PAC – Otro
                                    </span>
                                    <span class="block truncate">
                                        {{ $p->codigo ?? '-' }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Retención
                                    </span>
                                    <span class="block truncate">
                                        {{ number_format((float) ($p->retencion ?? 0), 2, ',', '.') }}%
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Inicio
                                    </span>
                                    <span class="block truncate">
                                        {{ $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '-' }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Fin
                                    </span>
                                    <span class="block truncate">
                                        {{ $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endforeach

            @if ($proyectos->count() === 0)
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400" colspan="11">
                            Sin resultados.
                        </td>
                    </tr>
                </tbody>
            @endif
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $proyectos->links() }}
    </div>

    {{-- MODAL PROYECTO (create / update) --}}
    @canany(['proyectos.create', 'proyectos.update'])
        <x-ui.modal wire:key="proyectos-modal" model="openModal" :title="$proyectoId ? 'Editar Proyecto' : 'Nuevo Proyecto'" maxWidth="sm:max-w-xl md:max-w-2xl"
            onClose="closeModal">
            {{-- BODY --}}
            <div class="space-y-3">

                {{-- Tipo (OBLIGATORIO) - siempre visible --}}
                <div>
                    <label class="block text-sm mb-1">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="tipo"
                        class="cursor-pointer w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700">
                        <option value="Propuesta">Propuesta</option>
                        <option value="Adjudicado">Adjudicado</option>
                    </select>
                    @error('tipo')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Entidad (OBLIGATORIO) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="entidad_id"
                        class="cursor-pointer w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700">
                        <option value="">Seleccione...</option>
                        @foreach ($entidades as $en)
                            <option value="{{ $en->id }}" title="{{ $en->nombre }}">
                                {{ $en->sigla ? $en->sigla . ' - ' : '' }}
                                {{ \Illuminate\Support\Str::limit($en->nombre, 30) }}
                            </option>
                        @endforeach
                    </select>
                    @error('entidad_id')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nombre (OBLIGATORIO) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="nombre" autocomplete="off" placeholder="Ej: Construcción oficina central"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('nombre')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Código --}}
                <div>
                    <label class="block text-sm mb-1">CUCE – PAC – Otro</label>
                    <input wire:model="codigo" autocomplete="off"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('codigo')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Monto --}}
                <div>
                    <label class="block text-sm mb-1">Monto del Proyecto</label>
                    <input type="text" inputmode="decimal" wire:model.blur="monto_formatted" placeholder="0,00"
                        class="w-full rounded border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700
                               text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2
                               focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('monto')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CAMPOS SOLO PARA ADJUDICADO --}}
                @if ($tipo === 'Adjudicado')
                    {{-- Retención (3 columnas) --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Retención (%)</label>
                            <input type="text" inputmode="decimal" wire:model.blur="retencion_formatted"
                                placeholder="0,00"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('retencion')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Monto retenido</label>
                            <input readonly value="{{ number_format($monto_retenido, 2, ',', '.') }}"
                                class="w-full rounded border px-3 py-2
                                   bg-gray-50 dark:bg-neutral-800
                                   border-gray-200 dark:border-neutral-700
                                   text-gray-500 dark:text-neutral-400 cursor-default" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Monto neto</label>
                            <input readonly value="{{ number_format($monto_neto, 2, ',', '.') }}"
                                class="w-full rounded border px-3 py-2
                                   bg-gray-50 dark:bg-neutral-800
                                   border-gray-200 dark:border-neutral-700
                                   text-gray-500 dark:text-neutral-400 cursor-default" />
                        </div>
                    </div>

                    {{-- Fechas (2 columnas) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Fecha inicio</label>
                            <input type="date" wire:model="fecha_inicio"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('fecha_inicio')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Fecha fin</label>
                            <input type="date" wire:model="fecha_fin"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('fecha_fin')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Nota --}}
                <p class="text-xs text-gray-500 dark:text-neutral-400 pt-1">
                    <span class="text-red-500">*</span> Campos obligatorios.
                </p>
            </div>

            {{-- FOOTER --}}
            @slot('footer')
                <button type="button" wire:click="closeModal"
                    class="px-4 py-2 rounded border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="px-4 py-2 rounded cursor-pointer bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">
                        {{ $proyectoId ? 'Actualizar' : 'Guardar' }}
                    </span>
                    <span wire:loading wire:target="save">
                        Guardando…
                    </span>
                </button>
            @endslot
        </x-ui.modal>
    @endcanany
</div>
