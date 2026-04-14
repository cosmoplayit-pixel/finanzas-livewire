    {{-- FILTROS --}}
    <div x-data="{ openFilters: false }" class="relative mb-6" wire:ignore.self>
        <div
            class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden shadow-sm">
            <div class="py-3 px-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6 lg:col-span-8">
                        <label
                            class="block text-[11px] font-bold uppercase mb-1 text-gray-500 dark:text-neutral-400">Búsqueda</label>
                        <div class="relative">
                            <input type="search" wire:model.live.debounce.300ms="search"
                                placeholder="Herramienta, código, proyecto, entidad, agente..."
                                class="w-full rounded-lg border px-3 py-2 pl-10 bg-white dark:bg-neutral-900 border-gray-200 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition text-sm" />
                            <svg class="absolute left-3 top-2.5 size-4 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2">
                        <label
                            class="block text-[11px] font-bold uppercase mb-1 text-gray-500 dark:text-neutral-400">Mostrar</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-200 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer text-sm font-medium">
                            <option value="10">10 Filas</option>
                            <option value="25">25 Filas</option>
                            <option value="50">50 Filas</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2">
                        <label
                            class="block text-[11px] font-bold uppercase mb-1 text-gray-500 dark:text-neutral-400">Más
                            Filtros</label>
                        <button type="button" @click.stop="openFilters = !openFilters"
                            class="w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-200 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 transition cursor-pointer text-sm font-bold shadow-sm">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Filtros
                            @php $activeFilters = count($f_estado) + ($f_proyecto_id !== 'all' ? 1 : 0) + ($f_entidad_id !== 'all' ? 1 : 0) + ($f_fecha_desde ? 1 : 0) + ($f_herramienta_id !== 'all' ? 1 : 0); @endphp
                            @if ($activeFilters > 0)
                                <span
                                    class="inline-flex items-center justify-center size-5 rounded-full bg-indigo-600 text-white text-[10px] font-black">{{ $activeFilters }}</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL FLOTANTE REDISEÑADO --}}
        <div x-show="openFilters" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2" @click.outside="openFilters = false"
            @keydown.escape.window="openFilters = false"
            class="absolute right-0 top-11 w-full sm:w-[380px] z-50 rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-neutral-800">
                <div class="flex items-center gap-2">
                    <div
                        class="size-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-extrabold text-gray-900 dark:text-neutral-100 text-xs">Filtros Avanzados</p>
                        @if ($activeFilters > 0)
                            <p class="text-[9px] text-indigo-500 font-bold leading-none">{{ $activeFilters }} filtro(s)
                                activo(s)</p>
                        @endif
                    </div>
                </div>
                <button @click="openFilters = false"
                    class="size-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-3 max-h-[65vh] overflow-y-auto">

                {{-- Estado del Préstamo --}}
                <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Estado</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['activo' => 'En Obra', 'finalizado' => 'Devuelto'] as $val => $label)
                            <label
                                class="flex items-center gap-2 p-2 rounded-xl border cursor-pointer transition
                                {{ in_array($val, $f_estado) ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 dark:border-indigo-500' : 'border-gray-200 dark:border-neutral-700 hover:bg-gray-50' }}">
                                <input type="checkbox" wire:model.live="f_estado" value="{{ $val }}"
                                    class="rounded text-indigo-600 focus:ring-0 border-gray-300">
                                <span
                                    class="text-[12px] font-bold text-gray-700 dark:text-neutral-200 truncate">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Rango de Fechas --}}
                <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Fecha Préstamo</p>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model.live="f_fecha_desde" title="Desde"
                            class="w-full text-xs rounded-lg border px-2 py-1.5 bg-gray-50/50 dark:bg-neutral-800 border-gray-200 dark:border-neutral-700 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <input type="date" wire:model.live="f_fecha_hasta" title="Hasta"
                            class="w-full text-xs rounded-lg border px-2 py-1.5 bg-gray-50/50 dark:bg-neutral-800 border-gray-200 dark:border-neutral-700 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>

                <div class="border-t border-gray-50 dark:border-neutral-800 my-1"></div>

                {{-- Entidad Autocomplete --}}
                <div class="space-y-1" x-data="{
                    query: '',
                    open: false,
                    list: @js($entidades->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()),
                    get suggestions() {
                        let valid = this.list.filter(e => e.nombre && e.nombre.trim() !== '');
                        if (!this.query.trim()) return valid.slice(0, 8);
                        const q = this.query.toUpperCase();
                        return valid.filter(e => e.nombre.toUpperCase().includes(q)).slice(0, 8);
                    }
                }" x-init="$watch('$wire.f_entidad_id', v => {
                    if (v === 'all') { query = ''; return; }
                    const f = list.find(e => e.id == v);
                    if (f) query = f.nombre;
                });
                if ($wire.f_entidad_id !== 'all') { const f = list.find(e => e.id == $wire.f_entidad_id); if (f) query = f.nombre; }">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Entidad / Cliente</p>
                    <div class="relative">
                        <input type="text" x-model="query" @focus="open = true"
                            @blur="setTimeout(() => open = false, 200)"
                            @input="open = true; $wire.set('f_entidad_id', 'all')" placeholder="Buscar entidad..."
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-[13px] shadow-sm">
                        <div x-show="open && suggestions.length > 0" x-cloak
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                            <template x-for="item in suggestions" :key="'f-ent-' + item.id">
                                <div @mousedown="query = item.nombre; $wire.set('f_entidad_id', item.id); open = false"
                                    class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-[12px] border-b dark:border-neutral-800 last:border-0 font-medium font-semibold text-gray-900 dark:text-white transition">
                                    <span x-text="item.nombre"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Proyecto Autocomplete --}}
                <div class="space-y-1" wire:key="f-pro-wrap-{{ $f_entidad_id }}" x-data="{
                    query: '',
                    open: false,
                    list: @js($this->proyectosFiltroByEntidad->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values()),
                    get suggestions() {
                        let valid = this.list.filter(p => p.nombre && p.nombre.trim() !== '');
                        if (!this.query.trim()) return valid.slice(0, 8);
                        const q = this.query.toUpperCase();
                        return valid.filter(p => p.nombre.toUpperCase().includes(q)).slice(0, 8);
                    }
                }"
                    x-init="$watch('$wire.f_proyecto_id', v => {
                        if (v === 'all') { query = ''; return; }
                        const f = list.find(p => p.id == v);
                        if (f) query = f.nombre;
                    });
                    if ($wire.f_proyecto_id !== 'all') { const f = list.find(p => p.id == $wire.f_proyecto_id); if (f) query = f.nombre; }">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Proyecto</p>
                    <div class="relative">
                        <input type="text" x-model="query" @focus="open = true"
                            @blur="setTimeout(() => open = false, 200)"
                            @input="open = true; $wire.set('f_proyecto_id', 'all')"
                            placeholder="{{ $f_entidad_id !== 'all' ? 'Buscar proyecto...' : 'Seleccione entidad' }}"
                            {{ $f_entidad_id === 'all' ? 'disabled' : '' }}
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 {{ $f_entidad_id !== 'all' ? 'border-indigo-400' : 'border-gray-300' }} dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-[13px] shadow-sm disabled:opacity-50">
                        <div x-show="open && suggestions.length > 0" x-cloak
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                            <template x-for="item in suggestions" :key="'f-pro-' + item.id">
                                <div @mousedown="query = item.nombre; $wire.set('f_proyecto_id', item.id); open = false"
                                    class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-[12px] border-b dark:border-neutral-800 last:border-0 font-medium font-semibold text-gray-900 dark:text-white transition">
                                    <span x-text="item.nombre"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-50 dark:border-neutral-800 my-1"></div>

                {{-- Herramienta Autocomplete Compacto --}}
                <div class="space-y-1" x-data="{
                    hQuery: '',
                    openH: false,
                    allTools: @js($herramientas->map(fn($h) => ['id' => $h->id, 'nombre' => $h->nombre, 'codigo' => $h->codigo])->values()),
                    get selected() {
                        if ($wire.f_herramienta_id === 'all') return null;
                        return this.allTools.find(h => h.id == $wire.f_herramienta_id);
                    },
                    get suggestions() {
                        if (!this.hQuery.trim()) return [];
                        const q = this.hQuery.toUpperCase();
                        return this.allTools.filter(h => h.nombre.toUpperCase().includes(q) || (h.codigo && h.codigo.toUpperCase().includes(q))).slice(0, 8);
                    }
                }" x-init="$watch('$wire.f_herramienta_id', v => { if (v === 'all') hQuery = ''; })">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Herramienta</p>
                    <template x-if="selected">
                        <div
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 dark:border-indigo-500">
                            <div class="flex-1 min-w-0">
                                <span class="font-bold text-indigo-700 dark:text-indigo-300 text-[12px] truncate block"
                                    x-text="selected.nombre"></span>
                                <span class="text-[9px] text-indigo-500 font-mono font-bold"
                                    x-text="selected.codigo"></span>
                            </div>
                            <button @click="$wire.set('f_herramienta_id', 'all'); hQuery = ''"
                                class="text-indigo-400 hover:text-red-500 transition"><svg class="size-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg></button>
                        </div>
                    </template>
                    <template x-if="!selected">
                        <div class="relative">
                            <input type="text" x-model="hQuery" @focus="openH = true"
                                @blur="setTimeout(() => openH = false, 200)" @input="openH = true"
                                placeholder="Buscar herramienta..."
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-[13px] shadow-sm">
                            <div x-show="openH && suggestions.length > 0" x-cloak
                                class="absolute z-50 left-0 right-0 bottom-full mb-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <template x-for="h in suggestions" :key="'fh-' + h.id">
                                    <div @mousedown="$wire.set('f_herramienta_id', h.id); hQuery = ''; openH = false"
                                        class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 font-semibold text-gray-900 dark:text-white text-[12px] border-b last:border-0">
                                        <div class="font-bold" x-text="h.nombre"></div>
                                        <div class="text-[9px] text-indigo-500 font-mono font-black"
                                            x-text="h.codigo"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer --}}
            <div
                class="px-5 py-3 bg-gray-50 dark:bg-neutral-800/50 border-t border-gray-100 flex justify-between items-center gap-3 rounded-b-2xl">
                <button wire:click="clearFilters" @click="openFilters = false"
                    class="text-[11px] font-black text-red-500 uppercase tracking-widest">Limpiar</button>
                <button @click="openFilters = false"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[11px] font-black shadow-lg shadow-indigo-500/20 uppercase tracking-widest transition leading-none">Aplicar</button>
            </div>
        </div>
    </div>

