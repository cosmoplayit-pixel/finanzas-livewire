@section('title', 'Entidades')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

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
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre, Sigla o Correo"
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
            <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $e->nombre }}</div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1">
                            {{ $e->sigla ?? '—' }}
                        </div>
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
                </div>

                {{-- Acciones --}}
                @canany(['entidades.update', 'entidades.toggle'])
                    <div class="mt-4 flex gap-2">
                        @can('entidades.update')
                            <button wire:click="openEdit({{ $e->id }})"
                                class="w-full px-3 py-1 rounded border border-gray-300 hover:bg-gray-50
                                       dark:border-neutral-700 dark:hover:bg-neutral-800">
                                Editar
                            </button>
                        @endcan

                        @can('entidades.toggle')
                            <button type="button"
                                wire:click="$dispatch('swal:toggle-active-entidad', {
                                    id: {{ $e->id }},
                                    active: @js($e->active),
                                    name: @js($e->nombre)
                                })"
                                class="w-full px-3 py-1 rounded text-sm font-medium
                                {{ $e->active
                                    ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                    : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                {{ $e->active ? 'Desactivar' : 'Activar' }}
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

    {{-- TABLET + DESKTOP: TABLA (plantilla tipo Proyectos) --}}
    <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
        <table class="w-full table-fixed text-sm">

            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left">

                    <th class="w-[70px] text-center p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="w-[300px] text-center p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="hidden lg:table-cell w-[110px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('sigla')">
                        Sigla
                        @if ($sortField === 'sigla')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>


                    <th class="hidden lg:table-cell w-[250px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('email')">
                        Email
                        @if ($sortField === 'email')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>


                    <th class="w-[120px] p-2 cursor-pointer select-none whitespace-nowrap hidden 2xl:table-cell"
                        wire:click="sortBy('telefono')">
                        Teléfono
                        @if ($sortField === 'telefono')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="w-[280px] p-2 cursor-pointer select-none whitespace-nowrap hidden 2xl:table-cell"
                        wire:click="sortBy('direccion')">
                        Dirección
                        @if ($sortField === 'direccion')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>


                    <th class="text-center w-[95px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    @canany(['entidades.update', 'entidades.toggle'])
                        <th class="w-[120px] p-2 whitespace-nowrap text-center">
                            Acciones
                        </th>
                    @endcanany
                </tr>
            </thead>

            @foreach ($entidades as $e)
                <tbody x-data="{ open: false }" class="divide-y divide-gray-200 dark:divide-neutral-200">

                    <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900">

                        {{-- ID + Toggle detalle (solo <2xl) --}}
                        <td class="p-1 whitespace-nowrap text-center" x-data="{ showToggle: !window.matchMedia('(min-width: 1536px)').matches }" x-init="const mq = window.matchMedia('(min-width: 1536px)');
                        const handler = e => showToggle = !e.matches;
                        mq.addEventListener('change', handler);">
                            <button type="button" x-show="showToggle" x-cloak
                                class="w-5 h-5 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100 hover:text-gray-800
                                       dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white transition cursor-pointer"
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

                        <td class="p-2 whitespace-nowrap hidden lg:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->sigla ?? '-' }}">
                                {{ $e->sigla ?? '-' }}
                            </span>
                        </td>

                        <td class="p-2 whitespace-nowrap hidden lg:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->email ?? '-' }}">
                                {{ $e->email ?? '-' }}
                            </span>
                        </td>


                        {{-- Teléfono (2xl) --}}
                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->telefono ?? '-' }}">
                                {{ $e->telefono ?? '-' }}
                            </span>
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            <span class="block truncate max-w-full" title="{{ $e->direccion ?? '-' }}">
                                {{ $e->direccion ?? '-' }}
                            </span>
                        </td>


                        {{-- Estado --}}
                        <td class="text-center p-2 whitespace-nowrap">
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
                        </td>

                        {{-- Acciones (íconos como Proyectos) --}}
                        @canany(['entidades.update', 'entidades.toggle'])
                            <td class="p-2 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">

                                    @can('entidades.update')
                                        <button wire:click="openEdit({{ $e->id }})" title="Editar entidad"
                                            class="cursor-pointer rounded dark:border-neutral-700 dark:hover:bg-neutral-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                    @endcan

                                    @can('entidades.toggle')
                                        <button type="button"
                                            title="{{ $e->active ? 'Desactivar entidad' : 'Activar entidad' }}"
                                            wire:click="$dispatch('swal:toggle-active-entidad', {
                                                id: {{ $e->id }},
                                                active: @js($e->active),
                                                name: @js($e->nombre)
                                            })"
                                            class="cursor-pointer inline-flex items-center justify-center size-8 rounded
                                            {{ $e->active
                                                ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">

                                            @if ($e->active)
                                                {{-- Heroicon: eye-slash (Desactivar) --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 3l18 18M10.584 10.584A2.25 2.25 0 0012 14.25 2.25 2.25 0 0014.25 12c0-.5-.167-.96-.45-1.33M9.88 5.09 A9.715 9.715 0 0112 4.5c4.478 0 8.268 2.943 9.543 7.5 a9.66 9.66 0 01-2.486 3.95M6.18 6.18 C4.634 7.436 3.55 9.135 3 12 c1.275 4.557 5.065 7.5 9.543 7.5 1.79 0 3.487-.469 4.993-1.29" />
                                                </svg>
                                            @else
                                                {{-- Heroicon: eye (Activar) --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 010-.639 C3.423 7.51 7.36 4.5 12 4.5 c4.638 0 8.573 3.007 9.963 7.178 .07.207.07.431 0 .639 C20.577 16.49 16.64 19.5 12 19.5 c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        @endcanany

                    </tr>

                    {{-- ✅ Detalle expandible SOLO cuando NO es 2xl y con 4 columnas --}}
                    @php
                        $colspan =
                            5 +
                            (auth()->user()->can('entidades.update') || auth()->user()->can('entidades.toggle')
                                ? 1
                                : 0);
                    @endphp


                    <tr x-show="open" x-cloak
                        class="2xl:hidden bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">
                        <td class="pl-20 py-1.5" colspan="{{ $colspan }}">
                            <div class="grid grid-cols-2 md:grid-cols-2 gap-1 text-sm">

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Teléfono
                                    </span>
                                    <span class="block truncate">
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
            @endforeach

            @if ($entidades->count() === 0)
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400" colspan="7">
                            Sin resultados.
                        </td>
                    </tr>
                </tbody>
            @endif

        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $entidades->links() }}
    </div>

    {{-- MODAL (solo create/update) --}}
    @canany(['entidades.create', 'entidades.update'])
        @if ($openModal)
            <div wire:key="entidades-modal" class="fixed inset-0 z-50">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closeModal"></div>

                {{-- Dialog --}}
                <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4">
                    <div
                        class="w-full
                           h-[100dvh] sm:h-auto
                           sm:max-h-[90vh]
                           sm:max-w-xl md:max-w-2xl
                           bg-white dark:bg-neutral-900
                           text-gray-700 dark:text-neutral-200
                           border border-gray-200 dark:border-neutral-800
                           rounded-none sm:rounded-xl
                           overflow-hidden shadow-xl">

                        {{-- Header (sticky) --}}
                        <div
                            class="sticky top-0 z-10 px-5 py-4 flex justify-between items-center
                               bg-gray-50 dark:bg-neutral-900
                               border-b border-gray-200 dark:border-neutral-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                                {{ $entidadId ? 'Editar Entidad' : 'Nueva Entidad' }}
                            </h2>

                            <button type="button" wire:click="closeModal"
                                class="inline-flex items-center justify-center size-9 rounded-md
                                   text-gray-500 hover:text-gray-900 hover:bg-gray-100
                                   dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                                ✕
                            </button>
                        </div>

                        {{-- Body (scroll) --}}
                        <div
                            class="p-5 space-y-4 overflow-y-auto
                               h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]">

                            {{-- Nombre --}}
                            <div>
                                <label class="block text-sm mb-1">Nombre</label>
                                <input wire:model="nombre" autocomplete="off"
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

                            {{-- Sigla --}}
                            <div>
                                <label class="block text-sm mb-1">Sigla</label>
                                <input wire:model="sigla" autocomplete="off"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('sigla')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm mb-1">Email</label>
                                <input wire:model="email" type="email" autocomplete="off"
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
                                <input wire:model="telefono" autocomplete="off"
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
                                <textarea wire:model="direccion" rows="2"
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

                            {{-- Observaciones --}}
                            <div>
                                <label class="block text-sm mb-1">Observaciones</label>
                                <textarea wire:model="observaciones" rows="2"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                                @error('observaciones')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        {{-- Footer (sticky) --}}
                        <div
                            class="sticky bottom-0 px-5 py-4 flex justify-end gap-2
                               bg-gray-50 dark:bg-neutral-900
                               border-t border-gray-200 dark:border-neutral-800">
                            <button wire:click="closeModal"
                                class="px-4 py-2 rounded border
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-700 dark:text-neutral-200
                                   hover:bg-gray-100 dark:hover:bg-neutral-800">
                                Cancelar
                            </button>

                            <button wire:click="save" class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
                                {{ $entidadId ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    @endcanany

</div>
