@section('title', 'Entidades')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

    {{-- HEADER (RESPONSIVE) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Entidades</h1>

        <button wire:click="openCreate" class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:opacity-90">
            Nueva Entidad
        </button>
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
        <input type="search" wire:model.live="search" placeholder="Buscar Nombre, Sigla o Correo"
            class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

        {{-- Selects derecha --}}
        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">

            {{-- Empresa (solo Admin) --}}
            @if (auth()->user()?->hasRole('Administrador'))
                <select wire:model.live="empresaFilter"
                    class="w-full sm:w-auto
                        border rounded px-3 py-2
                        bg-white text-gray-900 border-gray-300
                        dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                        focus:outline-none focus:ring-2 focus:ring-offset-0
                        focus:ring-gray-300 dark:focus:ring-neutral-600">
                    <option value="all">Todas las Empresas</option>
                    @foreach ($empresas as $emp)
                        <option value="{{ $emp->id }}" title="{{ $emp->nombre }}">
                            {{ \Illuminate\Support\Str::limit($emp->nombre, 35) }}
                        </option>
                    @endforeach
                </select>
            @endif

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
        @forelse ($entidades as $e)
            <div class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $e->nombre }}</div>
                    </div>

                    <div class="shrink-0">
                        @if ($e->active)
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
                        <span class="font-medium">{{ $e->id }}</span>
                    </div>

                    @if (auth()->user()?->hasRole('Administrador'))
                        <div class="flex justify-between gap-3">
                            <span class="font-medium">Empresa:</span>
                            <span title="{{ $e->empresa?->nombre ?? '-' }}">
                                {{ $e->empresa?->nombre ?? '-' }}
                            </span>
                        </div>
                    @endif

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Sigla:</span> {{ $e->sigla ?? '—' }}
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Email:</span> {{ $e->email ?? '—' }}
                    </div>

                    <div class="flex justify-between gap-3">
                        <span class="font-medium">Teléfono:</span> {{ $e->telefono ?? '—' }}
                    </div>
                </div>
            </div>

            {{-- Acciones (mismo patrón Usuarios: botón outline + botón semántico full width) --}}
            <div class="mt-4 space-y-2">
                <button wire:click="openEdit({{ $e->id }})"
                    class="w-full px-3 py-2 rounded border border-gray-300 hover:bg-gray-50
                           dark:border-neutral-700 dark:hover:bg-neutral-800">
                    Editar
                </button>

                <button type="button"
                    wire:click="$dispatch('swal:toggle-active-entidad', {
                        id: {{ $e->id }},
                        active: @js($e->active),
                        name: @js($e->nombre)
                    })"
                    class="w-full px-3 py-2 rounded text-sm font-medium
                    {{ $e->active
                        ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                        : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                    {{ $e->active ? 'Desactivar' : 'Activar' }}
                </button>
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

                    @if (auth()->user()?->hasRole('Administrador'))
                        <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('empresa_id')">
                            Empresa
                            @if ($sortField === 'empresa_id')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </th>
                    @endif

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap hidden lg:table-cell"
                        wire:click="sortBy('sigla')">
                        Sigla
                        @if ($sortField === 'sigla')
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

                    <th class="p-3 whitespace-nowrap">
                        Teléfono
                    </th>

                    <th class="p-3 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-3 whitespace-nowrap w-40 lg:w-56">
                        Acciones
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                @foreach ($entidades as $e)
                    <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900">
                        <td class="p-3 whitespace-nowrap">{{ $e->id }}</td>

                        @if (auth()->user()?->hasRole('Administrador'))
                            <td class="p-3">
                                <span class="truncate block max-w-[260px]" title="{{ $e->empresa?->nombre ?? '-' }}">
                                    {{ $e->empresa?->nombre ?? '-' }}
                                </span>
                            </td>
                        @endif

                        <td class="p-3">
                            <span class="truncate block max-w-[320px]" title="{{ $e->nombre }}">
                                {{ $e->nombre }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap hidden lg:table-cell">{{ $e->sigla ?? '-' }}</td>

                        <td class="p-3 hidden lg:table-cell">
                            <span class="truncate block max-w-[260px]" title="{{ $e->email ?? '-' }}">
                                {{ $e->email ?? '-' }}
                            </span>
                        </td>

                        <td class="p-3 whitespace-nowrap">{{ $e->telefono ?? '-' }}</td>

                        <td class="p-3 whitespace-nowrap">
                            @if ($e->active)
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

                        <td class="p-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <button wire:click="openEdit({{ $e->id }})"
                                    class="px-3 py-1 cursor-pointer rounded border border-gray-300 hover:bg-gray-50
                                           dark:border-neutral-700 dark:hover:bg-neutral-800">
                                    Editar
                                </button>

                                <button type="button"
                                    wire:click="$dispatch('swal:toggle-active-entidad', {
                                        id: {{ $e->id }},
                                        active: @js($e->active),
                                        name: @js($e->nombre)
                                    })"
                                    class="px-3 py-1 cursor-pointer rounded text-sm font-medium
                                    {{ $e->active
                                        ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500/20 dark:text-red-200 dark:hover:bg-red-500/30'
                                        : 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500/20 dark:text-green-200 dark:hover:bg-green-500/30' }}">
                                    {{ $e->active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if ($entidades->count() === 0)
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400"
                            colspan="{{ auth()->user()?->hasRole('Administrador') ? 8 : 7 }}">
                            Sin resultados.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $entidades->links() }}
    </div>



    {{--  MODAL --}}
    @if ($openModal)
        <div wire:key="entidades-modal" class="fixed inset-0 z-50">
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
                            {{ $entidadId ? 'Editar Entidad' : 'Nueva Entidad' }}
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

                        {{-- Empresa (solo Admin) --}}
                        @if (auth()->user()?->hasRole('Administrador'))
                            <div>
                                <label class="block text-sm mb-1">Empresa</label>

                                {{-- ✅ IMPORTANTE: propiedad correcta --}}
                                <select wire:model="empresa_id_form"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700">
                                    <option value="">Seleccione.</option>
                                    @foreach ($empresas as $em)
                                        <option value="{{ $em->id }}">{{ $em->nombre }}</option>
                                    @endforeach
                                </select>

                                @error('empresa_id_form')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        {{-- Nombre --}}
                        <div>
                            <label class="block text-sm mb-1">Nombre</label>
                            <input wire:model="nombre"
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

                        {{-- Sigla --}}
                        <div>
                            <label class="block text-sm mb-1">Sigla</label>
                            <input wire:model="sigla"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('sigla')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm mb-1">Email</label>
                            <input wire:model="email" type="email"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('email')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div>
                            <label class="block text-sm mb-1">Teléfono</label>
                            <input wire:model="telefono"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('telefono')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dirección --}}
                        <div>
                            <label class="block text-sm mb-1">Dirección</label>
                            <textarea wire:model="direccion" rows="2"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                            @error('direccion')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Observaciones --}}
                        <div>
                            <label class="block text-sm mb-1">Observaciones</label>
                            <textarea wire:model="observaciones" rows="2"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                            @error('observaciones')
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
                            {{ $entidadId ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
