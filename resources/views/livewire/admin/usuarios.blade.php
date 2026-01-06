@section('title', 'Usuarios')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

    @can('users.view')
        {{-- HEADER (RESPONSIVE PARA MOBILE) --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold">Usuarios</h1>

            @can('users.create')
                <button wire:click="openCreate" class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:opacity-90">
                    Nuevo Usuario
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

        {{-- FILTROS (MISMA LÍNEA EN TABLET Y DESKTOP) --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            {{-- Buscar --}}
            <input type="search" wire:model.live="search" placeholder="Buscar Nombre o Correo"
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
            @forelse ($users as $u)
                @php
                    $isRoot = (bool) ($u->is_root ?? false);
                @endphp

                <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">{{ $u->name }}</div>
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
                            <span class="text-gray-500 dark:text-neutral-400">Empresa</span>
                            <span class="font-medium truncate max-w-[60%]"
                                title="{{ $u->empresa?->nombre ?? 'Admin global' }}">
                                {{ $u->empresa?->nombre ?? '—' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500 dark:text-neutral-400">Rol</span>
                            <span class="font-medium">{{ $u->getRoleNames()->first() ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    @canany(['users.update', 'users.toggle'])
                        <div class="mt-4">
                            @if ($isRoot)
                                {{-- SOLO UNA VEZ --}}
                                <span
                                    class="block w-full px-3 py-1 rounded text-center text-xs font-medium
                                           bg-gray-200 text-gray-700
                                           dark:bg-neutral-800 dark:text-neutral-300">
                                    Admin principal
                                </span>
                            @else
                                <div class="flex gap-2">
                                    @can('users.update')
                                        <button wire:click="openEdit({{ $u->id }})"
                                            class="w-full px-3 py-1 rounded border border-gray-300 hover:bg-gray-50
                                                   dark:border-neutral-700 dark:hover:bg-neutral-800">
                                            Editar
                                        </button>
                                    @endcan

                                    @can('users.toggle')
                                        @if (auth()->id() !== $u->id)
                                            <button type="button"
                                                wire:click="$dispatch('swal:toggle-active', {
                                                    id: {{ $u->id }},
                                                    active: {{ $u->active ? 'true' : 'false' }},
                                                    name: @js($u->name)
                                                })"
                                                class="w-full px-3 py-1 rounded text-sm font-medium
                                                {{ $u->active
                                                    ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                    : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                                {{ $u->active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        @endif
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

        {{-- TABLET + DESKTOP: TABLA (visible desde md) --}}
        <div class="hidden md:block overflow-x-auto overflow-y-hidden border rounded bg-white dark:bg-neutral-800">
            <table class="w-full text-sm">
                {{-- ================= THEAD ================= --}}
                <thead
                    class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                    <tr class="text-left">
                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('id')">
                            ID
                            @if ($sortField === 'id')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>

                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('name')">
                            Nombre
                            @if ($sortField === 'name')
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

                        {{-- Empresa --}}
                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('empresa_id')">
                            Empresa
                            @if ($sortField === 'empresa_id')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>

                        {{-- Rol: oculto en tablet, visible desde lg --}}
                        <th class="p-3 cursor-pointer select-none whitespace-nowrap hidden lg:table-cell"
                            wire:click="sortBy('role')">
                            Rol
                            @if ($sortField === 'role')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>

                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('active')">
                            Estado
                            @if ($sortField === 'active')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>

                        {{-- Acciones --}}
                        @canany(['users.update', 'users.toggle'])
                            <th class="p-3 whitespace-nowrap w-40 lg:w-56">
                                Acciones
                            </th>
                        @endcanany
                    </tr>
                </thead>

                {{-- ================= TBODY ================= --}}
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    @foreach ($users as $u)
                        @php
                            $isRoot = (bool) ($u->is_root ?? false);
                        @endphp

                        <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900">
                            <td class="p-3 whitespace-nowrap">{{ $u->id }}</td>

                            {{-- Nombre --}}
                            <td class="p-3">
                                <span class="block max-w-[220px] lg:max-w-none truncate" title="{{ $u->name }}">
                                    {{ $u->name }}
                                </span>
                            </td>

                            {{-- Email --}}
                            <td class="p-3 hidden lg:table-cell">
                                <span class="block max-w-[260px] truncate" title="{{ $u->email }}">
                                    {{ $u->email }}
                                </span>
                            </td>

                            {{-- Empresa --}}
                            <td class="p-3">
                                <span class="block max-w-[180px] lg:max-w-[260px] truncate"
                                    title="{{ $u->empresa?->nombre ?? 'Admin global' }}">
                                    {{ $u->empresa?->nombre ?? '—' }}
                                </span>
                            </td>

                            {{-- Rol --}}
                            <td class="p-3 hidden lg:table-cell">
                                {{ $u->getRoleNames()->first() ?? '-' }}
                            </td>

                            {{-- Estado --}}
                            <td class="p-3 whitespace-nowrap">
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
                            </td>

                            {{-- Acciones --}}
                            @canany(['users.update', 'users.toggle'])
                                <td class="p-3 whitespace-nowrap">
                                    @if ($isRoot)
                                        {{-- SOLO UNA VEZ --}}
                                        <span
                                            class="inline-flex px-3 py-1 rounded text-xs font-medium
                                                   bg-gray-200 text-gray-700
                                                   dark:bg-neutral-800 dark:text-neutral-300">
                                            Admin principal
                                        </span>
                                    @else
                                        <div class="flex items-center gap-2">
                                            @can('users.update')
                                                <button wire:click="openEdit({{ $u->id }})"
                                                    class="px-3 py-1 cursor-pointer rounded border border-gray-300
                                                           hover:bg-gray-50
                                                           dark:border-neutral-700 dark:hover:bg-neutral-800">
                                                    Editar
                                                </button>
                                            @endcan

                                            @can('users.toggle')
                                                @if (auth()->id() !== $u->id)
                                                    <button type="button"
                                                        wire:click="$dispatch('swal:toggle-active', {
                                                            id: {{ $u->id }},
                                                            active: {{ $u->active ? 'true' : 'false' }},
                                                            name: @js($u->name)
                                                        })"
                                                        class="px-3 py-1 cursor-pointer rounded text-sm font-medium
                                                        {{ $u->active
                                                            ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                            : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                                        {{ $u->active ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    @endif
                                </td>
                            @endcanany
                        </tr>
                    @endforeach

                    @if ($users->count() === 0)
                        <tr>
                            <td colspan="{{ auth()->user()?->canAny(['users.update', 'users.toggle'])? 7: 6 }}"
                                class="p-4 text-center text-gray-500 dark:text-neutral-400">
                                Sin resultados.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- PAGINACION --}}
        <div>
            {{ $users->links() }}
        </div>

        {{-- INICIO - MODAL --}}
        @if ($openModal)
            @canany(['users.create', 'users.update'])
                <div wire:key="usuarios-modal" class="fixed inset-0 z-50">
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
                                    {{ $userId ? 'Editar Usuario' : 'Nuevo Usuario' }}
                                </h2>

                                <button type="button" wire:click="closeModal"
                                    class="inline-flex items-center justify-center size-9 rounded-md
                                           text-gray-500 hover:text-gray-900 hover:bg-gray-100
                                           dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                                    ✕
                                </button>
                            </div>

                            {{-- Body (scroll) --}}
                            <div class="p-5 space-y-4 overflow-y-auto
                                    h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]"
                                x-data="{ isAdmin: (@entangle('role').live === 'Administrador') }" x-effect="isAdmin = (@entangle('role').live === 'Administrador')">

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
                                <input type="password" name="fake_password" autocomplete="current-password"
                                    style="display:none">

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

                                {{-- Rol --}}
                                @can('users.assign_role')
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
                                @endcan

                                {{-- Empresa (OCULTAR si es Administrador) --}}
                                @if ($role !== 'Administrador')
                                    <div>
                                        <label class="block text-sm mb-1">Empresa</label>
                                        <select wire:model.live="empresa_id"
                                            class="w-full rounded border px-3 py-2
                                               bg-white dark:bg-neutral-900
                                               border-gray-300 dark:border-neutral-700
                                               text-gray-900 dark:text-neutral-100
                                               focus:outline-none focus:ring-2
                                               focus:ring-gray-300 dark:focus:ring-neutral-700">
                                            <option value="">-- Seleccione --</option>
                                            @foreach ($empresas as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                            @endforeach
                                        </select>

                                        @error('empresa_id')
                                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                        @enderror

                                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                                            Para roles distintos de Administrador, la empresa es obligatoria.
                                        </p>
                                    </div>
                                @endif

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
                                    {{ $userId ? 'Actualizar' : 'Guardar' }}
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            @endcanany
        @endif
        {{-- FIN - MODAL --}}
    @endcan

    @cannot('users.view')
        <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
            No tiene permisos para ver el módulo de Usuarios.
        </div>
    @endcannot

</div>
