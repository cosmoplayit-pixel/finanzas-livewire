<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 relative overflow-x-hidden">

    {{-- ===================== FONDO NODOS (GLOBAL) ===================== --}}
    {{-- Queda detrás de TODO, no bloquea clicks --}}
    <div class="pointer-events-none fixed inset-0 -z-10">
        <canvas id="app-nodes-bg" class="absolute inset-0 w-full h-full"></canvas>

        {{-- Overlay suave para que combine (light/dark) --}}
        <div
            class="absolute inset-0
            bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.04)_0%,transparent_60%)]
            dark:bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.05)_0%,transparent_55%)]">
        </div>

        {{-- Vignette --}}
        <div
            class="absolute inset-0
            bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(0,0,0,0.10)_100%)]
            dark:bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(0,0,0,0.55)_100%)]">
        </div>
    </div>

    {{-- ===================== SIDEBAR ===================== --}}
    <flux:sidebar sticky stashable
        class="border-e border-zinc-200 bg-zinc-100/85 backdrop-blur-xl
               dark:border-zinc-700 dark:bg-zinc-900/70">
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
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full" data-test="logout-button">
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

{{-- ===================== SCRIPT NODOS (GLOBAL) ===================== --}}
<script>
    (function() {
        const canvas = document.getElementById('app-nodes-bg');
        if (!canvas) return;

        const ctx = canvas.getContext('2d', {
            alpha: true
        });

        const prefersReduced = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const mqMobile = window.matchMedia('(max-width: 767px)');

        // Parallax (desktop)
        const mouse = {
            x: 0,
            y: 0,
            tx: 0,
            ty: 0
        };

        function onMove(e) {
            const w = window.innerWidth || 1;
            const h = window.innerHeight || 1;
            mouse.tx = (e.clientX / w - 0.5) * 2;
            mouse.ty = (e.clientY / h - 0.5) * 2;
        }

        function syncMouseListener() {
            window.removeEventListener('mousemove', onMove);
            if (!mqMobile.matches) window.addEventListener('mousemove', onMove, {
                passive: true
            });
        }
        syncMouseListener();

        function resize() {
            const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
            canvas.width = Math.floor(window.innerWidth * dpr);
            canvas.height = Math.floor(window.innerHeight * dpr);
            canvas.style.width = window.innerWidth + 'px';
            canvas.style.height = window.innerHeight + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        resize();
        window.addEventListener('resize', resize, {
            passive: true
        });

        const nodes = [];
        const rand = (min, max) => min + Math.random() * (max - min);

        function init() {
            nodes.length = 0;

            const w = window.innerWidth;
            const h = window.innerHeight;
            const area = w * h;

            // Densidad adaptativa (mobile más liviano)
            const divisor = mqMobile.matches ? 73000 : 73000; // más nodos (antes 70k/90k)
            const minN = mqMobile.matches ? 35 : 90; // mínimo más alto
            const maxN = mqMobile.matches ? 90 : 220; // tope más alto


            const count = Math.max(minN, Math.min(maxN, Math.floor(area / divisor)));

            for (let i = 0; i < count; i++) {
                nodes.push({
                    x: rand(0, w),
                    y: rand(0, h),
                    z: rand(0.15, 1.0),
                    vx: rand(-0.16, 0.16),
                    vy: rand(-0.16, 0.16),
                    r: rand(1.0, 2.0),
                });
            }
        }
        init();

        let resizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => init(), 140);
        }, {
            passive: true
        });

        mqMobile.addEventListener?.('change', () => {
            syncMouseListener();
            init();
        });

        function draw() {
            const w = window.innerWidth;
            const h = window.innerHeight;

            const ease = mqMobile.matches ? 0.02 : 0.06;
            mouse.x += (mouse.tx - mouse.x) * ease;
            mouse.y += (mouse.ty - mouse.y) * ease;

            ctx.clearRect(0, 0, w, h);

            const isDark = document.documentElement.classList.contains('dark');

            // Colores: en light negro suave, en dark blanco suave
            const line = isDark ? 'rgba(255,255,255,' : 'rgba(0,0,0,';
            const glow = isDark ? 'rgba(16,185,129,' : 'rgba(0,0,0,'; // emerald glow en dark
            const core = isDark ? 'rgba(255,255,255,' : 'rgba(0,0,0,';

            for (const p of nodes) {
                if (!prefersReduced) {
                    const speed = mqMobile.matches ? 0.55 : 1.00;
                    p.x += p.vx * speed * (1.10 - p.z);
                    p.y += p.vy * speed * (1.10 - p.z);
                }

                if (p.x < -25) p.x = w + 25;
                if (p.x > w + 25) p.x = -25;
                if (p.y < -25) p.y = h + 25;
                if (p.y > h + 25) p.y = -25;
            }

            const maxDist = mqMobile.matches ?
                Math.min(150, Math.max(110, Math.floor(Math.min(w, h) / 6))) :
                Math.min(260, Math.max(180, Math.floor(Math.min(w, h) / 4)));

            for (let i = 0; i < nodes.length; i++) {
                const a = nodes[i];
                for (let j = i + 1; j < nodes.length; j++) {
                    const b = nodes[j];
                    const dx = a.x - b.x;
                    const dy = a.y - b.y;
                    const d = Math.hypot(dx, dy);
                    if (d > maxDist) continue;

                    const depth = (2 - (a.z + b.z)) / 2;
                    const baseAlpha = mqMobile.matches ? 0.11 : 0.18;
                    const alpha = Math.max(0, (1 - d / maxDist)) * baseAlpha * (0.6 + depth);

                    const parallax = mqMobile.matches ? 5 : 10;
                    const ax = a.x + mouse.x * (parallax * (1 - a.z));
                    const ay = a.y + mouse.y * (parallax * (1 - a.z));
                    const bx = b.x + mouse.x * (parallax * (1 - b.z));
                    const by = b.y + mouse.y * (parallax * (1 - b.z));

                    ctx.beginPath();
                    ctx.moveTo(ax, ay);
                    ctx.lineTo(bx, by);
                    ctx.strokeStyle = line + alpha + ')';
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }

            for (const p of nodes) {
                const depth = (1 - p.z);
                const parallax = mqMobile.matches ? 7 : 14;
                const px = p.x + mouse.x * (parallax * (1 - p.z));
                const py = p.y + mouse.y * (parallax * (1 - p.z));
                const rr = p.r + depth * 1.4;

                // glow
                ctx.beginPath();
                ctx.arc(px, py, rr * 3.0, 0, Math.PI * 2);
                const glowA = isDark ? (0.02 + depth * 0.05) : (0.02 + depth * 0.03);
                ctx.fillStyle = glow + glowA + ')';
                ctx.fill();

                // core
                ctx.beginPath();
                ctx.arc(px, py, rr, 0, Math.PI * 2);
                const coreA = isDark ? (0.22 + depth * 0.16) : (0.20 + depth * 0.12);
                ctx.fillStyle = core + coreA + ')';
                ctx.fill();
            }

            requestAnimationFrame(draw);
        }

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) resize();
        });

        draw();
    })();
</script>

<script>
    document.addEventListener('livewire:init', () => {

        /* =========================================================
         * SWEETALERT CONFIRMS (Livewire)
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
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        document.addEventListener('livewire:init', registerSwalToggleEntidad);
        document.addEventListener('livewire:navigated', registerSwalToggleEntidad);

        // ===================== PROYECTOS =====================
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
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        document.addEventListener('livewire:init', registerSwalToggleProyecto);
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
                    window.dispatchEvent(new CustomEvent('swal:done'));
                });
            });
        }

        document.addEventListener('livewire:navigated', registerSwalToggleBanco);
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

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            Swal.fire({
                icon: data.icon ?? 'info',
                title: data.title ?? '',
                text: data.text ?? '',
            });
        });
    });
</script>
