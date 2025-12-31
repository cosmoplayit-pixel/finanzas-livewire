@section('title', 'Panel de Control')
<div class="p-0 md:p-6 space-y-4" :title="__('Panel de Control')">
    {{-- HEADER --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Panel de Control</h1>

            @if ($isAdmin)
                <p class="text-sm text-gray-600 dark:text-neutral-300">
                    Vista global del sistema.
                </p>
            @else
                <p class="text-sm text-gray-600 dark:text-neutral-300">
                    Empresa: <span class="font-semibold">{{ $empresaNombre ?: '—' }}</span>
                </p>
            @endif
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Usuarios --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
            <div class="text-sm text-gray-500 dark:text-neutral-400">Usuarios</div>
            <div class="text-2xl font-semibold">{{ $usuariosTotal }}</div>
            <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                Activos: <span class="font-semibold">{{ $usuariosActivos }}</span>
                · Inactivos: <span class="font-semibold">{{ $usuariosInactivos }}</span>
            </div>
        </div>

        {{-- Empresas / Mi empresa --}}
        @if ($isAdmin)
            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
                <div class="text-sm text-gray-500 dark:text-neutral-400">Empresas</div>
                <div class="text-2xl font-semibold">{{ $empresasTotal }}</div>
                <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                    Total registradas en el sistema
                </div>
            </div>
        @else
            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
                <div class="text-sm text-gray-500 dark:text-neutral-400">Mi empresa</div>
                <div class="text-base font-semibold truncate" title="{{ $empresaNombre }}">
                    {{ $empresaNombre ?: 'Sin empresa asignada' }}
                </div>
                <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                    Contexto filtrado por empresa
                </div>
            </div>
        @endif

        {{-- Entidades --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
            <div class="text-sm text-gray-500 dark:text-neutral-400">Entidades</div>
            <div class="text-2xl font-semibold">{{ $entidadesTotal }}</div>
            <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                @if ($isAdmin)
                    Total del sistema
                @else
                    Solo de mi empresa
                @endif
            </div>
        </div>

        {{-- Proyectos + Monto total --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
            <div class="text-sm text-gray-500 dark:text-neutral-400">Proyectos</div>

            <div class="text-2xl font-semibold">{{ $proyectosTotal }}</div>

            <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                @if ($isAdmin)
                    Total del sistema
                @else
                    Solo de mi empresa
                @endif
            </div>

            {{-- ✅ Monto total --}}
            <div class="mt-3 pt-3 border-t dark:border-neutral-800">
                <div class="text-xs text-gray-500 dark:text-neutral-400">Monto total de proyectos</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                    {{ number_format((float) $proyectosMontoTotal, 2, '.', ',') }}
                </div>
            </div>
        </div>
    </div>

    {{-- RECENTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Últimos usuarios --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Usuarios recientes</h2>
            </div>

            <div class="divide-y dark:divide-neutral-800">
                @forelse ($ultimosUsuarios as $u)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium truncate">{{ $u->name }}</div>
                            <div class="text-sm text-gray-600 dark:text-neutral-300 truncate">{{ $u->email }}</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                {{ optional($u->created_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <span
                            class="text-xs px-2 py-1 rounded border
                            {{ $u->active ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}">
                            {{ $u->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-sm text-gray-600 dark:text-neutral-300">
                        No hay usuarios para mostrar.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Últimos proyectos --}}
        <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-800 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Proyectos recientes</h2>

                {{-- Si quieres habilitar "Ver todos" solo si tiene empresa: --}}
                {{-- @if ($empresaId)
                    <a href="{{ route('proyectos') }}" class="text-sm text-gray-700 dark:text-neutral-200 hover:underline">
                        Ver todos
                    </a>
                @endif --}}
            </div>

            <div class="divide-y dark:divide-neutral-800">
                @forelse ($ultimosProyectos as $p)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium truncate">{{ $p->nombre }}</div>
                            <div class="text-sm text-gray-600 dark:text-neutral-300 truncate">
                                Código: <span class="font-medium">{{ $p->codigo }}</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                {{ optional($p->created_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-sm font-semibold">
                                {{ is_numeric($p->monto) ? number_format($p->monto, 2, '.', ',') : '0.00' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">Monto</div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-sm text-gray-600 dark:text-neutral-300">
                        No hay proyectos para mostrar.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
