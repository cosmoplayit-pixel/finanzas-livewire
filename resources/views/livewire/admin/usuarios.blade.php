@section('title', 'Usuarios')
<div class="p-6 space-y-4" :title="__('Dashboard')">

    {{-- INICIO - NOMBRE DEL MODULO ACTUAL Y BOTON PARA CREAR USUARIO --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Usuarios</h1>
        <button wire:click="openCreate" class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
            Nuevo Usuario
        </button>
    </div>
    {{-- FIN - NOMBRE DEL MODULO ACTUAL Y BOTON PARA CREAR USUARIO --}}

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
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre o Correo"
            class="w-full max-w-md border rounded px-3 py-2" />

        <div class="flex items-center gap-3 ml-auto">

            <select wire:model.live="status"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0 dark:focus:ring-neutral-600">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>

            <select wire:model.live="perPage"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0 dark:focus:ring-neutral-600">
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

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('name')">
                        Nombre
                        @if ($sortField === 'name')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('email')">
                        Email
                        @if ($sortField === 'email')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('empresa_id')">
                        Empresa
                        @if ($sortField === 'empresa_id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('role')">
                        Rol
                        @if ($sortField === 'role')
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
                @foreach ($users as $u)
                    <tr class="border-t">
                        <td class="p-3">{{ $u->id }}</td>
                        <td class="p-3">{{ $u->name }}</td>
                        <td class="p-3">{{ $u->email }}</td>

                        <td class="p-3">
                            <span class="truncate block max-w-[260px]"
                                title="{{ $u->empresa?->nombre ?? 'Admin global' }}">
                                {{ $u->empresa?->nombre ?? '—' }}
                            </span>
                        </td>

                        <td class="p-3">{{ $u->getRoleNames()->first() ?? '-' }}</td>

                        <td class="p-3">
                            @if ($u->active)
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
                            <button wire:click="openEdit({{ $u->id }})"
                                class="px-3 py-1 rounded border hover:bg-gray-50">
                                Editar
                            </button>

                            @if (auth()->id() !== $u->id)
                                <button type="button"
                                    wire:click="$dispatch('swal:toggle-active', {
                                        id: {{ $u->id }},
                                        active: {{ $u->active ? 'true' : 'false' }},
                                        name: @js($u->name)
                                    })"
                                    class="px-3 py-1 rounded text-sm font-medium
                                    {{ $u->active
                                        ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                        : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                    {{ $u->active ? 'Desactivar' : 'Activar' }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if ($users->count() === 0)
                    <tr>
                        <td class="p-3" colspan="7">Sin resultados.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    {{-- FIN - TABLA --}}

    <div>
        {{ $users->links() }}
    </div>

    {{-- INICIO - MODAL --}}
    @if ($openModal)
        <div wire:key="usuarios-modal"
            class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50">
            <div
                class="w-full max-w-lg rounded-lg border overflow-hidden bg-white text-gray-700
                dark:bg-neutral-900 dark:text-neutral-200 border-gray-200 dark:border-neutral-800">

                {{-- Header --}}
                <div
                    class="px-5 py-4 flex justify-between items-center
                    bg-gray-50 dark:bg-neutral-900
                    border-b border-gray-200 dark:border-neutral-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $userId ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h2>
                    <button wire:click="closeModal"
                        class="text-gray-500 hover:text-gray-900
                        dark:text-neutral-400 dark:hover:text-white">
                        ✕
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4" x-data="{ isAdmin: (@entangle('role').live === 'Administrador') }"
                    x-effect="isAdmin = (@entangle('role').live === 'Administrador')">

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm mb-1">Nombre</label>
                        <input wire:model="name" autocomplete="off"
                            class="w-full rounded border px-3 py-2
                            bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700
                            text-gray-900 dark:text-neutral-100
                            placeholder:text-gray-400 dark:placeholder:text-neutral-500
                            focus:outline-none focus:ring-2
                            focus:ring-gray-300 dark:focus:ring-neutral-700">
                        @error('name')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Trucos anti-autocomplete --}}
                    <input type="text" name="fake_email" autocomplete="username" style="display:none">
                    <input type="password" name="fake_password" autocomplete="current-password" style="display:none">

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

                    {{-- Rol --}}
                    <div>
                        <label class="block text-sm mb-1">Rol</label>
                        <select wire:model.live="role"
                            class="w-full rounded border px-3 py-2
                            bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700
                            text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2
                            focus:ring-gray-300 dark:focus:ring-neutral-700">
                            <option value="">-- Seleccione --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Empresa --}}
                    <div>
                        <label class="block text-sm mb-1">Empresa</label>
                        <select wire:model="empresa_id" :disabled="isAdmin"
                            class="w-full rounded border px-3 py-2
                            bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700
                            text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2
                            focus:ring-gray-300 dark:focus:ring-neutral-700
                            disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="">
                                <span x-text="isAdmin ? 'Admin global (sin empresa)' : '-- Seleccione --'"></span>
                            </option>
                            @foreach ($empresas as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                            @endforeach
                        </select>

                        @error('empresa_id')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror

                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                            Para rol Administrador, la empresa se deja vacía (Admin global). Para otros roles, es
                            obligatorio.
                        </p>
                    </div>

                    {{-- Password --}}
                    <div class="pt-3 border-t border-gray-200 dark:border-neutral-800">
                        <label class="block text-sm mb-1">
                            {{ $userId ? 'Nueva contraseña (opcional)' : 'Contraseña' }}
                        </label>
                        <input wire:model="password" type="password" autocomplete="new-password"
                            class="w-full rounded border px-3 py-2
                            bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700
                            text-gray-900 dark:text-neutral-100
                            placeholder:text-gray-400 dark:placeholder:text-neutral-500
                            focus:outline-none focus:ring-2
                            focus:ring-gray-300 dark:focus:ring-neutral-700">
                        @error('password')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmación --}}
                    <div>
                        <label class="block text-sm mb-1">Confirmar contraseña</label>
                        <input wire:model="password_confirmation" type="password" autocomplete="new-password"
                            class="w-full rounded border px-3 py-2
                            bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700
                            text-gray-900 dark:text-neutral-100
                            placeholder:text-gray-400 dark:placeholder:text-neutral-500
                            focus:outline-none focus:ring-2
                            focus:ring-gray-300 dark:focus:ring-neutral-700">
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
                        {{ $userId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>

            </div>
        </div>
    @endif
    {{-- FIN - MODAL --}}

</div>
