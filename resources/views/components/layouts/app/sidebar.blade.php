<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <x-app-logo />

        <flux:navlist variant="outline">

            {{-- ================= DASHBOARD ================= --}}
            @can('dashboard.view')
                <flux:navlist.group :heading="__('Dashboard')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                        wire:navigate>
                        {{ __('Panel de Control') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endcan


            {{-- ================= PLATAFORMA ================= --}}
            @canany(['users.view', 'roles.view', 'empresas.view'])
                <flux:navlist.group :heading="__('Plataforma')" class="grid">

                    {{-- Usuarios --}}
                    @can('users.view')
                        <flux:navlist.item icon="users" :href="route('usuarios')" :current="request()->routeIs('usuarios')"
                            wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Usuarios') }}</span>
                                @isset($navCounts['usuarios'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['usuarios'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                    {{-- Roles --}}
                    @can('roles.view')
                        <flux:navlist.item icon="shield-check" :href="route('admin.roles')"
                            :current="request()->routeIs('admin.roles')" wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Roles') }}</span>
                                @isset($navCounts['roles'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['roles'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                    {{-- Empresas --}}
                    @can('empresas.view')
                        <flux:navlist.item icon="building-office" :href="route('empresas')"
                            :current="request()->routeIs('empresas')" wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Empresas') }}</span>
                                @isset($navCounts['empresas'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['empresas'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                </flux:navlist.group>
            @endcanany


            {{-- ================= GESTIÓN FINANCIERA ================= --}}
            @canany(['entidades.view', 'proyectos.view', 'bancos.view'])
                <flux:navlist.group :heading="__('Gestión Financiera')" class="grid">

                    {{-- Entidades --}}
                    @can('entidades.view')
                        <flux:navlist.item icon="building-office" :href="route('entidades')"
                            :current="request()->routeIs('entidades')" wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Entidades') }}</span>
                                @isset($navCounts['entidades'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['entidades'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                    {{-- Proyectos --}}
                    @can('proyectos.view')
                        <flux:navlist.item icon="folder" :href="route('proyectos')"
                            :current="request()->routeIs('proyectos')" wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Proyectos') }}</span>
                                @isset($navCounts['proyectos'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['proyectos'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                    {{-- Bancos --}}
                    @can('bancos.view')
                        <flux:navlist.item icon="banknotes" :href="route('bancos')" :current="request()->routeIs('bancos')"
                            wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Bancos') }}</span>
                                @isset($navCounts['bancos'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['bancos'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                    {{-- Facturas --}}
                    @can('facturas.view')
                        <flux:navlist.item icon="document-text" :href="route('facturas')"
                            :current="request()->routeIs('facturas')" wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Facturas') }}</span>

                                @isset($navCounts['facturas'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['facturas'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan


                </flux:navlist.group>
            @endcanany

        </flux:navlist>
        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Configuración') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Cerrar Sesión') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>

<script>
    document.addEventListener('livewire:init', () => {

        /* =========================================================
         * SWEETALERT CONFIRMS (Livewire)
         * Recomendación: mantener todos los listeners aquí.
         * ========================================================= */

        // ===================== USUARIOS =====================
        Livewire.on('swal:toggle-active', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar usuario?' : '¿Activar usuario?',
                text: '¿Seguro que desea ' + (active ? 'desactivar' : 'activar') +
                    ` el usuario "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActive', {
                        id
                    });
                }
            });
        });

        // ===================== ROLES =====================
        Livewire.on('swal:toggle-active-rol', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar rol?' : '¿Activar rol?',
                text: `¿Seguro que deseas ${active ? 'desactivar' : 'activar'}: "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActiveRol', {
                        id
                    });
                }
            });
        });

        // ===================== EMPRESAS =====================
        Livewire.on('swal:toggle-active-empresa', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar empresa?' : '¿Activar empresa?',
                text: `¿Seguro que desea ${active ? 'desactivar' : 'activar'} la empresa "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActiveEmpresa', {
                        id
                    });
                }
            });
        });

        // ===================== ENTIDADES =====================
        Livewire.on('swal:toggle-active-entidad', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar entidad?' : '¿Activar entidad?',
                text: `¿Seguro que desea ${active ? 'desactivar' : 'activar'} la entidad "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('toggleEntidad', {
                        id
                    });
                }
            });
        });

        // ===================== PROYECTOS =====================
        Livewire.on('swal:toggle-active-proyecto', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar proyecto?' : '¿Activar proyecto?',
                text: '¿Seguro que desea ' + (active ? 'desactivar' : 'activar') +
                    ` el proyecto "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                reverseButtons: true, // ✅ corregido (antes estaba duplicado)
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActiveProyecto', {
                        id
                    });
                }
            });
        });

        // ===================== BANCOS =====================
        Livewire.on('swal:toggle-active-banco', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar banco?' : '¿Activar banco?',
                text: `¿Seguro que deseas ${active ? 'desactivar' : 'activar'}: "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActiveBanco', {
                        id
                    });
                }
            });
        });

    });
</script>
