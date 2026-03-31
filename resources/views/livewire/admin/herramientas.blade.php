@section('title', 'Herramientas')

<div>
    {{-- HEADER --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Catálogo de Herramientas
            </h1>
            <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">
                Administración y control de herramientas y equipos del almacén.
            </p>
        </div>

        @can('herramientas.create')
            <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span wire:loading.remove wire:target="openCreate">Nueva Herramienta</span>
                <span wire:loading wire:target="openCreate">Abriendo…</span>
            </button>
        @endcan
    </div>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="p-3 mb-4 rounded-lg bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200 border border-green-200 dark:border-green-700 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div
            class="rounded-xl border border-gray-200 bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-12 lg:col-span-8">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                        <input type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por nombre, código, marca o modelo…" autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40" />
                    </div>

                    @if (auth()->user()?->hasRole('Administrador'))
                        <div class="md:col-span-4 lg:col-span-2">
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Empresa</label>
                            <select wire:model.live="empresaFilter"
                                class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                                <option value="all">Todas</option>
                                @foreach ($empresas as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="md:col-span-4 lg:col-span-2">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div class="md:col-span-4 lg:col-span-2">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Filtros</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="w-full cursor-pointer flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none text-[13px] font-medium transition">
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
            class="absolute right-0 top-full mt-2 w-full sm:w-[320px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
            wire:ignore.self>
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="font-semibold text-sm text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
                <button @click="openFilters = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-4 py-4 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">Estado en
                        Sistema</label>
                    <select wire:model.live="status"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">Estado
                        Físico</label>
                    <select wire:model.live="estadoFisicoFilter"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                        <option value="all">Todos</option>
                        <option value="bueno">Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- CARDS MOBILE --}}
    <div class="space-y-3 md:hidden">
        @forelse ($herramientas as $h)
            <div class="border rounded-xl p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($h->imagen)
                            <img src="{{ Storage::url($h->imagen) }}" alt="{{ $h->nombre }}"
                                class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-neutral-700 shrink-0"
                                onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($h->imagen) }}' }))">
                        @else
                            <div
                                class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-400 border border-indigo-100 dark:border-indigo-800 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate text-sm">
                                @if ($h->codigo)
                                    <span
                                        class="font-mono text-xs text-indigo-600 dark:text-indigo-400 mr-1">{{ $h->codigo }}</span>
                                @endif
                                {{ $h->nombre }}
                            </div>
                            @if ($h->marca || $h->modelo)
                                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-0.5">
                                    {{ implode(' — ', array_filter([$h->marca, $h->modelo])) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Estado físico badge --}}
                    @php
                        $efClass = match ($h->estado_fisico) {
                            'bueno'
                                => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-700',
                            'regular'
                                => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-700',
                            'malo'
                                => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/20 dark:text-red-300 dark:border-red-700',
                            default
                                => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-neutral-700 dark:text-neutral-400 dark:border-neutral-600',
                        };
                        $efDot = match ($h->estado_fisico) {
                            'bueno' => 'bg-emerald-500',
                            'regular' => 'bg-amber-500',
                            'malo' => 'bg-red-500',
                            default => 'bg-gray-400',
                        };
                    @endphp
                    <span
                        class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium border {{ $efClass }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $efDot }}"></span>
                        {{ $h->estado_fisico_label }}
                    </span>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                    <div class="text-center p-2 rounded-lg bg-gray-50 dark:bg-neutral-800">
                        <div class="text-gray-500 dark:text-neutral-400 mb-0.5">Disp.</div>
                        <div
                            class="font-bold {{ $h->stock_disponible > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $h->stock_disponible }}
                        </div>
                    </div>
                    <div class="text-center p-2 rounded-lg bg-gray-50 dark:bg-neutral-800">
                        <div class="text-gray-500 dark:text-neutral-400 mb-0.5">Total</div>
                        <div class="font-bold text-gray-700 dark:text-neutral-300">{{ $h->stock_total }}</div>
                    </div>
                    <div class="text-center p-2 rounded-lg bg-amber-50 dark:bg-amber-900/10">
                        <div class="text-amber-600 dark:text-amber-400 mb-0.5">Prest.</div>
                        <div class="font-bold text-amber-700 dark:text-amber-400">{{ $h->stock_prestado }}</div>
                    </div>
                </div>

                {{-- Acciones --}}
                @canany(['herramientas.update', 'herramientas.toggle', 'herramientas.delete'])
                    <div class="mt-3 flex gap-2">
                        @can('herramientas.update')
                            <button wire:click="openAddStock({{ $h->id }})" wire:loading.attr="disabled"
                                wire:target="openAddStock({{ $h->id }})"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/40 text-xs font-medium cursor-pointer disabled:opacity-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span wire:loading.remove wire:target="openAddStock({{ $h->id }})">Agregar</span>
                                <span wire:loading wire:target="openAddStock({{ $h->id }})">…</span>
                            </button>
                        @endcan
                        @can('herramientas.toggle')
                            <button type="button" x-data="{ loading: false }"
                                x-on:click="loading = true; $dispatch('swal:toggle-active-herramienta', { id: {{ $h->id }}, active: @js($h->active), name: @js($h->nombre) })"
                                x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                class="flex-1 px-3 py-1.5 rounded-lg text-xs font-medium cursor-pointer disabled:opacity-50 transition {{ $h->active ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                <span x-show="!loading">{{ $h->active ? 'Desactivar' : 'Activar' }}</span>
                                <span x-show="loading" x-cloak>…</span>
                            </button>
                        @endcan
                        @can('herramientas.delete')
                            <button type="button" x-data
                                x-on:click="$dispatch('swal:delete-herramienta', { id: {{ $h->id }}, name: @js($h->nombre) })"
                                class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 text-xs cursor-pointer transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @endcan
                    </div>
                @endcanany
            </div>
        @empty
            <div
                class="border rounded-xl p-6 text-center text-sm text-gray-500 dark:text-neutral-400 dark:border-neutral-800">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- TABLA DESKTOP --}}
    <div
        class="hidden md:block border border-gray-200 rounded-xl bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden shadow-sm mt-4">
        <table class="w-full table-auto text-[13px] text-left">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-700">
                <tr class="text-left text-xs uppercase tracking-wider">
                    <th class="p-2 w-14 text-center">Img</th>
                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('codigo')">
                        Código
                        @if ($sortField === 'codigo')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif

                    </th>
                    <th class="p-2 cursor-pointer select-none" wire:click="sortBy('nombre')">
                        Herramienta
                        @if ($sortField === 'nombre')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('estado_fisico')">
                        Est. Físico
                        @if ($sortField === 'estado_fisico')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif

                    </th>
                    <th class="p-2 text-center cursor-pointer select-none" wire:click="sortBy('stock_disponible')">
                        Disp.
                        @if ($sortField === 'stock_disponible')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif

                    </th>
                    <th class="p-2 text-center cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('stock_total')">
                        Total
                    </th>
                    <th class="p-2 text-center cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('stock_prestado')">
                        Prest.
                    </th>
                    <th class="p-2 text-right cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('precio_unitario')">
                        P. Unit.
                    </th>
                    <th class="p-2 text-center cursor-pointer select-none" wire:click="sortBy('active')">Sistema</th>
                    <th class="p-2 whitespace-nowrap text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                @forelse ($herramientas as $h)
                    @php
                        $efClass = match ($h->estado_fisico) {
                            'bueno' => 'text-emerald-600 dark:text-emerald-400',
                            'regular' => 'text-amber-600 dark:text-amber-400',
                            'malo'
                                => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 px-2 py-0.5 rounded border',
                            'baja'
                                => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700 px-2 py-0.5 rounded border',
                            default => 'text-gray-500',
                        };
                        $efDot = match ($h->estado_fisico) {
                            'bueno' => 'bg-emerald-500',
                            'regular' => 'bg-amber-500',
                            'malo' => 'bg-red-500',
                            default => 'bg-gray-400',
                        };

                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/40 transition-colors"
                        wire:key="herr-{{ $h->id }}">

                        {{-- Imagen --}}
                        <td class="p-2 text-center">
                            @if ($h->imagen)
                                <img src="{{ Storage::url($h->imagen) }}"
                                    class="w-10 h-10 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-neutral-700 inline-block cursor-pointer hover:opacity-80 transition"
                                    alt="Img"
                                    onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($h->imagen) }}' }))">
                            @else
                                <div
                                    class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-neutral-800 inline-flex items-center justify-center text-gray-400 dark:text-neutral-600 border border-gray-200 dark:border-neutral-700">
                                    <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            @endif
                        </td>

                        {{-- Código --}}
                        <td class="p-2 whitespace-nowrap">
                            @if ($h->codigo)
                                <span class="font-mono text-xs font-semibold text-gray-500 dark:text-neutral-400">
                                    {{ $h->codigo }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-neutral-600">—</span>
                            @endif
                        </td>

                        {{-- Nombre + Marca/Modelo --}}
                        <td class="p-2">
                            <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate max-w-[200px]"
                                title="{{ $h->nombre }}">
                                <span class="truncate">{{ $h->nombre }}</span>
                            </div>

                            @if ($h->marca || $h->modelo)
                                <div class="text-xs text-gray-500 dark:text-neutral-500 truncate max-w-[200px] flex items-center gap-1 mt-0.5"
                                    title="{{ $h->marca }} {{ $h->modelo }}">
                                    <svg class="w-3 h-3 shrink-0 text-gray-400 dark:text-neutral-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span
                                        class="truncate">{{ implode(' — ', array_filter([$h->marca, $h->modelo])) }}</span>
                                </div>
                            @endif
                        </td>

                        {{-- Estado Físico --}}
                        <td class="p-2 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $efClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $efDot }}"></span>
                                {{ $h->estado_fisico_label }}
                            </span>
                        </td>


                        {{-- Stock Disponible --}}
                        <td class="p-2 text-center">
                            @if ($h->stock_disponible > 0)
                                <span class="text-xs font-bold text-gray-700 dark:text-neutral-300">
                                    {{ $h->stock_disponible }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 text-xs font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    0
                                </span>
                            @endif
                        </td>


                        {{-- Stock Total --}}
                        <td
                            class="p-2 text-center text-gray-600 dark:text-neutral-400 font-medium hidden xl:table-cell">
                            {{ $h->stock_total }}
                        </td>

                        {{-- Stock Prestado --}}
                        <td class="p-2 text-center hidden xl:table-cell">
                            @if ($h->stock_prestado > 0)
                                <span class="text-xs font-semibold text-gray-500 dark:text-neutral-400">
                                    {{ $h->stock_prestado }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-neutral-600">—</span>
                            @endif
                        </td>


                        {{-- Precio Unitario --}}
                        <td
                            class="p-2 text-right tabular-nums text-gray-700 dark:text-neutral-300 hidden xl:table-cell">
                            {{ number_format($h->precio_unitario, 2, ',', '.') }}
                        </td>

                        {{-- Estado Sistema --}}
                        <td class="p-2 text-center whitespace-nowrap">
                            @if ($h->active)
                                <span
                                    class="px-1.5 py-0.5 rounded text-[11px] font-medium bg-gray-50 text-gray-500 dark:bg-neutral-800 dark:text-neutral-400 border border-gray-200 dark:border-neutral-700">Activo</span>
                            @else
                                <span
                                    class="px-1.5 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 border border-red-100 dark:border-red-500/20">Inactivo</span>
                            @endif
                        </td>


                        {{-- Acciones --}}
                        @canany(['herramientas.update', 'herramientas.toggle', 'herramientas.delete'])
                            <td class="p-2 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">

                                    @can('herramientas.update')
                                        <button wire:click="openAddStock({{ $h->id }})" wire:loading.attr="disabled"
                                            wire:target="openAddStock({{ $h->id }})" title="Agregar stock"
                                            class="cursor-pointer inline-flex items-center gap-1.5 px-2 py-1 rounded border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-600 dark:text-neutral-400 hover:bg-gray-50 dark:hover:bg-neutral-800 text-[11px] font-semibold transition shadow-sm disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="openAddStock({{ $h->id }})"
                                                class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            <svg wire:loading wire:target="openAddStock({{ $h->id }})"
                                                class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span>Stock</span>
                                        </button>
                                    @endcan


                                    @can('herramientas.toggle')
                                        <button type="button" x-data="{ loading: false }"
                                            x-on:click="loading = true; $dispatch('swal:toggle-active-herramienta', { id: {{ $h->id }}, active: @js($h->active), name: @js($h->nombre) })"
                                            x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                            title="{{ $h->active ? 'Desactivar' : 'Activar' }}"
                                            class="cursor-pointer size-7 inline-flex items-center justify-center rounded border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 transition disabled:opacity-50 {{ $h->active ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' }}">

                                            <span x-show="!loading">
                                                @if ($h->active)
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @else
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                            </span>
                                            <span x-show="loading" x-cloak>
                                                <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    @endcan

                                    @can('herramientas.delete')
                                        <button type="button" x-data
                                            x-on:click="$dispatch('swal:delete-herramienta', { id: {{ $h->id }}, name: @js($h->nombre) })"
                                            title="Eliminar"
                                            class="cursor-pointer size-7 inline-flex items-center justify-center rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 text-gray-400 hover:text-red-600 dark:text-neutral-500 dark:hover:text-red-400 transition">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endcan

                                </div>
                            </td>
                        @endcanany
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-gray-400 dark:text-neutral-500">
                            <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            Sin resultados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $herramientas->links() }}</div>

    {{-- ===================== MODAL NUEVA HERRAMIENTA (Diseño Standard) ===================== --}}
    <x-ui.modal wire:key="herramienta-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal"
        title="Registro de Herramienta" maxWidth="sm:max-w-xl md:max-w-3xl" onClose="closeModal">

        <div class="space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

                {{-- Empresa (solo admin) --}}
                @if (auth()->user()?->hasRole('Administrador'))
                    <div class="col-span-2 lg:col-span-1">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Empresa <span
                                class="text-red-500">*</span></label>
                        <select wire:model="empresa_id"
                            class="cursor-pointer w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                            <option value="">Seleccione empresa...</option>
                            @foreach ($empresas as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                        @error('empresa_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Código --}}
                <div class="col-span-1 lg:col-span-1" x-data="{
                    query: @entangle('codigo'),
                    open: false,
                    codigos: @js($codigos->map(fn($c) => ['codigo' => $c->codigo, 'nombre' => $c->nombre])->values()),
                    get suggestions() {
                        if (!this.query.trim()) return [];
                        const q = this.query.toUpperCase();
                        return this.codigos.filter(c => c.codigo.includes(q) || (c.nombre && c.nombre.toUpperCase().includes(q))).slice(0, 8);
                    }
                }">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Código</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="open = true"
                            @blur="setTimeout(() => open = false, 200)" placeholder="Ej: TAL-001" autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 uppercase text-sm font-mono tracking-wider">

                        {{-- Sugerencias --}}
                        <div x-show="open && suggestions.length > 0" x-cloak
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-48 overflow-y-auto overflow-x-hidden">
                            <template x-for="item in suggestions" :key="item.codigo">
                                <div @mousedown="$wire.call('buscarPorCodigo', item.codigo); open = false"
                                    class="px-3 py-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-neutral-800 transition text-[11px] border-b dark:border-neutral-800 last:border-0">
                                    <span class="font-bold text-gray-900 dark:text-white" x-text="item.codigo"></span>
                                    <span class="text-gray-500 dark:text-neutral-500"
                                        x-text="' — ' + item.nombre"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Estado Físico --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Estado Físico
                        <span class="text-red-500">*</span></label>
                    <select wire:model="estado_fisico"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm cursor-pointer">
                        <option value="bueno">✅ Bueno</option>
                        <option value="regular">⚠️ Regular</option>
                        <option value="malo">❌ Malo</option>
                        <option value="baja">📉 Baja / Descarte</option>
                    </select>
                </div>

                {{-- Nombre --}}
                <div class="col-span-2 lg:col-span-2">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Nombre del Equipo
                        <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre" placeholder="Ej: Taladro de Banco 12 Vel."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                    @error('nombre')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Marca --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Marca</label>
                    <input type="text" wire:model="marca" placeholder="Ej: DeWalt"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                </div>

                {{-- Modelo --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Modelo /
                        Ref.</label>
                    <input type="text" wire:model="modelo" placeholder="DCD771..."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                </div>

                {{-- Unidad --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Unidad
                        Medida</label>
                    <input type="text" wire:model="unidad" placeholder="Pza, Jgo, Mt..."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                </div>

                {{-- Stock Total --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Stock Total <span
                            class="text-red-500">*</span></label>
                    <input type="number" wire:model.live="stock_total" min="0"
                        class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold text-center">
                    @error('stock_total')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Precio Unitario --}}
                <div class="col-span-2 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">P. Unitario (Bs)
                        <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" wire:model.live="precio_unitario" min="0"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-right font-medium">
                </div>

                {{-- Fotografía --}}
                <div class="col-span-2 lg:col-span-2">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Fotografía o
                        Ficha Técnica</label>
                    <div class="flex items-center gap-3">
                        <div
                            class="shrink-0 size-11 rounded-lg border border-dashed border-gray-300 dark:border-neutral-700 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-neutral-900">
                            @if ($imagen)
                                <img src="{{ $imagen->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <svg class="size-5 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <input type="file" wire:model="imagen" accept="image/*"
                            class="flex-1 text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-600 dark:file:text-indigo-400 cursor-pointer">
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="col-span-2 lg:col-span-3">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Descripción /
                        Detalles Adicionales</label>
                    <textarea wire:model="descripcion" rows="2" placeholder="Accesorios incluidos, historial de service..."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm resize-none"></textarea>
                </div>
            </div>

            {{-- RESUMEN VALORIZACIÓN --}}
            <div
                class="rounded-xl border bg-gray-50/50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden divide-y divide-gray-100 dark:divide-neutral-800">
                <div class="px-4 py-2 flex justify-between items-center bg-gray-100/50 dark:bg-black/10">
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-neutral-400">Resumen
                        Almacén</span>
                    <span class="text-[10px] font-bold text-gray-400">VALORES CALCULADOS</span>
                </div>
                <div class="p-3 grid grid-cols-2 gap-4">
                    <div class="space-y-0.5">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Disponible Inicial</div>
                        <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $stock_disponible }}
                            {{ $unidad ?: 'Unid.' }}</div>
                    </div>
                    <div class="space-y-0.5 text-right">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Inversión Total estimada</div>
                        <div class="text-sm font-black text-gray-900 dark:text-neutral-100">Bs.
                            {{ number_format((float) $precio_total, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
                <button type="button" wire:click="closeModal"
                    class="w-full sm:w-auto px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="w-full sm:w-auto px-6 py-2 rounded-lg cursor-pointer bg-black text-white hover:bg-neutral-800 transition text-sm font-black shadow-lg shadow-black/10">
                    <span wire:loading.remove wire:target="save">Guardar Registro</span>
                    <span wire:loading wire:target="save">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- ==========================================

         MODAL AGREGAR STOCK
         ========================================== --}}
    <x-ui.modal wire:key="add-stock-modal" model="openAddStockModal" title="Agregar Stock" maxWidth="sm:max-w-sm"
        onClose="closeAddStockModal">
        <div class="space-y-4">
            {{-- Info herramienta --}}
            <div class="rounded-lg bg-gray-50 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 p-3">
                <div class="text-[10px] uppercase font-bold text-gray-400 dark:text-neutral-500 mb-1">Herramienta</div>
                <div class="font-semibold text-gray-900 dark:text-neutral-100 text-sm">{{ $addStockNombre }}</div>
                @if ($addStockCodigo)
                    <div class="font-mono text-xs text-gray-500 dark:text-neutral-400 mt-0.5">{{ $addStockCodigo }}
                    </div>
                @endif
            </div>

            {{-- Cantidad a agregar --}}
            <div>
                <label class="block text-sm font-medium mb-1.5">
                    Cantidad a agregar <span class="text-red-500">*</span>
                </label>
                <input type="number" wire:model.live="addStockCantidad" min="1" max="9999"
                    autocomplete="off"
                    class="w-full rounded border px-3 py-3 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700 text-center text-2xl font-bold tabular-nums">
                @error('addStockCantidad')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-xs text-gray-400 text-center">Incrementará el stock total y disponible.</p>
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2">
                <button type="button" wire:click="closeAddStockModal"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="saveAddStock" wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-lg cursor-pointer bg-black text-white hover:bg-neutral-800 transition text-sm font-black shadow-lg shadow-black/10">
                    <span wire:loading.remove wire:target="saveAddStock">Agregar</span>
                    <span wire:loading wire:target="saveAddStock">...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>


    {{-- MODAL ZOOM IMAGEN --}}
    <div x-data="{ imgUrl: null, open: false }" @open-image-modal.window="imgUrl = $event.detail; open = true">
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
            @click="open = false" @keydown.escape.window="open = false">
            <button class="absolute top-4 right-4 text-white hover:text-gray-300" @click="open = false">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="imgUrl" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl"
                @click.stop>
        </div>
    </div>
</div>
