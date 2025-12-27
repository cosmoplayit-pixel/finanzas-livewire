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
            <flux:navlist.group :heading="__('Plataforma')" class="grid">
                @hasanyrole('Administrador')
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                        wire:navigate>{{ __('Panel de Control') }}</flux:navlist.item>

                    <flux:navlist.item icon="users" :href="route('usuarios')" :current="request()->routeIs('usuarios')"
                        wire:navigate>
                        {{ __('Usuarios') }}
                    </flux:navlist.item>
                @endhasanyrole

                @hasanyrole('Administrador|Manager')
                    <flux:navlist.item icon="folder" :href="route('empresas')" :current="request()->routeIs('empresas')"
                        wire:navigate>
                        {{ __('Empresas') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="pencil" :href="route('entidades')" :current="request()->routeIs('entidades')"
                        wire:navigate>
                        {{ __('Entidades') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="folder" :href="route('proyectos')" :current="request()->routeIs('proyectos')"
                        wire:navigate>
                        {{ __('Proyectos') }}
                    </flux:navlist.item>
                @endhasanyrole


            </flux:navlist.group>

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
        Livewire.on('swal:toggle-active', ({
            id,
            active,
            name
        }) => {

            Swal.fire({
                title: active ? '¿Desactivar usuario?' : '¿Activar usuario?',
                text: active ?
                    `El usuario "${name}" no podrá ingresar al sistema.` :
                    `El usuario "${name}" podrá volver a ingresar al sistema.`,
                icon: 'warning',
                showCancelButton: true,

                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',

                /* 🎨 COLORES */
                confirmButtonColor: active ? '#dc2626' : '#16a34a', // rojo / verde
                cancelButtonColor: '#6b7280', // gris

                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActive', {
                        id
                    });
                }
            });
        });
    });
</script>
<script>
    function confirmToggle(id, isActive) {
        Swal.fire({
            title: isActive ? '¿Desactivar entidad?' : '¿Activar entidad?',
            text: isActive ?
                'La entidad quedará inactiva en el sistema.' : 'La entidad volverá a estar disponible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isActive ? '#d33' : '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isActive ? 'Sí, desactivar' : 'Sí, activar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('toggleEntidad', {
                    id: id
                });
            }
        });
    }
</script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal:toggle-active-proyecto', ({
            id,
            active,
            name
        }) => {
            Swal.fire({
                title: active ? '¿Desactivar proyecto?' : '¿Activar proyecto?',
                text: active ?
                    `El proyecto "${name}" quedará inactivo.` :
                    `El proyecto "${name}" volverá a estar disponible.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: active ? 'Sí, desactivar' : 'Sí, activar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: active ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                reverseButtons: false,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('doToggleActiveProyecto', {
                        id
                    });
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('livewire:init', () => {

        Livewire.on('swal:toggle-active-empresa', ({
            id,
            active,
            name
        }) => {

            Swal.fire({
                title: active ? '¿Desactivar empresa?' : '¿Activar empresa?',
                text: `Empresa: ${name}`,
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

    });
</script>
