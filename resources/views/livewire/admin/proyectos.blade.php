@section('title', 'Proyectos')
<div class="p-6 space-y-4" :title="__('Dashboard')">

    {{-- INICIO - NOMBRE DEL MODULO ACTUAL Y BOTON PARA CREAR PROYECTO --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Proyectos</h1>
        <button wire:click="openCreate" class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
            Nuevo Proyecto
        </button>
    </div>
    {{-- FIN - NOMBRE DEL MODULO ACTUAL Y BOTON PARA CREAR PROYECTO --}}

    {{-- INICIO - ALERTAS --}}
    @if (session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif
    {{-- FIN - ALERTAS --}}

    {{-- INICIO - FILTROS --}}
    <div class="flex items-center gap-3">
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre o Código"
            class="w-full max-w-md border rounded px-3 py-2" />

        <div class="flex items-center gap-3 ml-auto">

            {{-- Entidad: sigla + nombre corto --}}
            <select wire:model.live="entidadFilter"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0 dark:focus:ring-neutral-600">
                <option value="all">Todas las Entidades</option>
                @foreach ($entidades as $en)
                    <option value="{{ $en->id }}" title="{{ $en->nombre }}">
                        {{ $en->sigla ? $en->sigla . ' - ' : '' }}
                        {{ \Illuminate\Support\Str::limit($en->nombre, 30) }}
                    </option>
                @endforeach
            </select>

            <select wire:model.live="status"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0 dark:focus:ring-neutral-600">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>

            <select wire:model.live="perPage"
                class="border rounded px-3 py-2 bg-white text-gray-900
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0 dark:focus:ring-neutral-600">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>

        </div>
    </div>
    {{-- FIN - FILTROS --}}

    {{-- INICIO - TABLA --}}
    <div class="overflow-x-auto border rounded">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200">
                <tr class="text-left">
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('id')">
                        ID @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('entidad_id')">
                        Entidad @if ($sortField === 'entidad_id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('nombre')">
                        Nombre @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('codigo')">
                        Código @if ($sortField === 'codigo')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('monto')">
                        Monto @if ($sortField === 'monto')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('fecha_inicio')">
                        Inicio @if ($sortField === 'fecha_inicio')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('fecha_fin')">
                        Fin @if ($sortField === 'fecha_fin')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('active')">
                        Estado @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                    <th class="p-3 w-56">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($proyectos as $p)
                    <tr class="border-t">
                        <td class="p-3">{{ $p->id }}</td>
                        <td class="p-3">{{ $p->entidad?->sigla ?? '-' }}</td>
                        <td class="p-3">{{ $p->nombre }}</td>
                        <td class="p-3">{{ $p->codigo ?? '-' }}</td>

                        <td class="p-3">
                            Bs {{ number_format((float) $p->monto, 2, ',', '.') }}
                        </td>

                        <td class="p-3">{{ $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '-' }}</td>
                        <td class="p-3">{{ $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '-' }}</td>

                        <td class="p-3">
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

                        <td class="p-3 space-x-2">
                            <button wire:click="openEdit({{ $p->id }})"
                                class="px-3 py-1 rounded border hover:bg-gray-50">
                                Editar
                            </button>

                            <button type="button"
                                wire:click="$dispatch('swal:toggle-active-proyecto', { id: {{ $p->id }}, active: {{ $p->active ? 'true' : 'false' }}, name: @js($p->nombre) })"
                                class="px-3 py-1 rounded text-sm font-medium {{ $p->active ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30' : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                {{ $p->active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </td>
                    </tr>
                @endforeach

                @if ($proyectos->count() === 0)
                    <tr>
                        <td class="p-3" colspan="9">Sin resultados.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    {{-- FIN - TABLA --}}

    <div>
        {{ $proyectos->links() }}
    </div>

    {{-- INICIO - MODAL --}}
    @if ($openModal)
        <div wire:key="proyectos-modal"
            class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50">
            <div
                class="w-full max-w-lg rounded-lg border overflow-hidden bg-white text-gray-700
                dark:bg-neutral-900 dark:text-neutral-200 border-gray-200 dark:border-neutral-800">

                <div
                    class="px-5 py-4 flex justify-between items-center bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                        {{ $proyectoId ? 'Editar Proyecto' : 'Nuevo Proyecto' }}
                    </h2>
                    <button wire:click="closeModal"
                        class="text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white">✕</button>
                </div>

                <div class="p-5 space-y-4">

                    <div>
                        <label class="block text-sm mb-1">Entidad</label>
                        <select wire:model="entidad_id"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
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

                    <div>
                        <label class="block text-sm mb-1">Nombre</label>
                        <input wire:model="nombre" autocomplete="off"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
                        @error('nombre')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Código</label>
                        <input wire:model="codigo" autocomplete="off"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
                        @error('codigo')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Monto del Proyecto</label>
                        <input type="number" step="0.01" min="0" wire:model="monto"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
                        @error('monto')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Fecha inicio</label>
                            <input type="date" wire:model="fecha_inicio"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
                            @error('fecha_inicio')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Fecha fin</label>
                            <input type="date" wire:model="fecha_fin"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
                            @error('fecha_fin')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Descripción</label>
                        <textarea wire:model="descripcion" rows="3"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                            border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                            focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                        @error('descripcion')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div
                    class="px-5 py-4 flex justify-end gap-2 bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-800">
                    <button wire:click="closeModal"
                        class="px-4 py-2 rounded border border-gray-300 dark:border-neutral-700
                        text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800">
                        Cancelar
                    </button>
                    <button wire:click="save"
                        class="px-4 py-2 rounded bg-gray-900 text-white hover:opacity-90 dark:bg-white dark:text-black">
                        {{ $proyectoId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
    {{-- FIN - MODAL --}}

</div>
