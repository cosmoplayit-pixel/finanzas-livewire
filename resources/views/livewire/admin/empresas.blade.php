@section('title', 'Empresas')
<div class="p-6 space-y-4" :title="__('Dashboard')">

    {{-- INICIO - NOMBRE DEL MODULO ACTUAL Y BOTON PARA CREAR EMPRESA --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Empresas</h1>
        <button wire:click="openCreate" class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
            Nueva Empresa
        </button>
    </div>
    {{-- FIN - NOMBRE DEL MODULO ACTUAL Y BOTON PARA CREAR EMPRESA --}}

    {{-- INICIO - ALERTAS --}}
    @if (session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif
    {{-- FIN - ALERTAS --}}

    {{-- INICIO - FILTROS --}}
    <div class="flex items-center gap-3">
        <!-- Search a la izquierda -->
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre, NIT o Correo"
            class="w-full max-w-md border rounded px-3 py-2" />

        <!-- Selects a la derecha -->
        <div class="flex items-center gap-3 ml-auto">
            <select wire:model.live="status"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0
                dark:focus:ring-neutral-600">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>

            <select wire:model.live="perPage"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0
                dark:focus:ring-neutral-600">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    {{-- FIN - FILTROS --}}

    {{-- INICIO - TABLA --}}
    <div class="overflow-x-auto border rounded">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200">
                <tr class="text-left">
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('nit')">
                        NIT
                        @if ($sortField === 'nit')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('email')">
                        Email
                        @if ($sortField === 'email')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 w-56">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($empresas as $e)
                    <tr class="border-t">
                        <td class="p-3">{{ $e->id }}</td>

                        <td class="p-3">
                            <span class="truncate block max-w-[320px]" title="{{ $e->nombre }}">
                                {{ $e->nombre }}
                            </span>
                        </td>

                        <td class="p-3">{{ $e->nit ?? '-' }}</td>
                        <td class="p-3">{{ $e->email ?? '-' }}</td>

                        <td class="p-3">
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

                        <td class="p-3 space-x-2">
                            {{-- Editar --}}
                            <button wire:click="openEdit({{ $e->id }})"
                                class="px-3 py-1 rounded border hover:bg-gray-50">
                                Editar
                            </button>

                            {{-- Activar / Desactivar (SweetAlert) --}}
                            <button type="button"
                                wire:click="$dispatch('swal:toggle-active-empresa', {
                                    id: {{ $e->id }},
                                    active: {{ $e->active ? 'true' : 'false' }},
                                    name: @js($e->nombre)
                                })"
                                class="px-3 py-1 rounded text-sm font-medium
                                {{ $e->active
                                    ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                    : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                {{ $e->active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </td>
                    </tr>
                @endforeach

                @if ($empresas->count() === 0)
                    <tr>
                        <td class="p-3" colspan="6">Sin resultados.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    {{-- FIN - TABLA --}}

    <div>
        {{ $empresas->links() }}
    </div>

    {{-- INICIO - MODAL --}}
    @if ($openModal)
        <div wire:key="empresas-modal"
            class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50">
            <div
                class="w-full max-w-lg rounded-lg border overflow-hidden bg-white text-gray-700
                dark:bg-neutral-900 dark:text-neutral-200
                border-gray-200 dark:border-neutral-800">

                {{-- Header --}}
                <div
                    class="px-5 py-4 flex justify-between items-center
                    bg-gray-50 dark:bg-neutral-900
                    border-b border-gray-200 dark:border-neutral-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $empresaId ? 'Editar Empresa' : 'Nueva Empresa' }}
                    </h2>
                    <button wire:click="closeModal"
                        class="text-gray-500 hover:text-gray-900
                        dark:text-neutral-400 dark:hover:text-white">
                        ✕
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4">

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
                    <input type="password" name="fake_password" autocomplete="current-password" style="display:none">

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
                        <input wire:model="email" type="email" name="email" autocomplete="off" autocapitalize="off"
                            spellcheck="false" inputmode="email"
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

                {{-- Footer --}}
                <div
                    class="px-5 py-4 flex justify-end gap-2
                    bg-gray-50 dark:bg-neutral-900
                    border-t border-gray-200 dark:border-neutral-800">
                    <button wire:click="closeModal"
                        class="px-4 py-2 rounded border
                        border-gray-300 dark:border-neutral-700
                        text-gray-700 dark:text-neutral-200
                        hover:bg-gray-100 dark:hover:bg-neutral-800">
                        Cancelar
                    </button>
                    <button wire:click="save"
                        class="px-4 py-2 rounded
                        bg-gray-900 text-white hover:opacity-90
                        dark:bg-white dark:text-black">
                        {{ $empresaId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>

            </div>
        </div>
    @endif
    {{-- FIN - MODAL --}}

</div>
