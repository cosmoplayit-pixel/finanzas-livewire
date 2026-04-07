<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <script>
        // Force light mode on first visit if no preference is set
        if (!localStorage.getItem('flux.appearance')) {
            localStorage.setItem('flux.appearance', 'light');
        }

        // Apply dark mode immediately if preferred to prevent flash
        if (localStorage.getItem('flux.appearance') === 'dark' || (localStorage.getItem('flux.appearance') === 'system' &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 relative overflow-x-hidden antialiased">

    {{-- Matamos el persist de nodos para limpiar memoria --}}
    <div wire:persist="app-bg-nodes" class="hidden"></div>

    {{-- ===================== OVERLAYS DE FONDO (RESTABLECIDOS) ===================== --}}
    <div class="pointer-events-none fixed inset-0 -z-10 bg-white dark:bg-zinc-800">
        {{-- Iluminación central suave --}}
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.04)_0%,transparent_60%)] dark:bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.05)_0%,transparent_55%)]">
        </div>
        {{-- Viñeta (sombras en bordes) --}}
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(0,0,0,0.10)_100%)] dark:bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(0,0,0,0.55)_100%)]">
        </div>
    </div>

    {{-- ===================== SIDEBAR (PLANTILLA FLUX) ===================== --}}
    <flux:sidebar sticky collapsible
        class="border-e border-zinc-200 bg-white/80 backdrop-blur-xl
               dark:border-zinc-700 dark:bg-zinc-900/70">

        <flux:sidebar.header>
            <flux:sidebar.brand
                :href="auth()->user()->hasRole('Administrador') ? route('usuarios') : route('dashboard')"
                logo="https://cdn.jsdelivr.net/npm/lucide-static@0.479.0/icons/circle-dollar-sign.svg"
                logo:dark="https://cdn.jsdelivr.net/npm/lucide-static@0.479.0/icons/circle-dollar-sign.svg"
                name="{{ config('app.name') }}" />

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        {{-- ===================== NAV PRINCIPAL ===================== --}}
        <flux:sidebar.nav>

            {{-- DASHBOARD --}}

            @can('dashboard.view')
                <flux:sidebar.group heading="{{ __('Dashboard') }}" class="grid"> </flux:sidebar.group>
            @endcan

            @can('dashboard.view')
                <flux:sidebar.item icon="home"
                    :href="auth()->user()->hasRole('Administrador') ? route('usuarios') : route('dashboard')"
                    :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Panel de Control') }}
                </flux:sidebar.item>
            @endcan

            {{-- PLATAFORMA --}}
            @canany(['users.view', 'roles.view', 'empresas.view'])
                <flux:sidebar.group heading="{{ __('Plataforma') }}" class="grid"> </flux:sidebar.group>
            @endcanany

            @can('users.view')
                <flux:sidebar.item icon="users" :href="route('usuarios')" :current="request()->routeIs('usuarios')"
                    :badge="$navCounts['usuarios'] ?? null" wire:navigate>
                    {{ __('Usuarios') }}
                </flux:sidebar.item>
            @endcan

            @can('roles.view')
                <flux:sidebar.item icon="shield-check" :href="route('roles')" :current="request()->routeIs('roles')"
                    :badge="$navCounts['roles'] ?? null" wire:navigate>
                    {{ __('Roles') }}
                </flux:sidebar.item>
            @endcan

            @can('empresas.view')
                <flux:sidebar.item icon="building-office" :href="route('empresas')"
                    :current="request()->routeIs('empresas')" :badge="$navCounts['empresas'] ?? null" wire:navigate>
                    {{ __('Empresas') }}
                </flux:sidebar.item>
            @endcan

            {{-- GESTIÓN DE CATÁLOGOS --}}
            @canany(['entidades.view', 'proyectos.view', 'bancos.view', 'agentes_servicio.view'])
                <flux:sidebar.group heading="{{ __('Gestión de Catálogos') }}" class="grid"> </flux:sidebar.group>
            @endcanany

            @can('entidades.view')
                <flux:sidebar.item icon="building-office" :href="route('clientes')"
                    :current="request()->routeIs('clientes')" :badge="$navCounts['entidades'] ?? null" wire:navigate>
                    {{ __('Clientes') }}
                </flux:sidebar.item>
            @endcan

            @can('proyectos.view')
                <flux:sidebar.item icon="folder" :href="route('proyectos')" :current="request()->routeIs('proyectos')"
                    :badge="$navCounts['proyectos'] ?? null" wire:navigate>
                    {{ __('Proyectos') }}
                </flux:sidebar.item>
            @endcan

            @can('bancos.view')
                <flux:sidebar.item icon="banknotes" :href="route('bancos')" :current="request()->routeIs('bancos')"
                    :badge="$navCounts['bancos'] ?? null" wire:navigate>
                    {{ __('Bancos') }}
                </flux:sidebar.item>
            @endcan

            @can('agentes_servicio.view')
                <flux:sidebar.item icon="user-group" :href="route('agentes_servicio')"
                    :current="request()->routeIs('agentes_servicio')" :badge="$navCounts['agentes_servicio'] ?? null"
                    wire:navigate>
                    {{ __('Ag. de Servicio') }}
                </flux:sidebar.item>
            @endcan


            {{-- GESTIÓN FINANCIERA --}}
            @canany(['facturas.view', 'agente_presupuestos.view', 'boletas_garantia.view', 'inversiones.view',
                'proyectos.resumen', 'transacciones.view'])
                <flux:sidebar.group heading="{{ __('Gestión Financiera') }}" class="grid"> </flux:sidebar.group>
            @endcanany

            @can('boletas_garantia.view')
                <flux:sidebar.item icon="shield-check" :href="route('boletas_garantia')"
                    :current="request()->routeIs('boletas_garantia')" :badge="$navCounts['boletas_garantia'] ?? null"
                    wire:navigate>
                    {{ __('Boletas Garantía') }}
                </flux:sidebar.item>
            @endcan

            @can('facturas.view')
                <flux:sidebar.item icon="document-text" :href="route('facturas')"
                    :current="request()->routeIs('facturas')" :badge="$navCounts['facturas'] ?? null" wire:navigate>
                    {{ __('Facturas') }}
                </flux:sidebar.item>
            @endcan

            @can('agente_presupuestos.view')
                <flux:sidebar.item icon="user-group" :href="route('agente_presupuestos')"
                    :current="request()->routeIs('agente_presupuestos')"
                    :badge="$navCounts['agente_presupuestos'] ?? null" wire:navigate>
                    {{ __('Presupuestos') }}
                </flux:sidebar.item>
            @endcan

            @can('inversiones.view')
                <flux:sidebar.item icon="currency-dollar" :href="route('inversiones')"
                    :current="request()->routeIs('inversiones')" :badge="$navCounts['inversiones'] ?? null" wire:navigate>
                    {{ __('Inversión Externa') }}
                </flux:sidebar.item>
            @endcan

            {{-- MOVIMIENTOS --}}
            @canany(['proyectos.resumen', 'transacciones.view'])
                <flux:sidebar.group expandable heading="Movimientos" class="grid">

                    @can('proyectos.resumen')
                        <flux:sidebar.item icon="chart-pie" :href="route('proyectos.resumen')"
                            :current="request()->routeIs('proyectos.resumen')" wire:navigate>
                            {{ __('Resumen Proyectos') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('transacciones.view')
                        <flux:sidebar.item icon="arrows-right-left" :href="route('transacciones')"
                            :current="request()->routeIs('transacciones')" wire:navigate>
                            {{ __('Transacciones') }}
                        </flux:sidebar.item>
                    @endcan

                </flux:sidebar.group>
            @endcanany


            {{-- GESTIÓN HERRAMIENTAS 
            @canany(['herramientas.view'])
                <flux:sidebar.group heading="{{ __('Gestión Herramientas') }}" class="grid">
                    <flux:sidebar.item icon="wrench" href="{{ route('herramientas') }}" wire:navigate>
                        {{ __('Herramientas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" href="{{ route('prestamos_herramientas') }}"
                        wire:navigate>
                        {{ __('Préstamos y Devoluciones') }}
                    </flux:sidebar.item>

                </flux:sidebar.group>
            @endcanany --}}




        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        {{-- ===================== PERFIL (DESKTOP) ===================== --}}
        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile :name="auth()->user()->name" :initials="auth()->user()->initials()" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg
                            bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
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

                {{-- Tema --}}
                <flux:menu.item icon="sun" x-show="$flux.appearance === 'dark'" @click="$flux.appearance = 'light'">
                    {{ __('Modo Claro') }}
                </flux:menu.item>
                <flux:menu.item icon="moon" x-show="$flux.appearance === 'light'" @click="$flux.appearance = 'dark'">
                    {{ __('Modo Oscuro') }}
                </flux:menu.item>

                <flux:menu.separator />

                {{-- Configuración --}}
                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Configuración') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                {{-- Cerrar Sesión --}}
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Cerrar Sesión') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>

    </flux:sidebar>

    {{-- ===================== HEADER MOBILE ===================== --}}
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
                                    class="flex h-full w-full items-center justify-center rounded-lg
                                    bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
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

                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Configuración') }}
                </flux:menu.item>

                <flux:menu.separator />

                {{-- Tema --}}
                <flux:menu.item icon="sun" x-show="$flux.appearance === 'dark'" @click="$flux.appearance = 'light'">
                    {{ __('Modo Claro') }}
                </flux:menu.item>
                <flux:menu.item icon="moon" x-show="$flux.appearance === 'light'" @click="$flux.appearance = 'dark'">
                    {{ __('Modo Oscuro') }}
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Cerrar Sesión') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{-- ===================== CONTENIDO ===================== --}}
    {{ $slot }}

    @fluxScripts

    {{-- ===================== SWEETALERT + LIVEWIRE ===================== --}}
    <script data-navigate-once>
        document.addEventListener('livewire:init', () => {

            // USUARIOS
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
                    if (result.isConfirmed) Livewire.dispatch('doToggleActive', {
                        id
                    });
                });
            });

            // ROLES
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
                    if (result.isConfirmed) Livewire.dispatch('doToggleActiveRol', {
                        id
                    });
                });
            });

            // EMPRESAS
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
                    if (result.isConfirmed) Livewire.dispatch('doToggleActiveEmpresa', {
                        id
                    });
                });
            });

            // BANCOS
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
                    if (result.isConfirmed) Livewire.dispatch('doToggleActiveBanco', {
                        id
                    });
                }).finally(() => {
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // AGENTES DE SERVICIO
            window.addEventListener('swal:toggle-active-agente', (event) => {
                const {
                    id,
                    active,
                    name
                } = event.detail || {};
                Swal.fire({
                    title: active ? '¿Desactivar agente?' : '¿Activar agente?',
                    text: `¿Seguro que deseas ${active ? 'desactivar' : 'activar'} el agente "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: active ? '#dc2626' : '#16a34a',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) Livewire.dispatch('doToggleActiveAgente', {
                        id
                    });
                }).finally(() => {
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // ENTIDADES: ELIMINAR
            window.addEventListener('swal:delete-entidad', (event) => {
                const {
                    id,
                    name
                } = event.detail || {};
                Swal.fire({
                    title: '¿Eliminar entidad?',
                    text: `¿Seguro que desea eliminar "${name}" permanentemente? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) Livewire.dispatch('deleteEntidad', {
                        id
                    });
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // ENTIDADES (eventos window)
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
                    if (result.isConfirmed) Livewire.dispatch('toggleEntidad', {
                        id
                    });
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // PROYECTOS
            window.addEventListener('swal:toggle-active-proyecto', (event) => {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                const {
                    id,
                    active,
                    name
                } = data || {};
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
                    if (result.isConfirmed) Livewire.dispatch('doToggleActiveProyecto', {
                        id
                    });
                }).finally(() => {
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // PROYECTOS — Confirmar eliminación (sin dependencias)
            window.addEventListener('swal:confirm-delete-proyecto', (event) => {
                // En Livewire 3, si despachamos un array asociativo desde PHP,
                // llega envuelto en un array: event.detail = [{id:..., name:...}]
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                const {
                    id,
                    name
                } = data || {};

                Swal.fire({
                    title: '¿Eliminar proyecto?',
                    html: `¿Seguro que desea eliminar el proyecto <strong>"${name}"</strong>?<br><span style="font-size:0.85em;color:#6b7280">Esta acción no se puede deshacer.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    timer: 8000,
                    timerProgressBar: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('doDeleteProyecto', {
                            id
                        });
                    }
                }).finally(() => {
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // PROYECTOS — Bloqueo de eliminación (con dependencias)
            window.addEventListener('swal:proyecto-no-deletable', (event) => {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                const {
                    name,
                    detalle
                } = data || {};

                Swal.fire({
                    title: 'No se puede eliminar',
                    html: `El proyecto <strong>"${name}"</strong> tiene registros asociados:<br><br><em>${detalle}</em><br><br>Desvincule todos los registros antes de intentar eliminar.`,
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc2626',
                });
            });

            // RENDICIÓN: ELIMINAR MOVIMIENTO
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
                    if (result.isConfirmed) Livewire.dispatch('doDeleteMovimiento', {
                        id
                    });
                }).finally(() => {
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });

            // FACTURAS: ELIMINAR PAGO
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
                    if (result.isConfirmed) Livewire.dispatch('doDeletePago', {
                        id
                    });
                });
            });

            // FACTURAS: BANCO SIN SALDO al eliminar pago
            Livewire.on('swal:banco-sin-saldo', ({
                html,
                monto,
                banco
            }) => {
                setTimeout(() => {
                    Swal.fire({
                        title: 'No se puede eliminar el pago',
                        html: html ?
                            `<p style="font-size:0.92em; color:#555; line-height:1.6;">${html}</p>` :
                            `El banco <strong>${banco ?? 'destino'}</strong> no tiene saldo suficiente para revertir este movimiento.<br><br>Saldo necesario: <strong>${monto ?? ''}</strong>`,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#dc2626',
                    });
                }, 350);
            });

            // Scroll restore
            Livewire.on('scroll:restore', () => {
                const y = window.__lw_scrollY ?? 0;
                requestAnimationFrame(() => window.scrollTo({
                    top: y,
                    behavior: 'auto'
                }));
            });

            // HERRAMIENTAS
            window.addEventListener('swal:toggle-active-herramienta', (e) => {
                const {
                    id,
                    active,
                    name
                } = e.detail;
                Swal.fire({
                    title: active ? '¿Desactivar herramienta?' : '¿Activar herramienta?',
                    text: '¿Seguro que desea ' + (active ? 'desactivar' : 'activar') +
                        ' la herramienta "' + name + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: active ? '#dc2626' : '#16a34a',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) Livewire.dispatch('doToggleActiveHerramienta', {
                        id
                    });
                });
            });

            window.addEventListener('swal:delete-herramienta', (e) => {
                const {
                    id,
                    name
                } = e.detail;
                Swal.fire({
                    title: '¿Eliminar herramienta?',
                    text: '¿Seguro que desea eliminar "' + name +
                        '" permanentemente? Esta acción no se puede deshacer.',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) Livewire.dispatch('doDeleteHerramienta', {
                        id
                    });
                });
            });

        });
    </script>

    {{-- ===================== ALPINE: MODAL STACK ===================== --}}
    <script data-navigate-once>
        document.addEventListener('alpine:init', () => {
            Alpine.store('modalStack', {
                stack: [],

                open(id, closeFn) {
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

    {{-- ===================== OTROS SWEETALERTS ===================== --}}
    <script data-navigate-once>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:confirm-delete-devolucion', ({
                boletaId,
                devolucionId,
                info
            }) => {
                Swal.fire({
                    title: '¿Eliminar devolución?',
                    text: info ? info : 'Esta acción revertirá el saldo del banco.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
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

    <script data-navigate-once>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                Swal.fire({
                    icon: data.icon ?? 'info',
                    title: data.title ?? '',
                    text: data.text ?? '',
                });
            });

            // ✅ GESTOR GLOBAL DE TOASTS
            Livewire.on('toast', ({
                type,
                message
            }) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: type,
                    title: message
                });
            });

            // ✅ GESTOR GLOBAL DE MODALES SWAL
            Livewire.on('swal:modal', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                Swal.fire({
                    icon: data.type ?? 'info',
                    title: data.title ?? '',
                    text: data.text ?? '',
                    confirmButtonColor: '#6366f1',
                });
            });
        });
    </script>



    @stack('js')

    {{-- ===================== BACKUP: FONDO NODOS (DESACTIVADO) ===================== --}}
    {{-- 
    <div class="pointer-events-none fixed inset-0 -z-10" wire:persist="app-bg-nodes" x-data="nodeBackground()">
        <canvas id="app-nodes-bg" x-ref="canvas" class="absolute inset-0 w-full h-full"></canvas>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.04)_0%,transparent_60%)] dark:bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.05)_0%,transparent_55%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(0,0,0,0.10)_100%)] dark:bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(0,0,0,0.55)_100%)]"></div>
    </div>
    --}}

    @livewireStyles
</body>

</html>
