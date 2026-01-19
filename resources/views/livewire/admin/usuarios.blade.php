@section('title', 'Usuarios')

<div class="p-0 md:p-6 space-y-4">

    @can('users.view')
        {{-- HEADER (RESPONSIVE PARA MOBILE) --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold">Usuarios</h1>

            @can('users.create')
                <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                    class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:bg-gray-800 hover:text-white transition-colors duration-150
                     cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="openCreate"> Nuevo Usuario </span>
                    <span wire:loading wire:target="openCreate"> Abriendo… </span>
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
                    class="cursor-pointer w-full sm:w-auto
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
                    class="cursor-pointer w-full sm:w-auto
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
                                        <button wire:click="openEdit({{ $u->id }})" wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $u->id }})"
                                            class="w-full px-3 py-1 rounded border border-gray-300
                                            cursor-pointer
                                            hover:bg-gray-50
                                            dark:border-neutral-700 dark:hover:bg-neutral-800
                                            disabled:opacity-50 disabled:cursor-not-allowed">

                                            {{-- Texto normal --}}
                                            <span wire:loading.remove wire:target="openEdit({{ $u->id }})">
                                                Editar
                                            </span>

                                            {{-- Texto loading --}}
                                            <span wire:loading wire:target="openEdit({{ $u->id }})">
                                                Abriendo…
                                            </span>
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

                        <tr wire:key="{{ $u->id }}"
                            class="max-w-[50px] hover:bg-gray-100 dark:hover:bg-neutral-900">
                            <td class="p-3 whitespace-nowrap">{{ $u->id }}</td>

                            {{-- Nombre --}}
                            <td class="p-3">
                                <span class="block max-w-[220px] lg:max-w-none truncate" title="{{ $u->name }}">
                                    {{ $u->name }}
                                </span>
                            </td>

                            {{-- Email --}}
                            <td class="p-3 hidden lg:table-cell">
                                <span class="block max-w-[250px] truncate" title="{{ $u->email }}">
                                    {{ $u->email }}
                                </span>
                            </td>

                            {{-- Empresa --}}
                            <td class="p-3">
                                <span class="block max-w-[150px] lg:max-w-[260px] truncate"
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
                                                <button wire:click="openEdit({{ $u->id }})" wire:loading.attr="disabled"
                                                    wire:target="openEdit({{ $u->id }})"
                                                    class="w-full px-3 py-1 rounded border border-gray-300
                                                    cursor-pointer
                                                    hover:bg-gray-50
                                                    dark:border-neutral-700 dark:hover:bg-neutral-800
                                                    disabled:opacity-50 disabled:cursor-not-allowed">

                                                    {{-- Texto normal --}}
                                                    <span wire:loading.remove wire:target="openEdit({{ $u->id }})">
                                                        Editar
                                                    </span>

                                                    {{-- Texto loading --}}
                                                    <span wire:loading wire:target="openEdit({{ $u->id }})">
                                                        Abriendo…
                                                    </span>
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

        {{-- INICIO - MODAL (reutilizando x-ui.modal) --}}
        @canany(['users.create', 'users.update'])
            <x-ui.modal model="openModal" :title="$userId ? 'Editar Usuario' : 'Nuevo Usuario'" maxWidth="md:max-w-2xl" onClose="closeModal">
                {{-- BODY --}}
                <div x-data="{ isAdmin: (@entangle('role').live === 'Administrador') }" x-effect="isAdmin = (@entangle('role').live === 'Administrador')"
                    class="space-y-4">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" autocomplete="off" placeholder="Ej: Juan Pérez"
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
                        <label class="block text-sm mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" name="email" autocomplete="off" autocapitalize="off"
                            spellcheck="false" inputmode="email" placeholder="Ej: usuario@dominio.com"
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
                            <label class="block text-sm mb-1">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="role"
                                class="cursor-pointer w-full rounded border px-3 py-2
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

                    {{-- Empresa (OBLIGATORIA si NO es Administrador) --}}
                    @if ($role !== 'Administrador')
                        <div>
                            <label class="block text-sm mb-1">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="empresa_id"
                                class="cursor-pointer w-full rounded border px-3 py-2
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
                    <div x-data="{ show: false }" class="pt-3 border-t border-gray-200 dark:border-neutral-800">
                        <label class="block text-sm mb-1">
                            {{ $userId ? 'Nueva contraseña (opcional)' : 'Contraseña' }}
                            @if (!$userId)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" wire:model="password" autocomplete="new-password"
                                placeholder="{{ $userId ? 'Dejar vacío para mantener la contraseña actual' : 'Mínimo 8 caracteres' }}"
                                class="w-full rounded border px-3 py-2 pr-10
                               bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700
                               text-gray-900 dark:text-neutral-100
                               placeholder:text-gray-400 dark:placeholder:text-neutral-500
                               focus:outline-none focus:ring-2
                               focus:ring-gray-300 dark:focus:ring-neutral-700">

                            {{-- Ojo --}}
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 px-3 flex items-center
                               text-gray-500 dark:text-neutral-400
                               hover:text-gray-700 dark:hover:text-neutral-200"
                                tabindex="-1">
                                {{-- Ojo cerrado --}}
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12s3.6-7 9-7 9 7 9 7-3.6 7-9 7-9-7-9-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>

                                {{-- Ojo abierto --}}
                                <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19 c-5.4 0-9-7-9-7a18.9 18.9 0 014.125-4.825M9.9 4.5 A9.956 9.956 0 0112 4 c5.4 0 9 7 9 7 a18.9 18.9 0 01-4.2 4.9M15 12 a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmación --}}
                    <div x-data="{ show: false }">
                        <label class="block text-sm mb-1">
                            Confirmar contraseña
                            @if (!$userId)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" wire:model="password_confirmation"
                                autocomplete="new-password"
                                placeholder="{{ $userId ? 'Solo si estás cambiando la contraseña' : 'Repite la contraseña' }}"
                                class="w-full rounded border px-3 py-2 pr-10
                               bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700
                               text-gray-900 dark:text-neutral-100
                               placeholder:text-gray-400 dark:placeholder:text-neutral-500
                               focus:outline-none focus:ring-2
                               focus:ring-gray-300 dark:focus:ring-neutral-700">

                            {{-- Ojo --}}
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 px-3 flex items-center
                               text-gray-500 dark:text-neutral-400
                               hover:text-gray-700 dark:hover:text-neutral-200"
                                tabindex="-1">
                                {{-- Ojo cerrado --}}
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12s3.6-7 9-7 9 7 9 7-3.6 7-9 7-9-7-9-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>

                                {{-- Ojo abierto --}}
                                <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19 c-5.4 0-9-7-9-7a18.9 18.9 0 014.125-4.825M9.9 4.5 A9.956 9.956 0 0112 4 c5.4 0 9 7 9 7 a18.9 18.9 0 01-4.2 4.9M15 12 a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        @error('password_confirmation')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nota de obligatorios --}}
                    <p class="text-xs text-gray-500 dark:text-neutral-400 pt-2">
                        <span class="text-red-500">*</span> Campos obligatorios.
                    </p>
                </div>

                {{-- FOOTER --}}
                @slot('footer')
                    <button type="button" wire:click="closeModal"
                        class="cursor-pointer px-4 py-2 rounded border border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800">
                        Cancelar
                    </button>

                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="cursor-pointer px-4 py-2 rounded bg-gray-900 text-white hover:opacity-90
                       dark:bg-white dark:text-black disabled:opacity-50 disabled:cursor-not-allowed">
                        {{-- Texto normal --}}
                        <span wire:loading.remove wire:target="save">
                            {{ $userId ? 'Actualizar' : 'Guardar' }}
                        </span>

                        {{-- Texto loading --}}
                        <span wire:loading wire:target="save">
                            Guardando…
                        </span>
                    </button>
                @endslot
            </x-ui.modal>
        @endcanany
        {{-- FIN - MODAL --}}

    @endcan

    @cannot('users.view')
        <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
            No tiene permisos para ver el módulo de Usuarios.
        </div>
    @endcannot

</div>
