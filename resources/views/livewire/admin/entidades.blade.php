@section('title', 'Entidades')

<div class="p-0 md:p-6 space-y-4">

    {{-- HEADER (RESPONSIVE) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Entidades</h1>

        @can('entidades.create')
            <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                class="w-full sm:w-auto px-4 py-2 rounded
                       bg-black text-white
                       hover:bg-gray-800 hover:text-white
                       transition-colors duration-150
                       cursor-pointer
                       disabled:opacity-50 disabled:cursor-not-allowed">

                {{-- Texto normal --}}
                <span wire:loading.remove wire:target="openCreate">
                    Nueva Entidad
                </span>

                {{-- Texto loading --}}
                <span wire:loading wire:target="openCreate">
                    Abriendo…
                </span>
            </button>
        @endcan
    </div>

    {{-- ALERTAS (LIGHT/DARK) --}}
    @if (session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
        {{-- Buscar --}}
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre o Correo"
            class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

        {{-- Selects derecha --}}
        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">
            {{-- Estado --}}
            <select wire:model.live="status"
                class="w-full sm:w-auto
                    border rounded px-3 py-2
                    bg-white text-gray-900 border-gray-300
                    dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                    focus:outline-none focus:ring-2 focus:ring-offset-0
                    focus:ring-gray-300 dark:focus:ring-neutral-600">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>

            {{-- PerPage --}}
            <select wire:model.live="perPage"
                class="w-full sm:w-auto
                    border rounded px-3 py-2
                    bg-white text-gray-900 border-gray-300
                    dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                    focus:outline-none focus:ring-2 focus:ring-offset-0
                    focus:ring-gray-300 dark:focus:ring-neutral-600">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- MOBILE: CARDS (md:hidden) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($entidades as $e)
            <div wire:key="entidad-card-{{ $e->id }}"
                class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $e->nombre }}</div>
                    </div>

                    <div class="shrink-0">
                        @if ($e->active)
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

                {{-- Datos --}}
                <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500 dark:text-neutral-400">ID</span>
                        <span class="font-medium">{{ $e->id }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Email:</span>
                        <span class="truncate">{{ $e->email ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Teléfono:</span>
                        <span class="truncate">{{ $e->telefono ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Dirección:</span>
                        <span class="truncate" title="{{ $e->direccion ?? '' }}">{{ $e->direccion ?? '—' }}</span>
                    </div>
                </div>

                {{-- Acciones --}}
                @canany(['entidades.update', 'entidades.toggle'])
                    <div class="mt-4">

                        {{-- EJEMPLO DE BLOQUEO (opcional, si aplica en tu modelo) --}}
                        @if ($e->is_system ?? false)
                            <span
                                class="block w-full px-3 py-1 rounded text-center text-xs font-medium
                       bg-gray-200 text-gray-700
                       dark:bg-neutral-800 dark:text-neutral-300">
                                Entidad bloqueada
                            </span>
                        @else
                            <div class="grid grid-cols-2 gap-2">

                                {{-- EDITAR --}}
                                @can('entidades.update')
                                    <button wire:click="openEdit({{ $e->id }})" wire:loading.attr="disabled"
                                        wire:target="openEdit({{ $e->id }})"
                                        class="w-full px-3 py-1 rounded border border-gray-300
                               cursor-pointer hover:bg-gray-50
                               dark:border-neutral-700 dark:hover:bg-neutral-800
                               disabled:opacity-50 disabled:cursor-not-allowed
                               @cannot('entidades.toggle') col-span-2 @endcannot">

                                        <span wire:loading.remove wire:target="openEdit({{ $e->id }})">
                                            Editar
                                        </span>

                                        <span wire:loading wire:target="openEdit({{ $e->id }})">
                                            Abriendo…
                                        </span>
                                    </button>
                                @endcan

                                {{-- ACTIVAR / DESACTIVAR --}}
                                @can('entidades.toggle')
                                    <button type="button" x-data="{ loading: false }"
                                        x-on:click="
                            loading = true;
                            $dispatch('swal:toggle-active-entidad', {
                                id: {{ $e->id }},
                                active: @js($e->active),
                                name: @js($e->nombre)
                            });
                        "
                                        x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                        class="w-full px-3 py-1 rounded text-sm font-medium
                               cursor-pointer
                               disabled:opacity-50 disabled:cursor-not-allowed
                               @cannot('entidades.update') col-span-2 @endcannot
                               {{ $e->active
                                   ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                   : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">

                                        <span x-show="!loading">
                                            {{ $e->active ? 'Desactivar' : 'Activar' }}
                                        </span>

                                        <span x-show="loading" x-cloak>
                                            Procesando…
                                        </span>
                                    </button>
                                @endcan

                            </div>
                        @endif
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
    <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
        <table class="w-full table-fixed text-sm">

            {{-- =========================
             THEAD
        ========================== --}}
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200
                   border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left">

                    {{-- ID --}}
                    <th class="w-[80px] text-center p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Nombre --}}
                    <th class="w-[260px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Email --}}
                    <th class="hidden 2xl:table-cell w-[260px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('email')">
                        Email
                        @if ($sortField === 'email')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Teléfono --}}
                    <th class="hidden 2xl:table-cell w-[140px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('telefono')">
                        Teléfono
                        @if ($sortField === 'telefono')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Dirección --}}
                    <th class="hidden 2xl:table-cell w-[280px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('direccion')">
                        Dirección
                        @if ($sortField === 'direccion')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Estado --}}
                    <th class="text-center w-[110px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Acciones (SIEMPRE visible para evitar huecos) --}}
                    <th class="w-[120px] p-2 whitespace-nowrap text-center">
                        Acciones
                    </th>

                </tr>
            </thead>

            @forelse ($entidades as $e)
                <tbody x-data="{ open: false }" class="divide-y divide-gray-200 dark:divide-neutral-200">

                    {{-- FILA PRINCIPAL --}}
                    <tr wire:key="entidad-row-{{ $e->id }}" class="hover:bg-gray-100 dark:hover:bg-neutral-900">

                        {{-- ID + Toggle detalle (<2xl) --}}
                        <td class="p-1 whitespace-nowrap text-center" x-data="{ showToggle: !window.matchMedia('(min-width: 1536px)').matches }" x-init="const mq = window.matchMedia('(min-width: 1536px)');
                        const handler = e => showToggle = !e.matches;
                        mq.addEventListener('change', handler);">
                            <button type="button" x-show="showToggle" x-cloak
                                class="w-5 h-5 inline-flex items-center justify-center rounded
                                    border border-gray-300 text-gray-600
                                    hover:bg-gray-100 hover:text-gray-800
                                    dark:border-neutral-700 dark:text-neutral-300
                                    dark:hover:bg-neutral-700 dark:hover:text-white
                                    transition cursor-pointer"
                                @click.stop="open = !open" :aria-expanded="open">
                                <span x-show="!open">+</span>
                                <span x-show="open">−</span>
                            </button>
                            <span class="ml-1">{{ $e->id }}</span>
                        </td>

                        {{-- Nombre --}}
                        <td class="p-2 min-w-0">
                            <span class="block truncate max-w-full" title="{{ $e->nombre }}">
                                {{ $e->nombre }}
                            </span>
                        </td>

                        {{-- Email --}}
                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->email ?? '-' }}">
                                {{ $e->email ?? '-' }}
                            </span>
                        </td>

                        {{-- Teléfono  --}}
                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->telefono ?? '-' }}">
                                {{ $e->telefono ?? '-' }}
                            </span>
                        </td>

                        {{-- Dirección  --}}
                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->direccion ?? '-' }}">
                                {{ $e->direccion ?? '-' }}
                            </span>
                        </td>

                        {{-- Estado --}}
                        <td class="text-center p-2 whitespace-nowrap">
                            @if ($e->active)
                                <span
                                    class="px-2 py-1 rounded text-xs
                                       bg-green-100 text-green-800
                                       dark:bg-green-500/20 dark:text-green-200">
                                    Activo
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-xs
                                       bg-red-100 text-red-800
                                       dark:bg-red-500/20 dark:text-red-200">
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        {{-- ACCIONES (placeholder si no hay permisos) --}}
                        <td class="p-2 whitespace-nowrap">
                            @canany(['entidades.update', 'entidades.toggle'])
                                <div class="flex items-center justify-center gap-2">

                                    {{-- Editar --}}
                                    @can('entidades.update')
                                        <button wire:click="openEdit({{ $e->id }})" wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $e->id }})" title="Editar entidad"
                                            aria-label="Editar entidad"
                                            class="cursor-pointer rounded p-1
                                               hover:bg-gray-100 dark:hover:bg-neutral-800
                                               disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg wire:loading.remove wire:target="openEdit({{ $e->id }})"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>

                                            <svg wire:loading wire:target="openEdit({{ $e->id }})"
                                                class="size-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                            </svg>
                                        </button>
                                    @endcan

                                    {{-- Activar / Desactivar --}}
                                    @can('entidades.toggle')
                                        <button type="button" x-data="{ loading: false }"
                                            x-on:click="
                                                loading = true;
                                                $dispatch('swal:toggle-active-entidad', {
                                                    id: {{ $e->id }},
                                                    active: @js($e->active),
                                                    name: @js($e->nombre)
                                                });
                                            "
                                            x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                            title="{{ $e->active ? 'Desactivar entidad' : 'Activar entidad' }}"
                                            aria-label="{{ $e->active ? 'Desactivar entidad' : 'Activar entidad' }}"
                                            class="cursor-pointer inline-flex items-center justify-center size-8 rounded
                                               disabled:opacity-50 disabled:cursor-not-allowed
                                               {{ $e->active
                                                   ? 'bg-red-600 text-white hover:bg-red-700
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                   : 'bg-green-600 text-white hover:bg-green-700
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                            <span x-show="!loading">
                                                @if ($e->active)
                                                    {{-- eye-slash --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 3l18 18M10.584 10.584A2.25 2.25 0 0012 14.25 2.25 2.25 0 0014.25 12c0-.5-.167-.96-.45-1.33 M9.88 5.09A9.715 9.715 0 0112 4.5 c4.478 0 8.268 2.943 9.543 7.5 a9.66 9.66 0 01-2.486 3.95 M6.18 6.18C4.634 7.436 3.55 9.135 3 12 c1.275 4.557 5.065 7.5 9.543 7.5 1.79 0 3.487-.469 4.993-1.29" />
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
                            @else
                                {{-- Placeholder si NO tiene permisos --}}
                                <div class="text-center text-xs text-gray-400 dark:text-neutral-500 select-none">
                                    —
                                </div>
                            @endcanany
                        </td>

                    </tr>

                    {{-- DETALLE EXPANDIBLE (<2xl) --}}
                    <tr x-show="open" x-cloak
                        class="2xl:hidden bg-gray-100/60 dark:bg-neutral-900/40
                           border-b border-gray-200 dark:border-neutral-200">
                        <td class="pl-20 py-2" colspan="4">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Teléfono
                                    </span>
                                    <span class="block truncate" title="{{ $e->telefono ?? '-' }}">
                                        {{ $e->telefono ?? '-' }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Dirección
                                    </span>
                                    <span class="block truncate" title="{{ $e->direccion ?? '-' }}">
                                        {{ $e->direccion ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>

                </tbody>
            @empty
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400" colspan="4">
                            Sin resultados.
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $entidades->links() }}
    </div>

    {{-- MODAL ENTIDAD (create / update) --}}
    @canany(['entidades.create', 'entidades.update'])
        <x-ui.modal wire:key="entidades-modal" model="openModal" :title="$entidadId ? 'Editar Entidad' : 'Nueva Entidad'" maxWidth="sm:max-w-xl md:max-w-2xl"
            onClose="closeModal">
            {{-- BODY --}}
            <div class="space-y-4">
                {{-- Nombre (OBLIGATORIO) --}}
                <div>
                    <label class="block text-sm mb-1">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="nombre" autocomplete="off" placeholder="Ej: Empresa Nacional de Electricidad"
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

                {{-- Email --}}
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input wire:model="email" type="email" autocomplete="off" placeholder="contacto@entidad.com"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('email')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block text-sm mb-1">Teléfono</label>
                    <input wire:model="telefono" autocomplete="off" placeholder="Ej: 71234567"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                    @error('telefono')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div>
                    <label class="block text-sm mb-1">Dirección</label>
                    <textarea wire:model="direccion" rows="2" placeholder="Dirección física de la entidad"
                        class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                    @error('direccion')
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nota --}}
                <p class="text-xs text-gray-500 dark:text-neutral-400 pt-1">
                    <span class="text-red-500">*</span> Campo obligatorio.
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
                    class="px-4 py-2 rounded cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">
                        {{ $entidadId ? 'Actualizar' : 'Guardar' }}
                    </span>
                    <span wire:loading wire:target="save">
                        Guardando…
                    </span>
                </button>
            @endslot
        </x-ui.modal>
    @endcanany
</div>
