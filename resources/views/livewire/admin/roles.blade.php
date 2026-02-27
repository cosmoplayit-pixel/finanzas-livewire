@section('title', 'Roles')

<div class="p-0 md:p-6 space-y-4">

    @can('roles.view')
        {{-- HEADER --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold">Roles</h1>
            @can('roles.create')
                <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                    class="w-full sm:w-auto px-4 py-2 rounded
                bg-black text-white
                hover:bg-gray-800 hover:text-white
                transition-colors duration-150
                cursor-pointer
                disabled:opacity-50 disabled:cursor-not-allowed">

                    {{-- Texto normal --}}
                    <span wire:loading.remove wire:target="openCreate">
                        Nuevo Rol
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
            <input type="search" wire:model.live="search" placeholder="Buscar Rol o Descripción"
                class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

            <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">
                <select wire:model.live="type"
                    class="w-full sm:w-auto border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-offset-0
                       focus:ring-gray-300 dark:focus:ring-neutral-600">
                    <option value="all">Todos</option>
                    <option value="system">Sistema</option>
                    <option value="custom">Personalizados</option>
                </select>

                <select wire:model.live="status"
                    class="w-full sm:w-auto border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-offset-0
                       focus:ring-gray-300 dark:focus:ring-neutral-600">
                    <option value="all">Todos</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </select>

                <select wire:model.live="perPage"
                    class="w-full sm:w-auto border rounded px-3 py-2 bg-white text-gray-900 border-gray-300
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
            @forelse ($roles as $r)
                <div wire:key="{{ $r->id }}"
                    class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">{{ $r->name }}</div>

                        </div>

                        <div class="shrink-0 flex  gap-2 items-end">
                            <span
                                class="px-2 py-1 rounded text-xs
                            {{ $r->is_system ? 'bg-gray-200 text-gray-800 dark:bg-neutral-700 dark:text-neutral-100' : 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200' }}">
                                {{ $r->is_system ? 'Sistema' : 'Personalizado' }}
                            </span>

                            <span
                                class="px-2 py-1 rounded text-xs
                            {{ $r->active ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200' }}">
                                {{ $r->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500 dark:text-neutral-400">ID</span>
                            <span class="font-medium">{{ $r->id }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500 dark:text-neutral-400 ">Descripción</span>
                            <span class="font-medium truncate">{{ $r->description ?? '—' }}</span>
                        </div>
                    </div>

                    @canany(['roles.update', 'roles.assign_permissions', 'roles.toggle'])
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @if (!$r->is_system)
                                {{-- EDITAR --}}
                                @can('roles.update')
                                    <button wire:click="openEdit({{ $r->id }})" wire:loading.attr="disabled"
                                        wire:target="openEdit({{ $r->id }})"
                                        class="w-full px-3 py-1 rounded border border-gray-300
                           cursor-pointer hover:bg-gray-50
                           dark:border-neutral-700 dark:hover:bg-neutral-800
                           disabled:opacity-50 disabled:cursor-not-allowed">

                                        <span wire:loading.remove wire:target="openEdit({{ $r->id }})">Editar</span>
                                        <span wire:loading wire:target="openEdit({{ $r->id }})">Abriendo…</span>
                                    </button>
                                @endcan

                                {{-- PERMISOS --}}
                                @can('roles.assign_permissions')
                                    <button wire:click="openPermissions({{ $r->id }})" wire:loading.attr="disabled"
                                        wire:target="openPermissions({{ $r->id }})"
                                        class="w-full px-3 py-1 rounded border border-gray-300
                           cursor-pointer hover:bg-gray-50
                           dark:border-neutral-700 dark:hover:bg-neutral-800
                           disabled:opacity-50 disabled:cursor-not-allowed">

                                        <span wire:loading.remove
                                            wire:target="openPermissions({{ $r->id }})">Permisos</span>
                                        <span wire:loading wire:target="openPermissions({{ $r->id }})">Cargando…</span>
                                    </button>
                                @endcan

                                {{-- ACTIVAR / DESACTIVAR --}}
                                @can('roles.toggle')
                                    <button type="button"
                                        wire:click="$dispatch('swal:toggle-active-rol', {
                        id: {{ $r->id }},
                        active: @js($r->active),
                        name: @js($r->name)
                    })"
                                        class="w-full px-3 py-1 cursor-pointer rounded text-sm font-medium
                           disabled:opacity-50 disabled:cursor-not-allowed
                        {{ $r->active
                            ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                            : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                        {{ $r->active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                @endcan
                            @else
                                <div class="col-span-3 text-xs text-gray-500 dark:text-neutral-400">
                                    Rol del sistema: bloqueado.
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

                        <th class="p-3 whitespace-nowrap hidden lg:table-cell">
                            Descripción
                        </th>

                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('is_system')">
                            Tipo
                            @if ($sortField === 'is_system')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>

                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('active')">
                            Estado
                            @if ($sortField === 'active')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>
                        @canany(['roles.update', 'roles.assign_permissions', 'roles.toggle'])
                            <th class="p-3 whitespace-nowrap w-56">
                                Acciones
                            </th>
                        @endcanany
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    @foreach ($roles as $r)
                        <tr wire:key="{{ $r->id }}" class="hover:bg-gray-100 dark:hover:bg-neutral-900">
                            <td class="p-3 whitespace-nowrap">{{ $r->id }}</td>

                            <td class="p-3">
                                <span class="block max-w-[280px] truncate" title="{{ $r->name }}">
                                    {{ $r->name }}
                                </span>
                            </td>

                            <td class="p-3 hidden lg:table-cell">
                                <span class="block max-w-[420px] truncate" title="{{ $r->description }}">
                                    {{ $r->description ?? '—' }}
                                </span>
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 rounded text-xs
                                {{ $r->is_system ? 'bg-gray-200 text-gray-800 dark:bg-neutral-700 dark:text-neutral-100' : 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200' }}">
                                    {{ $r->is_system ? 'Sistema' : 'Personalizado' }}
                                </span>
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 rounded text-xs
                                {{ $r->active ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200' }}">
                                    {{ $r->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                @if (!$r->is_system)
                                    <div class="flex items-center gap-2">

                                        {{-- EDITAR --}}
                                        @can('roles.update')
                                            <button wire:click="openEdit({{ $r->id }})" wire:loading.attr="disabled"
                                                wire:target="openEdit({{ $r->id }})"
                                                class="px-3 py-1 rounded border border-gray-300
                                            cursor-pointer
                                            hover:bg-gray-50
                                            dark:border-neutral-700 dark:hover:bg-neutral-800
                                            disabled:opacity-50 disabled:cursor-not-allowed">

                                                <span wire:loading.remove wire:target="openEdit({{ $r->id }})">
                                                    Editar
                                                </span>

                                                <span wire:loading wire:target="openEdit({{ $r->id }})">
                                                    Abriendo…
                                                </span>
                                            </button>
                                        @endcan

                                        {{-- PERMISOS --}}
                                        @can('roles.assign_permissions')
                                            <button wire:click="openPermissions({{ $r->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="openPermissions({{ $r->id }})"
                                                class="px-3 py-1 rounded border border-gray-300
                           cursor-pointer
                           hover:bg-gray-50
                           dark:border-neutral-700 dark:hover:bg-neutral-800
                           disabled:opacity-50 disabled:cursor-not-allowed">

                                                <span wire:loading.remove wire:target="openPermissions({{ $r->id }})">
                                                    Permisos
                                                </span>

                                                <span wire:loading wire:target="openPermissions({{ $r->id }})">
                                                    Cargando…
                                                </span>
                                            </button>
                                        @endcan

                                        {{-- ACTIVAR / DESACTIVAR --}}
                                        @can('roles.toggle')
                                            <button type="button"
                                                wire:click="$dispatch('swal:toggle-active-rol', {
                        id: {{ $r->id }},
                        active: @js($r->active),
                        name: @js($r->name)
                    })"
                                                class="px-3 py-1 cursor-pointer rounded text-sm font-medium
                        {{ $r->active
                            ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                            : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                                {{ $r->active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        @endcan

                                    </div>
                                @else
                                    <span
                                        class="px-2 py-1 rounded text-xs
                   bg-gray-200 text-gray-800
                   dark:bg-neutral-700 dark:text-neutral-100">
                                        Bloqueado
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $roles->links() }}
        </div>

        {{-- MODAL CREAR / EDITAR ROL (reutilizando x-ui.modal) --}}
        @canany(['roles.create', 'roles.update'])
            <x-ui.modal model="openModal" :title="$roleId ? 'Editar rol' : 'Crear rol'" maxWidth="md:max-w-2xl" onClose="closeModal">
                {{-- Formulario --}}
                <div class="space-y-4">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Nombre del rol <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.defer="name" placeholder="Ej: Administrador, Cajero, Gerente"
                            class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                        @error('name')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Descripción
                        </label>
                        <textarea wire:model.defer="description" rows="3" placeholder="Describe brevemente la función del rol"
                            class="w-full rounded border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2
                           focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                        @error('description')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Activo --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model.defer="active" id="active" class="rounded" />
                        <label for="active" class="text-sm">
                            Rol activo
                        </label>
                    </div>

                    {{-- Nota de obligatorios --}}
                    <p class="text-xs text-gray-500 dark:text-neutral-400 pt-1">
                        <span class="text-red-500">*</span> Campos obligatorios.
                    </p>
                </div>

                {{-- Footer --}}
                @slot('footer')
                    <button type="button" wire:click="closeModal"
                        class="cursor-pointer px-4 py-2 rounded
                       border border-gray-300
                       hover:bg-gray-50
                       dark:border-neutral-700
                       dark:hover:bg-neutral-800">
                        Cancelar
                    </button>

                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="cursor-pointer px-4 py-2 rounded
                       bg-black text-white
                       hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">
                            {{ $roleId ? 'Guardar cambios' : 'Crear rol' }}
                        </span>
                        <span wire:loading wire:target="save">
                            Guardando…
                        </span>
                    </button>
                @endslot
            </x-ui.modal>
        @endcanany

        {{-- MODAL PERMISOS (reutilizando x-ui.modal) --}}
        @can('roles.assign_permissions')
            <x-ui.modal wire:key="roles-permissions-modal" model="openPermsModal" title="Permisos del rol"
                maxWidth="md:max-w-4xl" onClose="closePermissions">
                @php
                    // 1) Normalizar a array
                    $pg = $permissionsGrouped ?? [];
                    if ($pg instanceof \Illuminate\Support\Collection) {
                        $pg = $pg->toArray();
                    }

                    // 2) Orden de grupos (fijo)
                    $groupOrder = [
                        'dashboard' => 1,
                        'users' => 2,
                        'roles' => 3,
                        'empresa' => 4,
                        'empresas' => 4,
                        'entidad' => 5,
                        'entidades' => 5,
                        'proyecto' => 6,
                        'proyectos' => 6,
                        'bancos' => 7,
                        'facturas' => 8,
                        'agentes_servicio' => 9,
                        'agente_presupuestos' => 10,
                        'agente_rendicion' => 11,
                        'boletas_garantia' => 12,
                        'inversiones' => 13,
                    ];

                    // 3) Títulos de grupos
                    $groupTitleMap = [
                        'dashboard' => 'Panel de control',
                        'users' => 'Usuarios',
                        'roles' => 'Roles',
                        'empresa' => 'Empresa',
                        'empresas' => 'Empresa',
                        'entidad' => 'Entidad',
                        'entidades' => 'Entidad',
                        'proyecto' => 'Proyecto',
                        'proyectos' => 'Proyecto',
                        'bancos' => 'Bancos',
                        'facturas' => 'Facturas',
                        'agentes_servicio' => 'Agentes de Servicio',
                        'agente_presupuestos' => 'Agentes de Presupuestos',
                        'agente_rendicion' => 'Rendición de Gastos',
                        'boletas_garantia' => 'Boletas de Garantía',
                        'inversiones' => 'Inversiones',
                    ];

                    // 4) Orden de acciones dentro de cada grupo
                    $actionOrder = [
                        'view' => 1,
                        'create' => 2,
                        'update' => 3,
                        'toggle' => 4,
                        'assign_role' => 5,
                        'assign_permissions' => 6,
                        'pay' => 7,
                        'delete' => 8,
                        'close' => 9,
                        'movimiento' => 10,
                        'pagar' => 11,
                    ];

                    // 5) Diccionario humano (puedes ampliarlo)
                    $permLabels = [
                        'dashboard.view' => [
                            'label' => 'Ver panel de control',
                            'desc' => 'Permite acceder al panel principal.',
                        ],

                        'users.view' => ['label' => 'Ver usuarios', 'desc' => 'Permite visualizar usuarios.'],
                        'users.create' => ['label' => 'Crear usuarios', 'desc' => 'Permite registrar nuevos usuarios.'],
                        'users.update' => [
                            'label' => 'Editar usuarios',
                            'desc' => 'Permite modificar usuarios existentes.',
                        ],
                        'users.toggle' => [
                            'label' => 'Activar o desactivar usuarios',
                            'desc' => 'Permite habilitar o deshabilitar usuarios.',
                        ],
                        'users.assign_role' => [
                            'label' => 'Asignar roles',
                            'desc' => 'Permite definir los roles de un usuario.',
                        ],

                        'roles.view' => ['label' => 'Ver roles', 'desc' => 'Permite visualizar roles.'],
                        'roles.create' => ['label' => 'Crear roles', 'desc' => 'Permite crear nuevos roles.'],
                        'roles.update' => ['label' => 'Editar roles', 'desc' => 'Permite modificar roles existentes.'],
                        'roles.toggle' => [
                            'label' => 'Activar o desactivar roles',
                            'desc' => 'Permite habilitar o deshabilitar roles.',
                        ],
                        'roles.assign_permissions' => [
                            'label' => 'Asignar permisos a roles',
                            'desc' => 'Permite definir permisos de un rol.',
                        ],

                        'facturas.pay' => [
                            'label' => 'Pagar facturas',
                            'desc' => 'Permite registrar pagos a facturas.',
                        ],
                        
                        'agente_presupuestos.close' => [
                            'label' => 'Cerrar presupuestos',
                            'desc' => 'Permite cerrar controles de presupuestos.',
                        ],
                        
                        'agente_rendicion.close' => [
                            'label' => 'Cerrar rendición',
                            'desc' => 'Permite cerrar rendiciones de gastos.',
                        ],
                        
                        'inversiones.movimiento' => [
                            'label' => 'Registrar movimiento',
                            'desc' => 'Permite registrar entradas y salidas a inversiones.',
                        ],
                        'inversiones.pagar' => [
                            'label' => 'Finalizar inversión',
                            'desc' => 'Permite consolidar la inversión y pagar capital o utilidades.',
                        ],
                    ];

                    // Fallback humano (ES)
                    $humanize = function (string $perm) use ($permLabels) {
                        if (isset($permLabels[$perm])) {
                            return $permLabels[$perm];
                        }

                        $parts = explode('.', $perm);
                        $modulo = ucfirst(str_replace('_', ' ', $parts[0] ?? 'Módulo'));
                        $accionKey = $parts[1] ?? '';

                        $accion = match ($accionKey) {
                            'view' => 'Ver',
                            'create' => 'Crear',
                            'update' => 'Editar',
                            'delete' => 'Eliminar',
                            'toggle' => 'Activar / Desactivar',
                            default => ucfirst(str_replace('_', ' ', $accionKey ?: $perm)),
                        };

                        return [
                            'label' => "{$accion} ({$modulo})",
                            'desc' => "Permite {$accion} en el módulo {$modulo}.",
                        ];
                    };

                    // 6) Ordenar grupos según el orden fijo
                    $groupsOrdered = collect($pg)->sortBy(function ($perms, $group) use ($groupOrder) {
                        $o = $groupOrder[$group] ?? 999;
                        return sprintf('%03d-%s', $o, $group);
                    });

                    // 7) Ordenar permisos dentro del grupo (view, create, update, toggle, luego resto)
                    $sortPerms = function ($perms) use ($actionOrder) {
                        return collect($perms)
                            ->sortBy(function ($p) use ($actionOrder) {
                                $name = is_array($p) ? $p['name'] ?? '' : $p->name ?? '';
                                $parts = explode('.', $name);
                                $action = $parts[1] ?? '';
                                $o = $actionOrder[$action] ?? 999;
                                return sprintf('%03d-%s', $o, $name);
                            })
                            ->values()
                            ->all();
                    };
                @endphp

                <div x-data="{
                    q: '',
                    isMatch(text) {
                        if (!this.q) return true;
                        return (text || '').toLowerCase().includes(this.q.toLowerCase());
                    }
                }" class="space-y-4">

                    {{-- Buscador --}}
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <label class="text-sm font-medium">Buscar permiso</label>
                            <input x-model="q" type="text" placeholder="Ej: ver, crear, editar, usuarios..."
                                class="mt-1 w-full rounded border px-3 py-2
                               bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700
                               text-gray-900 dark:text-neutral-100
                               placeholder:text-gray-400 dark:placeholder:text-neutral-500" />
                        </div>

                        <div class="sm:w-72">
                            <div class="mt-6 text-xs text-gray-600 dark:text-neutral-400">
                                Recomendación: habilita solo lo necesario. Comienza por <b>Ver</b> y luego agrega <b>Crear</b> o
                                <b>Editar</b>.
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto pr-1 space-y-3">
                        @foreach ($groupsOrdered as $group => $perms)
                            @php
                                $groupTitle = $groupTitleMap[$group] ?? ucfirst(str_replace('_', ' ', $group));
                                $permsSorted = $sortPerms($perms);
                            @endphp

                            <div x-data="{ open: true }" class="border rounded dark:border-neutral-800">
                                {{-- Header del grupo --}}
                                <div class="flex items-center justify-between p-3 gap-3">
                                    <button type="button" class="text-left min-w-0" @click="open = !open">
                                        <div class="font-semibold truncate">{{ $groupTitle }}</div>
                                        <div class="text-xs text-gray-600 dark:text-neutral-400">
                                            Permisos del módulo {{ strtolower($groupTitle) }}.
                                        </div>
                                    </button>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button"
                                            class="text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50
                                           dark:border-neutral-700 dark:hover:bg-neutral-800"
                                            wire:click="selectAllGroup(@js($group))">
                                            Seleccionar todo
                                        </button>
                                        <button type="button"
                                            class="text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50
                                           dark:border-neutral-700 dark:hover:bg-neutral-800"
                                            wire:click="clearAllGroup(@js($group))">
                                            Quitar todo
                                        </button>
                                    </div>
                                </div>

                                {{-- Contenido del grupo --}}
                                <div x-show="open" x-collapse class="px-3 pb-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach ($permsSorted as $p)
                                            @php
                                                $permName = is_array($p) ? $p['name'] ?? '' : $p->name ?? '';
                                                $info = $humanize($permName);
                                                $searchText =
                                                    ($info['label'] ?? '') .
                                                    ' ' .
                                                    ($info['desc'] ?? '') .
                                                    ' ' .
                                                    $groupTitle;
                                            @endphp

                                            <div x-show="isMatch(@js($searchText))"
                                                class="rounded border border-gray-200 dark:border-neutral-800 p-3">
                                                <label class="flex items-start gap-3 cursor-pointer">
                                                    <input type="checkbox" value="{{ $permName }}"
                                                        wire:model.defer="permissionsSelected" class="mt-1 rounded" />

                                                    <div class="min-w-0">
                                                        <div class="font-medium text-sm text-gray-900 dark:text-neutral-100">
                                                            {{ $info['label'] ?? 'Permiso' }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 dark:text-neutral-400">
                                                            {{ $info['desc'] ?? '' }}
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    @slot('footer')
                        <button type="button" wire:click="closePermissions"
                            class="cursor-pointer px-4 py-2 rounded border border-gray-300 hover:bg-gray-50
                           dark:border-neutral-700 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>

                        <button type="button" wire:click="savePermissions" wire:loading.attr="disabled"
                            wire:target="savePermissions"
                            class="cursor-pointer px-4 py-2 rounded bg-black text-white hover:opacity-90
                           disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="savePermissions">Guardar permisos</span>
                            <span wire:loading wire:target="savePermissions">Guardando…</span>
                        </button>
                    @endslot
                </div>
            </x-ui.modal>
        @endcan
    @endcan

    @cannot('roles.view')
        <div
            class="p-4 border rounded bg-yellow-50 dark:bg-yellow-900 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200">
            No tienes permiso para ver esta sección.
        </div>
    @endcannot
</div>
