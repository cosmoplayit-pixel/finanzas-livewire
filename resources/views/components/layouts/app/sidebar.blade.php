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
                        <flux:navlist.item icon="shield-check" :href="route('roles')"
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
                <flux:navlist.group :heading="__('Gestión de Catalogos')" class="grid">

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

                    {{-- Agentes de Servicio --}}
                    @can('agentes_servicio.view')
                        <flux:navlist.item icon="user-group" :href="route('agentes_servicio')"
                            :current="request()->routeIs('agentes_servicio')" wire:navigate>
                            <span class="flex w-full items-center justify-between gap-2">
                                <span>{{ __('Agentes de Servicio') }}</span>
                                @isset($navCounts['agentes_servicio'])
                                    <flux:badge size="sm" variant="subtle">
                                        {{ $navCounts['agentes_servicio'] }}
                                    </flux:badge>
                                @endisset
                            </span>
                        </flux:navlist.item>
                    @endcan

                    <flux:navlist.group :heading="__('Gestión Financiera')" class="grid">

                        {{-- Facturas --}}
                        @can('facturas.view')
                            <flux:navlist.item icon="document-text" :href="route('facturas')"
                                :current="request()->routeIs('facturas')" wire:navigate>
                                <span class="flex w-full items-center justify-between gap-2">
                                    <span>{{ __('Proyetos Facturas') }}</span>
                                    @isset($navCounts['facturas'])
                                        <flux:badge size="sm" variant="subtle">
                                            {{ $navCounts['facturas'] }}
                                        </flux:badge>
                                    @endisset
                                </span>
                            </flux:navlist.item>
                        @endcan

                        {{-- Agentes de Presupuestos --}}
                        @can('agente_presupuestos.view')
                            <flux:navlist.item icon="user-group" :href="route('agente_presupuestos')"
                                :current="request()->routeIs('agente_presupuestos')" wire:navigate>
                                <span class="flex w-full items-center justify-between gap-2">
                                    <span>{{ __('Ag. Presupuestos') }}</span>
                                    @isset($navCounts['agente_presupuestos'])
                                        <flux:badge size="sm" variant="subtle">
                                            {{ $navCounts['agente_presupuestos'] }}
                                        </flux:badge>
                                    @endisset
                                </span>
                            </flux:navlist.item>
                        @endcan

                        {{-- Boletas de Garantia --}}
                        @can('boletas_garantia.view')
                            <flux:navlist.item icon="shield-check" :href="route('boletas_garantia')"
                                :current="request()->routeIs('boletas_garantia')" wire:navigate>
                                <span class="flex w-full items-center justify-between gap-2">
                                    <span>{{ __('Boletas de Garantía') }}</span>
                                    @isset($navCounts['boletas_garantia'])
                                        <flux:badge size="sm" variant="subtle">
                                            {{ $navCounts['boletas_garantia'] }}
                                        </flux:badge>
                                    @endisset
                                </span>
                            </flux:navlist.item>
                        @endcan

                        {{-- Inversiones --}}
                        @can('inversiones.view')
                            <flux:navlist.item icon="currency-dollar" :href="route('inversiones')"
                                :current="request()->routeIs('inversiones')" wire:navigate>
                                <span class="flex w-full items-center justify-between gap-2">
                                    <span>{{ __('Inversiones') }}</span>
                                    @isset($navCounts['inversiones'])
                                        <flux:badge size="sm" variant="subtle">
                                            {{ $navCounts['inversiones'] }}
                                        </flux:badge>
                                    @endisset
                                </span>
                            </flux:navlist.item>
                        @endcan

                    </flux:navlist.group>
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
        function registerSwalToggleEntidad() {
            window.addEventListener('swal:toggle-active-entidad', (event) => {
                const {
                    id,
                    active,
                    name
                } = event.detail || {};

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

                    // Importante: para quitar tu loading Alpine
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        // Livewire 3 (recomendado)
        document.addEventListener('livewire:init', registerSwalToggleEntidad);

        // Si usas navegación tipo SPA (Livewire navigate), registra también:
        document.addEventListener('livewire:navigated', registerSwalToggleEntidad);


        // ===================== PROYECTOS (SweetAlert + DOM event) =====================
        function registerSwalToggleProyecto() {
            window.addEventListener('swal:toggle-active-proyecto', (event) => {
                const {
                    id,
                    active,
                    name
                } = event.detail || {};

                Swal.fire({
                    title: active ? '¿Desactivar proyecto?' : '¿Activar proyecto?',
                    text: `¿Seguro que desea ${active ? 'desactivar' : 'activar'} el proyecto "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: active ? '#dc2626' : '#16a34a',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('doToggleActiveProyecto', {
                            id
                        });
                    }
                }).finally(() => {
                    // ✅ libera el loading Alpine SIEMPRE (confirmó o canceló)
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        // Registra cuando Livewire inicia
        document.addEventListener('livewire:init', registerSwalToggleProyecto);

        // Si usas navegación (wire:navigate), registra también al navegar
        document.addEventListener('livewire:navigated', registerSwalToggleProyecto);


        // ===================== BANCOS =====================
        function registerSwalToggleBanco() {
            window.addEventListener('swal:toggle-active-banco', (event) => {
                const {
                    id,
                    active,
                    name
                } = event.detail || {};

                Swal.fire({
                    title: active ? '¿Desactivar banco?' : '¿Activar banco?',
                    text: `¿Seguro que deseas ${active ? 'desactivar' : 'activar'} el banco "${name}"?`,
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
                }).finally(() => {
                    // ✅ libera el loading Alpine SIEMPRE
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        // Si usas navegación tipo SPA (wire:navigate), registra también:
        document.addEventListener('livewire:navigated', registerSwalToggleBanco);

        // ✅ y regístralo una vez al iniciar (ESTO va dentro de tu livewire:init wrapper)
        registerSwalToggleBanco();

        // ===================== FACTURAS: ELIMINAR PAGO =====================
        Livewire.on('swal:delete-pago', ({
            id,
            info
        }) => {
            Swal.fire({
                title: '¿Eliminar pago?',
                text: info ? info : 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doDeletePago', {
                        id
                    });
                }
            });
        });
        Livewire.on('scroll:restore', () => {
            const y = window.__lw_scrollY ?? 0;
            requestAnimationFrame(() => window.scrollTo({
                top: y,
                behavior: 'auto'
            }));
        });

        // ===================== RENDICIÓN: ELIMINAR MOVIMIENTO =====================
        function registerSwalDeleteMovimiento() {
            window.addEventListener('swal:delete-movimiento', (event) => {
                const {
                    id,
                    monto
                } = event.detail || {};

                Swal.fire({
                    title: '¿Eliminar movimiento?',
                    text: `Esta acción no se puede deshacer.${monto ? ' (' + monto + ')' : ''}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('doDeleteMovimiento', {
                            id
                        });
                    }
                }).finally(() => {
                    // si usas loading alpine, libera
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        document.addEventListener('livewire:init', registerSwalDeleteMovimiento);
        document.addEventListener('livewire:navigated', registerSwalDeleteMovimiento);




    });
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('modalStack', {
            stack: [], // [{ id, closeFn }...]

            open(id, closeFn) {
                // evita duplicados
                this.stack = this.stack.filter(x => x.id !== id);
                this.stack.push({
                    id,
                    closeFn
                });
                document.documentElement.classList.add('overflow-hidden');
            },

            closeTop() {
                const top = this.stack[this.stack.length - 1];
                if (top?.closeFn) top.closeFn();
            },

            close(id) {
                // si se cierra el que no es top, lo sacamos sin afectar top
                this.stack = this.stack.filter(x => x.id !== id);
                if (this.stack.length === 0) {
                    document.documentElement.classList.remove('overflow-hidden');
                }
            },

            isTop(id) {
                return this.stack.length && this.stack[this.stack.length - 1].id === id;
            },

            hasAny() {
                return this.stack.length > 0;
            }
        });
    });
</script>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal:confirm-delete-devolucion', ({
            boletaId,
            devolucionId
        }) => {
            Swal.fire({
                title: '¿Eliminar devolución?',
                text: 'Esta acción revertirá el saldo del banco.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((res) => {
                if (res.isConfirmed) {
                    Livewire.dispatch('bg:delete-devolucion', {
                        boletaId,
                        devolucionId
                    });
                }
            });
        });
    });
</script>
