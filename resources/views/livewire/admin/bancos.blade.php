@section('title', 'Bancos')

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
                Bancos
            </h1>
            <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">
                Registro y Configuración de bancos por empresa. Los nombres de banco con palabras como “Efectivo” se
                visualizará por separado en el dashboard.
            </p>
        </div>

        <div class="flex gap-2">
            @can('bancos.create')
                <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span wire:loading.remove wire:target="openCreate">Nuevo Banco</span>
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
    @php $activeFiltersCount = ($status !== 'active' ? 1 : 0) + ($monedaFilter !== 'all' ? 1 : 0); @endphp
    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div
            class="rounded-xl border border-gray-200 bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            {{-- MOBILE (<= md): FILTROS COLAPSABLES --}}
            <div class="md:hidden" x-data="{ openMobile: false }">
                <div class="px-4 h-11 flex items-center justify-between">
                    <div class="text-[13px] font-semibold text-gray-700 dark:text-neutral-200 flex items-center gap-2">
                        Filtros
                        <span
                            class="text-[11px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-400">
                            {{ $activeFiltersCount }}
                        </span>
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
                            placeholder="Buscar Banco o Nro. de Cuenta..." autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Mostrar</label>
                            <select wire:model.live="perPage"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
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
                            placeholder="Buscar Banco o Nro. de Cuenta..." autocomplete="off"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
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
                            <span
                                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                                {{ $activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL FLOTANTE --}}
        <div x-show="openFilters" x-cloak @click.outside="openFilters = false"
            @keydown.escape.window="openFilters = false"
            class="absolute right-0 top-full mt-2 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
            wire:ignore.self wire:key="bancos-panel-filtros">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
                @if ($activeFiltersCount > 0)
                    <button type="button" wire:click="clearFilters"
                        class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium cursor-pointer transition">
                        Limpiar filtros ({{ $activeFiltersCount }})
                    </button>
                @endif
            </div>

            <div class="px-4 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Moneda</label>
                    <select wire:model.live="monedaFilter"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] cursor-pointer">
                        <option value="all">Todas las Monedas</option>
                        <option value="BOB">Bolivianos (Bs)</option>
                        <option value="USD">Dólar (USD)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Estado</label>
                    <select wire:model.live="status"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] cursor-pointer">
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
        @forelse ($bancos as $b)
            <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $b->nombre }}</div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1">
                            {{ $b->empresa?->nombre ?? '—' }}
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if ($b->active)
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
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500 dark:text-neutral-400">ID</span>
                        <span class="font-medium">{{ $bancos->firstItem() + $loop->index }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Titular:</span>
                        <span class="truncate">{{ $b->titular ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Cuenta:</span>
                        <span class="truncate">{{ $b->numero_cuenta ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Monto Inicial:</span>
                        <span
                            class="truncate text-gray-500">{{ number_format((float) ($b->monto_inicial ?? 0), 2, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Monto Actual:</span>
                        <span class="truncate">{{ number_format((float) ($b->monto ?? 0), 2, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Moneda:</span>
                        <span class="truncate">{{ $b->moneda ?? '—' }}</span>
                    </div>
                </div>

                {{-- Acciones --}}
                @canany(['bancos.update', 'bancos.toggle'])
                    <div class="mt-4 flex gap-2">

                        {{-- EDITAR (con bloqueo) --}}
                        @can('bancos.update')
                            <button wire:click="openEdit({{ $b->id }})" wire:loading.attr="disabled"
                                wire:target="openEdit({{ $b->id }})"
                                class="w-full px-3 py-1 rounded border border-gray-300
                                       cursor-pointer hover:bg-gray-50
                                       dark:border-neutral-700 dark:hover:bg-neutral-800
                                       disabled:opacity-50 disabled:cursor-not-allowed">

                                <span wire:loading.remove wire:target="openEdit({{ $b->id }})">
                                    Editar
                                </span>

                                <span wire:loading wire:target="openEdit({{ $b->id }})">
                                    Abriendo…
                                </span>
                            </button>
                        @endcan

                        {{-- TOGGLE ACTIVO (SweetAlert + Alpine loading) --}}
                        @can('bancos.toggle')
                            <button type="button" x-data="{ loading: false }"
                                x-on:click="
                                    loading = true;
                                    $dispatch('swal:toggle-active-banco', {
                                        id: {{ $b->id }},
                                        active: @js($b->active),
                                        name: @js($b->nombre)
                                    });
                                "
                                x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                class="w-full px-3 py-1 rounded text-sm font-medium
                                       cursor-pointer
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                       {{ $b->active
                                           ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                           : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">

                                <span x-show="!loading">
                                    {{ $b->active ? 'Desactivar' : 'Activar' }}
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

    {{-- TABLET + DESKTOP: TABLA (misma plantilla estilo Proyectos, SIN expandible) --}}
    <div
        class="hidden md:block border border-gray-200 rounded-xl bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden shadow-sm mt-4">
        <table class="w-full table-auto text-[13px] text-left">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200
                   border-b border-gray-200 dark:border-neutral-200">

                <tr class="text-left text-xs uppercase tracking-wider">

                    <th class="w-[70px] text-center p-2 cursor-pointer select-none whitespace-nowrap"
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

                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('nombre')">
                        Banco
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

                    <th class="p-2 cursor-pointer select-none whitespace-nowrap hidden xl:table-cell"
                        wire:click="sortBy('titular')">
                        Titular
                        @if ($sortField === 'titular')
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

                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('numero_cuenta')">
                        Nro. Cuenta
                        @if ($sortField === 'numero_cuenta')
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

                    <th class="w-[130px] p-2 cursor-pointer select-none whitespace-nowrap text-right hidden xl:table-cell"
                        wire:click="sortBy('monto_inicial')">
                        Monto Inicial
                        @if ($sortField === 'monto_inicial')
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

                    <th class="w-[130px] p-2 cursor-pointer select-none whitespace-nowrap text-right"
                        wire:click="sortBy('monto')">
                        Monto Actual
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

                    <th class="w-[90px] p-2 cursor-pointer select-none whitespace-nowrap hidden xl:table-cell"
                        wire:click="sortBy('moneda')">
                        Moneda
                        @if ($sortField === 'moneda')
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

                    <th class="text-center w-[85px] p-2 cursor-pointer select-none whitespace-nowrap"
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

                    @canany(['bancos.update', 'bancos.toggle'])
                        <th class="w-[120px] p-2 whitespace-nowrap text-center">
                            Acciones
                        </th>
                    @endcanany
                </tr>
            </thead>
            @foreach ($bancos as $b)
                <tbody wire:key="banco-{{ $b->id }}" x-data="{ open: false }"
                    class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/40 transition-colors">

                        {{-- ID + botón expandible --}}
                        <td class="p-1 text-center whitespace-nowrap" x-data="{ showToggle: !window.matchMedia('(min-width: 1280px)').matches }"
                            x-init="const mq = window.matchMedia('(min-width: 1280px)');
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
                            <span class="ml-1">{{ $bancos->firstItem() + $loop->index }}</span>
                        </td>

                        {{-- Banco --}}
                        <td class="p-2 min-w-0">
                            <span class="block truncate max-w-full" title="{{ $b->nombre }}">
                                {{ $b->nombre }}
                            </span>
                        </td>

                        {{-- Titular --}}
                        <td class="p-2 min-w-0 hidden xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $b->titular ?? '-' }}">
                                {{ $b->titular ?? '-' }}
                            </span>
                        </td>

                        {{-- Nro. Cuenta --}}
                        <td class="p-2 whitespace-nowrap">
                            <span class="block truncate max-w-full" title="{{ $b->numero_cuenta ?? '-' }}">
                                {{ $b->numero_cuenta ?? '-' }}
                            </span>
                        </td>

                        {{-- Monto Inicial --}}
                        <td
                            class="p-2 whitespace-nowrap text-right tabular-nums text-gray-500 dark:text-neutral-400 hidden xl:table-cell">
                            {{ number_format((float) ($b->monto_inicial ?? 0), 2, ',', '.') }}
                        </td>

                        {{-- Monto Actual --}}
                        <td class="p-2 whitespace-nowrap text-right tabular-nums font-semibold">
                            {{ number_format((float) ($b->monto ?? 0), 2, ',', '.') }}
                        </td>

                        {{-- Moneda --}}
                        <td class="p-2 whitespace-nowrap hidden xl:table-cell">
                            {{ $b->moneda ?? '-' }}
                        </td>

                        {{-- Estado --}}
                        <td class="text-center p-2 whitespace-nowrap">
                            @if ($b->active)
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
                        </td>

                        {{-- Acciones --}}
                        @canany(['bancos.update', 'bancos.toggle'])
                            <td class="p-2 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- EDITAR (Livewire) --}}
                                    @can('bancos.update')
                                        <button wire:click="openEdit({{ $b->id }})" wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $b->id }})" title="Editar banco"
                                            aria-label="Editar banco"
                                            class="cursor-pointer rounded p-1
                                                   hover:bg-gray-100 dark:hover:bg-neutral-800
                                                   disabled:opacity-50 disabled:cursor-not-allowed">

                                            <svg wire:loading.remove wire:target="openEdit({{ $b->id }})"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>

                                            <svg wire:loading wire:target="openEdit({{ $b->id }})"
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
                                    @can('bancos.toggle')
                                        <button type="button" x-data="{ loading: false }"
                                            x-on:click="
                                                loading = true;
                                                $dispatch('swal:toggle-active-banco', {
                                                    id: {{ $b->id }},
                                                    active: @js($b->active),
                                                    name: @js($b->nombre)
                                                });
                                            "
                                            x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                            title="{{ $b->active ? 'Desactivar banco' : 'Activar banco' }}"
                                            aria-label="{{ $b->active ? 'Desactivar banco' : 'Activar banco' }}"
                                            class="cursor-pointer inline-flex items-center justify-center size-8 rounded
                                                   disabled:opacity-50 disabled:cursor-not-allowed
                                                   {{ $b->active
                                                       ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                       : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">

                                            <span x-show="!loading">
                                                @if ($b->active)
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

                    {{-- Detalle expandible SOLO cuando NO es xl --}}
                    @php
                        // En md (<xl): ID, Banco, Nro. Cuenta, Monto Actual, Estado = 5
                        // + Acciones si tiene permisos
                        $colspan =
                            5 + (auth()->user()->can('bancos.update') || auth()->user()->can('bancos.toggle') ? 1 : 0);
                    @endphp

                    <tr x-show="open" x-cloak
                        class="xl:hidden bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">
                        <td class="pl-20 py-2" colspan="{{ $colspan }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-medium text-gray-500 dark:text-neutral-400">Titular</span>
                                    <span
                                        class="block text-gray-900 dark:text-neutral-200 truncate">{{ $b->titular ?? '—' }}</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">Monto
                                        Inicial</span>
                                    <span
                                        class="block text-gray-900 dark:text-neutral-200 tabular-nums">{{ number_format((float) ($b->monto_inicial ?? 0), 2, ',', '.') }}</span>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-medium text-gray-500 dark:text-neutral-400">Moneda</span>
                                    <span
                                        class="block text-gray-900 dark:text-neutral-200">{{ $b->moneda ?? '—' }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endforeach

            @if ($bancos->count() === 0)
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    @php
                        $colspan =
                            8 + (auth()->user()->can('bancos.update') || auth()->user()->can('bancos.toggle') ? 1 : 0);
                    @endphp
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400"
                            colspan="{{ $colspan }}">
                            Sin resultados.
                        </td>
                    </tr>
                </tbody>
            @endif
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $bancos->links() }}
    </div>

    {{-- MODAL BANCO (create / update) --}}
    @canany(['bancos.create', 'bancos.update'])
        <x-ui.modal wire:key="bancos-modal" model="openModal" :title="$bancoId ? 'Editar Banco' : 'Nuevo Banco'" maxWidth="sm:max-w-xl md:max-w-2xl"
            onClose="closeModal">
            {{-- BODY --}}
            <div class="space-y-4">
                {{-- Nombre (OBLIGATORIO) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Nombre del Banco <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="nombre" autocomplete="off" placeholder="Ej. Banco Union o Efectivo Willam"
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

                {{-- Titular (opcional) --}}
                <div>
                    <label class="block text-sm mb-1">Titular de la Cuenta <span class="text-red-500">*</span></label>
                    <input wire:model="titular" autocomplete="off" placeholder="Ej: Sitcom SRL"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('titular')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Número de cuenta (OBLIGATORIO) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Número de Cuenta <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="numero_cuenta" autocomplete="off" placeholder="Ej: 123-4567890-01"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('numero_cuenta')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Monto (solo editable en create) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Monto Inicial
                        @if (!$bancoId)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    <input type="text" inputmode="decimal" wire:model.lazy="monto_formatted" placeholder="0,00"
                        @disabled($bancoId)
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700
                           disabled:bg-gray-100 disabled:dark:bg-neutral-800
                           disabled:opacity-80 disabled:cursor-not-allowed" />

                    @error('monto')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror

                    @if ($bancoId)
                        <div class="text-xs mt-1 text-gray-500 dark:text-neutral-400">
                            El monto inicial no se puede editar. Registra movimientos desde el módulo correspondiente.
                        </div>
                    @endif
                </div>

                {{-- Moneda (OBLIGATORIO) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Moneda
                        @if (!$bancoId)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <select wire:model="moneda" @disabled($bancoId)
                        class="w-full cursor-pointer rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700
                           disabled:bg-gray-100 disabled:dark:bg-neutral-800
                           disabled:opacity-80 disabled:cursor-not-allowed">
                        <option value="">Seleccione...</option>
                        <option value="BOB">Bolivianos (Bs)</option>
                        <option value="USD">Dólar (USD)</option>
                    </select>
                    @error('moneda')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror

                    @if ($bancoId)
                        <div class="text-xs mt-1 text-gray-500 dark:text-neutral-400">
                            La moneda no se puede editar una vez registrado el banco.
                        </div>
                    @endif
                </div>

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
                    class="px-4 py-2 cursor-pointer rounded bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">
                        {{ $bancoId ? 'Actualizar' : 'Guardar' }}
                    </span>
                    <span wire:loading wire:target="save">
                        Guardando…
                    </span>
                </button>
            @endslot
        </x-ui.modal>
    @endcanany
</div>
