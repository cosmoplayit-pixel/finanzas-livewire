@section('title', 'Proyectos')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

    {{-- HEADER (RESPONSIVE) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Proyectos</h1>

        @can('proyectos.create')
            <button wire:click="openCreate" class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:opacity-90">
                Nuevo Proyecto
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
        {{-- Buscar --}}
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre o Código"
            class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

        {{-- Selects derecha --}}
        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">

            {{-- Entidad --}}
            <select wire:model.live="entidadFilter"
                class="w-full sm:w-auto
                    border rounded px-3 py-2
                    bg-white text-gray-900 border-gray-300
                    dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                    focus:outline-none focus:ring-2 focus:ring-offset-0
                    focus:ring-gray-300 dark:focus:ring-neutral-600">
                <option value="all">Todas las Entidades</option>
                @foreach ($entidades as $en)
                    <option value="{{ $en->id }}" title="{{ $en->nombre }}">
                        {{ \Illuminate\Support\Str::limit($en->nombre, 30) }}
                    </option>
                @endforeach
            </select>

            {{-- Estado --}}
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

            {{-- PerPage --}}
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
        @forelse ($proyectos as $p)
            <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $p->nombre }}</div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1">
                            {{ $p->entidad?->nombre ?? '—' }}
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if ($p->active)
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
                        <span class="font-medium">{{ $p->id }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Código:</span>
                        <span class="truncate">{{ $p->codigo ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Monto:</span>
                        <span>Bs {{ number_format((float) $p->monto, 2, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Inicio:</span>
                        <span>{{ $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '—' }}</span>
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Fin:</span>
                        <span>{{ $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '—' }}</span>
                    </div>
                </div>

                {{-- Acciones --}}
                @canany(['proyectos.update', 'proyectos.toggle'])
                    <div class="mt-4 flex  gap-2">
                        @can('proyectos.update')
                            <button wire:click="openEdit({{ $p->id }})"
                                class="w-full px-3 py-1 rounded border border-gray-300 hover:bg-gray-50
                                       dark:border-neutral-700 dark:hover:bg-neutral-800">
                                Editar
                            </button>
                        @endcan

                        @can('proyectos.toggle')
                            <button type="button"
                                wire:click="$dispatch('swal:toggle-active-proyecto', {
                                    id: {{ $p->id }},
                                    active: @js($p->active),
                                    name: @js($p->nombre)
                                })"
                                class="w-full px-3 py-1 rounded text-sm font-medium
                                {{ $p->active
                                    ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                    : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                {{ $p->active ? 'Desactivar' : 'Activar' }}
                            </button>
                        @endcan
                    </div>
                @endcanany
            </div>
        @empty
            <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- ==========================================
        TABLET + DESKTOP: TABLA
    =========================================== --}}
    <div
        class="hidden md:block
               overflow-x-auto overflow-y-hidden
               border rounded
               bg-white dark:bg-neutral-800">
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

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('entidad_id')">
                        Entidad
                        @if ($sortField === 'entidad_id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap hidden lg:table-cell"
                        wire:click="sortBy('codigo')">
                        Código
                        @if ($sortField === 'codigo')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('monto')">
                        Monto
                        @if ($sortField === 'monto')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap hidden lg:table-cell"
                        wire:click="sortBy('fecha_inicio')">
                        Inicio
                        @if ($sortField === 'fecha_inicio')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap hidden lg:table-cell"
                        wire:click="sortBy('fecha_fin')">
                        Fin
                        @if ($sortField === 'fecha_fin')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    @canany(['proyectos.update', 'proyectos.toggle'])
                        <th class="p-3 whitespace-nowrap w-40 lg:w-56">
                            Acciones
                        </th>
                    @endcanany
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                @foreach ($proyectos as $p)
                    <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900">
                        <td class="p-3 whitespace-nowrap">{{ $p->id }}</td>

                        <td class="p-3">
                            <span class="truncate block max-w-[280px]" title="{{ $p->entidad?->nombre ?? '-' }}">
                                {{ $p->entidad?->nombre ?? '-' }}
                            </span>
                        </td>

                        <td class="p-3">
                            <span class="truncate block max-w-[320px]" title="{{ $p->nombre }}">
                                {{ $p->nombre }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap hidden lg:table-cell">{{ $p->codigo ?? '-' }}</td>

                        <td class="p-3 whitespace-nowrap">
                            Bs {{ number_format((float) $p->monto, 2, ',', '.') }}
                        </td>

                        <td class="p-3 whitespace-nowrap hidden lg:table-cell">
                            {{ $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '-' }}
                        </td>

                        <td class="p-3 whitespace-nowrap hidden lg:table-cell">
                            {{ $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '-' }}
                        </td>

                        <td class="p-3 whitespace-nowrap">
                            @if ($p->active)
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

                        @canany(['proyectos.update', 'proyectos.toggle'])
                            <td class="p-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @can('proyectos.update')
                                        <button wire:click="openEdit({{ $p->id }})"
                                            class="px-3 py-1 cursor-pointer rounded border border-gray-300 hover:bg-gray-50
                                                   dark:border-neutral-700 dark:hover:bg-neutral-800">
                                            Editar
                                        </button>
                                    @endcan

                                    @can('proyectos.toggle')
                                        <button type="button"
                                            wire:click="$dispatch('swal:toggle-active-proyecto', {
                                                id: {{ $p->id }},
                                                active: @js($p->active),
                                                name: @js($p->nombre)
                                            })"
                                            class="px-3 py-1 cursor-pointer rounded text-sm font-medium
                                            {{ $p->active
                                                ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                                : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                            {{ $p->active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        @endcanany
                    </tr>
                @endforeach

                @if ($proyectos->count() === 0)
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400" colspan="9">
                            Sin resultados.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $proyectos->links() }}
    </div>

    {{-- MODAL (solo create/update) --}}
    @canany(['proyectos.create', 'proyectos.update'])
        @if ($openModal)
            <div wire:key="proyectos-modal" class="fixed inset-0 z-50">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closeModal"></div>

                {{-- Dialog --}}
                <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4">
                    <div
                        class="w-full
                           h-[100dvh] sm:h-auto
                           sm:max-h-[90vh]
                           sm:max-w-xl md:max-w-2xl
                           bg-white dark:bg-neutral-900
                           text-gray-700 dark:text-neutral-200
                           border border-gray-200 dark:border-neutral-800
                           rounded-none sm:rounded-xl
                           overflow-hidden shadow-xl">

                        {{-- Header (sticky) --}}
                        <div
                            class="sticky top-0 z-10 px-5 py-4 flex justify-between items-center
                               bg-gray-50 dark:bg-neutral-900
                               border-b border-gray-200 dark:border-neutral-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                                {{ $proyectoId ? 'Editar Proyecto' : 'Nuevo Proyecto' }}
                            </h2>

                            <button type="button" wire:click="closeModal"
                                class="inline-flex items-center justify-center size-9 rounded-md
                                   text-gray-500 hover:text-gray-900 hover:bg-gray-100
                                   dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                                ✕
                            </button>
                        </div>

                        {{-- Body (scroll) --}}
                        <div
                            class="p-5 space-y-4 overflow-y-auto
                               h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]">

                            {{-- Entidad --}}
                            <div>
                                <label class="block text-sm mb-1">Entidad</label>
                                <select wire:model="entidad_id"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700">
                                    <option value="">Seleccione...</option>
                                    @foreach ($entidades as $en)
                                        <option value="{{ $en->id }}" title="{{ $en->nombre }}">
                                            {{ $en->sigla ? $en->sigla . ' - ' : '' }}
                                            {{ \Illuminate\Support\Str::limit($en->nombre, 30) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('entidad_id')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nombre --}}
                            <div>
                                <label class="block text-sm mb-1">Nombre</label>
                                <input wire:model="nombre" autocomplete="off"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('nombre')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Código --}}
                            <div>
                                <label class="block text-sm mb-1">Código</label>
                                <input wire:model="codigo" autocomplete="off"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('codigo')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Monto --}}
                            <div>
                                <label class="block text-sm mb-1">Monto del Proyecto</label>
                                <input type="number" step="0.01" min="0" wire:model="monto"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('monto')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Fechas --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm mb-1">Fecha inicio</label>
                                    <input type="date" wire:model="fecha_inicio"
                                        class="w-full rounded border px-3 py-2
                                           bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700
                                           text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2
                                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                    @error('fecha_inicio')
                                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm mb-1">Fecha fin</label>
                                    <input type="date" wire:model="fecha_fin"
                                        class="w-full rounded border px-3 py-2
                                           bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700
                                           text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2
                                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                    @error('fecha_fin')
                                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div>
                                <label class="block text-sm mb-1">Descripción</label>
                                <textarea wire:model="descripcion" rows="3"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                                @error('descripcion')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        {{-- Footer (sticky) --}}
                        <div
                            class="sticky bottom-0 px-5 py-4 flex justify-end gap-2
                               bg-gray-50 dark:bg-neutral-900
                               border-t border-gray-200 dark:border-neutral-800">
                            <button wire:click="closeModal"
                                class="px-4 py-2 rounded border
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-700 dark:text-neutral-200
                                   hover:bg-gray-100 dark:hover:bg-neutral-800">
                                Cancelar
                            </button>

                            <button wire:click="save" class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
                                {{ $proyectoId ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    @endcanany

</div>
