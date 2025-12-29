@section('title', 'Empresas')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

    {{-- HEADER (RESPONSIVE PARA MOBILE) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Empresas</h1>

        <button wire:click="openCreate" class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:opacity-90">
            Nueva Empresa
        </button>
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

    {{-- FILTROS (MISMA LÍNEA EN TABLET Y DESKTOP) --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
        {{-- Buscar --}}
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre, NIT o Correo"
            class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

        {{-- Selects --}}
        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">
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
        @forelse ($empresas  as $u)
            <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $u->nombre }}</div>
                        <div class="text-sm text-gray-600 dark:text-neutral-300 truncate">{{ $u->email }}</div>
                    </div>

                    <div class="shrink-0">
                        @if ($u->active)
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
                        <span class="font-medium">{{ $u->id }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="text-gray-500 dark:text-neutral-400">NIT</span>
                        <span class="font-medium">{{ $u->nit ?? '—' }}</span>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-2">
                    <button wire:click="openEdit({{ $u->id }})"
                        class="w-full px-3 py-2 rounded border border-gray-300 hover:bg-gray-50
                               dark:border-neutral-700 dark:hover:bg-neutral-800">
                        Editar
                    </button>

                    <button type="button"
                        wire:click="$dispatch('swal:toggle-active-empresa', {
                            id: {{ $u->id }},
                            active: {{ $u->active ? 'true' : 'false' }},
                            name: @js($u->nombre)
                        })"
                        class="w-full px-3 py-2 rounded text-sm font-medium
                        {{ $u->active
                            ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                            : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                        {{ $u->active ? 'Desactivar' : 'Activar' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- TABLET + DESKTOP: TABLA (visible desde md) --}}
    <div
        class="hidden md:block
           overflow-x-auto overflow-y-hidden
           border rounded
           bg-white dark:bg-neutral-800">
        <table class="w-full text-sm">
            {{-- ================= THEAD ================= --}}
            <thead
                class="bg-gray-50 text-gray-700
                   dark:bg-neutral-900 dark:text-neutral-200
                   border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left">
                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- NIT: visible siempre --}}
                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('nit')">
                        NIT
                        @if ($sortField === 'nit')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Email: oculto en tablet, visible desde lg --}}
                    <th class="p-3 cursor-pointer select-none whitespace-nowrap hidden lg:table-cell"
                        wire:click="sortBy('email')">
                        Email
                        @if ($sortField === 'email')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 whitespace-nowrap w-40 lg:w-56">
                        Acciones
                    </th>
                </tr>
            </thead>

            {{-- ================= TBODY ================= --}}
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                @foreach ($empresas as $e)
                    <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900">
                        <td class="p-3 whitespace-nowrap">{{ $e->id }}</td>

                        {{-- Nombre --}}
                        <td class="p-3">
                            <span class="block max-w-[260px] lg:max-w-none truncate" title="{{ $e->nombre }}">
                                {{ $e->nombre }}
                            </span>
                        </td>

                        {{-- NIT --}}
                        <td class="p-3 whitespace-nowrap">
                            <span class="block max-w-[180px] truncate" title="{{ $e->nit }}">
                                {{ $e->nit ?? '—' }}
                            </span>
                        </td>

                        {{-- Email --}}
                        <td class="p-3 hidden lg:table-cell">
                            <span class="block max-w-[260px] truncate" title="{{ $e->email }}">
                                {{ $e->email ?? '—' }}
                            </span>
                        </td>

                        {{-- Estado --}}
                        <td class="p-3 whitespace-nowrap">
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

                        {{-- Acciones --}}
                        <td class="p-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <button wire:click="openEdit({{ $e->id }})"
                                    class="px-3 py-1 cursor-pointer rounded border border-gray-300
                                       hover:bg-gray-50
                                       dark:border-neutral-700 dark:hover:bg-neutral-800">
                                    Editar
                                </button>

                                <button type="button"
                                    wire:click="$dispatch('swal:toggle-active-empresa', {
                                        id: {{ $e->id }},
                                        active: {{ $e->active ? 'true' : 'false' }},
                                        name: @js($e->nombre)
                                    })"
                                    class="px-3 py-1 cursor-pointer rounded text-sm font-medium
                                    {{ $e->active
                                        ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                        : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                    {{ $e->active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if ($empresas->count() === 0)
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500 dark:text-neutral-400">
                            Sin resultados.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- PAGINACION --}}
    <div>
        {{ $empresas->links() }}
    </div>

    {{-- INICIO - MODAL --}}
    @if ($openModal)
        <div wire:key="empresas-modal" class="fixed inset-0 z-50">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closeModal"></div>

            {{-- Dialog --}}
            <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div
                    class="
                    w-full
                    h-[100dvh] sm:h-auto
                    sm:max-h-[90vh]
                    sm:max-w-xl md:max-w-2xl
                    bg-white dark:bg-neutral-900
                    text-gray-700 dark:text-neutral-200
                    border border-gray-200 dark:border-neutral-800
                    rounded-none sm:rounded-xl
                    overflow-hidden
                    shadow-xl
                ">

                    {{-- Header (sticky) --}}
                    <div
                        class="sticky top-0 z-10 px-5 py-4 flex justify-between items-center
                           bg-gray-50 dark:bg-neutral-900
                           border-b border-gray-200 dark:border-neutral-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $empresaId ? 'Editar Empresa' : 'Nueva Empresa' }}
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
                                   focus:ring-gray-300 dark:focus:ring-neutral-700">
                            @error('nombre')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Trucos anti-autocomplete --}}
                        <input type="text" name="fake_email" autocomplete="username" style="display:none">
                        <input type="password" name="fake_password" autocomplete="current-password"
                            style="display:none">

                        {{-- NIT --}}
                        <div>
                            <label class="block text-sm mb-1">NIT</label>
                            <input wire:model="nit" autocomplete="off"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700">
                            @error('nit')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm mb-1">Email</label>
                            <input wire:model="email" type="email" name="email" autocomplete="off"
                                autocapitalize="off" spellcheck="false" inputmode="email"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700">
                            @error('email')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- Footer (sticky) --}}
                    <div
                        class="sticky bottom-0 z-10 px-5 py-4 flex gap-2
                           bg-gray-50 dark:bg-neutral-900
                           border-t border-gray-200 dark:border-neutral-800">
                        <button wire:click="closeModal"
                            class="w-1/2 px-4 py-2 rounded border
                               border-gray-300 dark:border-neutral-700
                               text-gray-700 dark:text-neutral-200
                               hover:bg-gray-100 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>

                        <button wire:click="save"
                            class="w-1/2 px-4 py-2 rounded
                               bg-gray-900 text-white hover:opacity-90
                               dark:bg-white dark:text-black">
                            {{ $empresaId ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
    {{-- FIN - MODAL --}}

</div>
