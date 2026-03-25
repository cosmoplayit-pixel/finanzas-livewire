@section('title', 'Herramientas')

@push('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding-top: 6px !important;
            border-color: #d1d5db !important;
            border-radius: 0.375rem !important;
        }

        .dark .select2-container--default .select2-selection--single {
            background-color: #171717 !important;
            border-color: #3f3f46 !important;
            color: #f4f4f5 !important;
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f4f4f5 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }

        .select2-dropdown {
            border-color: #d1d5db !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .dark .select2-dropdown {
            background-color: #171717 !important;
            border-color: #3f3f46 !important;
            color: white !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
        }
    </style>
@endpush

<div>
    {{-- HEADER (RESPONSIVE) --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Catálogo de Herramientas
            </h1>
            <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">
                Administración y control de herramientas y equipos del almacén.
            </p>
        </div>

        <div class="flex gap-2">
            @can('herramientas.create')
                <!-- Temporarily using existing permission or should we assume herramientas.create? user said same style -->
                <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span wire:loading.remove wire:target="openCreate">Nueva Herramienta</span>
                    <span wire:loading wire:target="openCreate">Abriendo…</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms
            class="p-3 mb-4 rounded bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div x-data="{ openFilters: false }" class="relative mb-6">
        <div
            class="rounded-xl border border-gray-200 bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            {{-- DESKTOP --}}
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-12 lg:col-span-6">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                        <input type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por Nombre, Código, Marca o Modelo..." autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                    </div>

                    @if (auth()->user()?->hasRole('Administrador'))
                        <div class="md:col-span-4 lg:col-span-2">
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Empresa</label>
                            <select wire:model.live="empresaFilter"
                                class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
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
                            class="w-full cursor-pointer rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div class="md:col-span-4 lg:col-span-2">
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
            wire:ignore.self>
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros Avanzados</div>
            </div>
            <div class="px-4 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Estado en
                        Sistema</label>
                    <select wire:model.live="status"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] cursor-pointer">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Estado
                        Físico</label>
                    <select wire:model.live="estadoFisicoFilter"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] cursor-pointer">
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

    {{-- CARDS (MOBILE) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($herramientas as $h)
            <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if ($h->imagen)
                            <img src="{{ Storage::url($h->imagen) }}" alt="{{ $h->nombre }}"
                                class="w-12 h-12 rounded object-cover border border-gray-200 dark:border-neutral-700">
                        @else
                            <div
                                class="w-12 h-12 rounded bg-gray-100 dark:bg-neutral-800 flex items-center justify-center text-gray-400 border border-gray-200 dark:border-neutral-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate"><span
                                    class="text-xs text-gray-500 mr-1">[{{ $h->codigo ?? 'S/C' }}]</span>{{ $h->nombre }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-0.5">
                                {{ $h->marca ?? 'S/M' }} {{ $h->modelo ? '- ' . $h->modelo : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500 dark:text-neutral-400">Stock Disp / Total</span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $h->stock_disponible }} /
                            {{ $h->stock_total }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Estado Físico:</span>
                        <span class="truncate">{{ $h->estado_fisico_label }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Precio Unitario:</span>
                        <span class="truncate">Bs {{ number_format($h->precio_unitario, 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="mt-4 flex gap-2">
                    <button wire:click="openEdit({{ $h->id }})"
                        class="w-full px-3 py-1.5 rounded border border-gray-300 cursor-pointer hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800 text-sm">
                        Editar
                    </button>
                    <button type="button" x-data="{}"
                        x-on:click="$dispatch('swal:toggle-active-herramienta', { id: {{ $h->id }}, active: @js($h->active), name: @js($h->nombre) })"
                        class="w-full px-3 py-1.5 rounded text-sm font-medium cursor-pointer {{ $h->active ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                        {{ $h->active ? 'Desactivar' : 'Activar' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- TABLA DESKTOP --}}
    <div
        class="hidden md:block border border-gray-200 rounded-xl bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden shadow-sm mt-4">
        <table class="w-full table-auto text-[13px] text-left">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left text-xs uppercase tracking-wider">
                    <th class="p-2 w-16 text-center">Img</th>
                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('codigo')">Código
                    </th>
                    <th class="p-2 cursor-pointer select-none" wire:click="sortBy('nombre')">Herramienta</th>
                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('estado_fisico')">
                        Est. Físico</th>
                    <th class="p-2 text-center cursor-pointer select-none" wire:click="sortBy('stock_disponible')">
                        Stock Disp.</th>
                    <th class="p-2 text-center cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('stock_total')">Total</th>
                    <th class="p-2 text-right cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('precio_unitario')">P. Unit.</th>
                    <th class="p-2 text-center cursor-pointer select-none" wire:click="sortBy('active')">Sistema</th>
                    <th class="p-2 whitespace-nowrap text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                @forelse ($herramientas as $h)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/40 transition-colors"
                        wire:key="herr-{{ $h->id }}">
                        <td class="p-2 text-center">
                            @if ($h->imagen)
                                <img src="{{ Storage::url($h->imagen) }}"
                                    class="w-10 h-10 object-cover rounded shadow-sm border border-gray-200 dark:border-neutral-700 inline-block cursor-pointer hover:opacity-80 transition"
                                    alt="Img"
                                    onclick="$dispatch('open-image-modal', '{{ Storage::url($h->imagen) }}')">
                            @else
                                <div
                                    class="w-10 h-10 rounded bg-gray-100 dark:bg-neutral-800 inline-flex items-center justify-center text-gray-400 border border-gray-200 dark:border-neutral-700 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="p-2 whitespace-nowrap font-medium text-gray-600 dark:text-neutral-400">
                            {{ $h->codigo ?? '-' }}</td>
                        <td class="p-2">
                            <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate max-w-[200px]"
                                title="{{ $h->nombre }}">{{ $h->nombre }}</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-500 truncate max-w-[200px]"
                                title="{{ $h->marca }} {{ $h->modelo }}">{{ $h->marca }}
                                {{ $h->modelo }}</div>
                        </td>
                        <td class="p-2 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-neutral-800 dark:text-neutral-300 border border-gray-200 dark:border-neutral-700">
                                {{ $h->estado_fisico_label }}
                            </span>
                        </td>
                        <td
                            class="p-2 text-center font-bold {{ $h->stock_disponible > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $h->stock_disponible }}
                        </td>
                        <td class="p-2 text-center text-gray-600 dark:text-neutral-400 hidden xl:table-cell">
                            {{ $h->stock_total }}</td>
                        <td class="p-2 text-right tabular-nums hidden xl:table-cell">
                            {{ number_format($h->precio_unitario, 2, ',', '.') }}</td>
                        <td class="p-2 text-center whitespace-nowrap">
                            @if ($h->active)
                                <span
                                    class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">Activo</span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-xs bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200">Inactivo</span>
                            @endif
                        </td>
                        <td class="p-2 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="openEdit({{ $h->id }})" title="Editar"
                                    class="cursor-pointer p-1 rounded hover:bg-gray-100 dark:hover:bg-neutral-800 text-gray-600 dark:text-neutral-400 transition">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <button type="button" x-data="{}"
                                    x-on:click="$dispatch('swal:toggle-active-herramienta', { id: {{ $h->id }}, active: @js($h->active), name: @js($h->nombre) })"
                                    title="{{ $h->active ? 'Desactivar' : 'Activar' }}"
                                    class="cursor-pointer size-7 inline-flex items-center justify-center rounded transition {{ $h->active ? 'bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-500/20 dark:text-red-400' : 'bg-green-100 text-green-600 hover:bg-green-200 dark:bg-green-500/20 dark:text-green-400' }}">
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
                                </button>
                                <button type="button" x-data
                                    x-on:click="$dispatch('swal:delete-herramienta', { id: {{ $h->id }}, name: @js($h->nombre) })"
                                    title="Eliminar"
                                    class="cursor-pointer p-1 rounded hover:bg-red-100 dark:hover:bg-red-500/20 text-gray-500 hover:text-red-600 dark:text-neutral-400 dark:hover:text-red-400 transition">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-4 text-center text-gray-500 dark:text-neutral-400">Sin resultados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $herramientas->links() }}</div>

    {{-- MODAL --}}
    <x-ui.modal wire:key="herramienta-modal" model="openModal" :title="$herramientaId ? 'Editar Herramienta' : 'Nueva Herramienta'" maxWidth="md:max-w-4xl"
        onClose="closeModal">
        <form wire:submit.prevent="save" class="space-y-4">
            {{-- Empresa --}}
            @if (auth()->user()?->hasRole('Administrador'))
                <div>
                    <label class="block text-sm font-medium mb-1">Empresa <span class="text-red-500">*</span></label>
                    <select wire:model="empresa_id"
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300">
                        <option value="">Seleccione...</option>
                        @foreach ($empresas as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                    @error('empresa_id')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Código --}}
                <div class="col-span-1 md:col-span-2" wire:ignore>
                    <label class="block text-sm font-medium mb-1">Código (Escribe o Selecciona uno)</label>
                    <select id="select2-codigo" class="w-full uppercase select2-base">
                        @if ($codigo)
                            <option value="{{ $codigo }}" selected>{{ $codigo }}</option>
                        @endif
                        @foreach ($codigosExistentes as $c)
                            @if ($c !== $codigo)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('codigo')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nombre --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1 line-clamp-1">Nombre <span
                            class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre" placeholder="Ej: Taladro Percutor 800W"
                        autocomplete="off" {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 uppercase @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif">
                    @error('nombre')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Marca --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Marca</label>
                    <input type="text" wire:model="marca" placeholder="Ej: Bosch" autocomplete="off"
                        {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 uppercase @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif">
                    @error('marca')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Modelo --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Modelo</label>
                    <input type="text" wire:model="modelo" placeholder="Ej: GSB 16 RE" autocomplete="off"
                        {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 uppercase @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif">
                    @error('modelo')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Estado Físico --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Estado Físico <span
                            class="text-red-500">*</span></label>
                    <select wire:model="estado_fisico" {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif">
                        <option value="bueno">Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                        <option value="baja">Baja / Descarte</option>
                    </select>
                </div>

                {{-- Unidad --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Unidad</label>
                    <input type="text" wire:model="unidad" placeholder="Ej: pza, caja, kit" autocomplete="off"
                        {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 uppercase @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif">
                    @error('unidad')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Stock --}}
                <div
                    class="col-span-1 md:col-span-2 grid grid-cols-3 gap-4 border-t border-b border-gray-100 dark:border-neutral-800 py-3 mt-2">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-300">Stock Total
                            <span class="text-red-500">*</span></label>
                        <input type="number" wire:model.live="stock_total" min="0" autocomplete="off"
                            class="w-full rounded border px-3 py-2 bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-neutral-700 font-semibold focus:ring-2 focus:ring-gray-300">
                        @error('stock_total')
                            <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-emerald-700 dark:text-emerald-500">Disponible
                            <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="stock_disponible" min="0" autocomplete="off"
                            class="w-full rounded border px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 font-semibold text-emerald-700 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-300">
                        @error('stock_disponible')
                            <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-amber-700 dark:text-amber-500">Prestado <span
                                class="text-red-500">*</span></label>
                        <input type="number" wire:model="stock_prestado" min="0" autocomplete="off"
                            class="w-full rounded border px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 font-semibold text-amber-700 dark:text-amber-400 focus:ring-2 focus:ring-amber-300">
                        @error('stock_prestado')
                            <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Precios --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Precio Unitario (Bs) <span
                            class="text-red-500">*</span></label>
                    <input type="number" step="0.01" wire:model.live="precio_unitario" min="0"
                        autocomplete="off" {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 text-right font-medium @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif">
                    @error('precio_unitario')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Precio Total (Bs) <span
                            class="text-red-500">*</span></label>
                    <input type="number" step="0.01" wire:model="precio_total" min="0" readonly
                        autocomplete="off"
                        class="w-full rounded border px-3 py-2 bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-neutral-700 focus:ring-0 text-right font-bold text-indigo-600 dark:text-indigo-400">
                    @error('precio_total')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Descripción --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Descripción / Observaciones</label>
                    <textarea wire:model="descripcion" rows="2" {{ $isExisting ? 'disabled' : '' }}
                        class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 focus:ring-2 focus:ring-gray-300 uppercase @if ($isExisting) bg-gray-100 dark:bg-neutral-800 opacity-75 cursor-not-allowed @endif"></textarea>
                    @error('descripcion')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fotografía --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Fotografía del Ítem</label>
                    <div class="flex items-center gap-4">
                        <div class="shrink-0">
                            @if ($imagen)
                                <img src="{{ $imagen->temporaryUrl() }}"
                                    class="w-20 h-20 object-cover rounded border border-gray-200 shadow-sm"
                                    alt="Preview">
                            @elseif ($imagenActual)
                                <img src="{{ Storage::url($imagenActual) }}"
                                    class="w-20 h-20 object-cover rounded border border-gray-200 shadow-sm"
                                    alt="Actual">
                            @else
                                <div
                                    class="w-20 h-20 bg-gray-100 dark:bg-neutral-800 rounded border border-dashed border-gray-300 dark:border-neutral-700 flex items-center justify-center text-gray-400">
                                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="imagen" accept="image/*"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-400">
                            <div class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG. Max 2MB.</div>
                            @error('imagen')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                            <div wire:loading wire:target="imagen" class="text-blue-500 text-xs font-semibold mt-1">
                                Cargando imagen...</div>
                        </div>
                    </div>
                </div>
            </div>

            <p
                class="text-xs text-gray-500 dark:text-neutral-400 mt-4 border-t border-gray-100 dark:border-neutral-800 pt-3">
                <span class="text-red-500">*</span> Campos obligatorios.
            </p>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" wire:click="closeModal"
                    class="px-4 py-2 rounded border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 transition">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save, imagen"
                    class="px-5 py-2 rounded bg-black text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>
        </form>
    </x-ui.modal>

    {{-- MODAL FOTO ZOOM (Reutilizamos componente si existe o generamos uno rápido) --}}
    <div x-data="{ imgUrl: null, open: false }" @open-image-modal.window="imgUrl = $event.detail; open = true">
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
            @click="open = false" @keydown.escape.window="open = false">
            <button class="absolute top-4 right-4 text-white hover:text-gray-300" @click="open = false">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <img :src="imgUrl" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl"
                @click.stop>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('swal:toggle-active-herramienta', (e) => {
            const {
                id,
                active,
                name
            } = e.detail;
            Swal.fire({
                title: active ? '¿Desactivar herramienta?' : '¿Activar herramienta?',
                text: '¿Seguro que desea ' + (active ? 'desactivar' : 'activar') +
                    ' la herramienta "' + name + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) Livewire.dispatch('doToggleActiveHerramienta', {
                    id
                });
            });
        });

        window.addEventListener('swal:delete-herramienta', (e) => {
            const {
                id,
                name
            } = e.detail;
            Swal.fire({
                title: '¿Eliminar herramienta?',
                text: '¿Seguro que desea eliminar "' + name +
                    '" permanentemente? Esta acción no se puede deshacer.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) Livewire.dispatch('doDeleteHerramienta', {
                    id
                });
            });
        });
    });
</script>

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            // Sincronización Select2 -> Livewire
            Livewire.on('reinit-select2', () => {
                const $el = $('#select2-codigo');
                setTimeout(() => {
                    $el.select2({
                        tags: true,
                        placeholder: 'ESCRIBE O SELECCIONA UN CÓDIGO',
                        allowClear: true,
                        width: '100%'
                    }).on('change', function(e) {
                        @this.set('codigo', $(this).val());
                    });

                    // Asegurar que Select2 tenga el valor actual de Livewire
                    $el.val(@this.codigo).trigger('change.select2');
                }, 100);
            });

            // Sincronización Livewire -> Select2 (para cuando se encuentra código duplicado)
            Livewire.on('codigo-found', () => {
                $('#select2-codigo').val(@this.codigo).trigger('change.select2');
            });
        });
    </script>
@endpush
