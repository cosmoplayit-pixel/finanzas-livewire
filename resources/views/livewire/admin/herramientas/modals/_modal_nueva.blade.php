    {{-- ===================== MODAL NUEVA / EDITAR HERRAMIENTA ===================== --}}
    <x-ui.modal wire:key="herramienta-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal" :title="$isExistingCode ? 'Editar Herramienta' : 'Registro de Herramienta'"
        maxWidth="sm:max-w-xl md:max-w-3xl" onClose="closeModal">

        <div class="space-y-4">

            {{-- Banner modo edición --}}
            @if ($isExistingCode)
                <div
                    class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 px-3 py-2 text-xs text-amber-700 dark:text-amber-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Estás editando un registro existente. Podés modificar todos los datos incluido el
                        stock.</span>
                </div>
            @endif

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

                {{-- Nombre --}}
                <div class="col-span-2 lg:col-span-2" x-data="{
                    query: @entangle('nombre'),
                    open: false,
                    get items() { return $wire.codigosData; },
                    get suggestions() {
                            if (!this.query || this.query.length < 2) return [];
                            const q = this.query.toUpperCase();
                            return this.items.filter(c => c.nombre.toUpperCase().includes(q)).slice(0, 8);
                        }
                
                        <
                        label class ="block text-sm mb-1 font-medium
                    text-gray-700 dark:text-neutral-300">Nombre del Equipo
                    <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="open = true"
                            @blur="setTimeout(() => open = false, 200)"
                            placeholder="Buscar equipo existente o escribir nuevo..." autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">

                        {{-- Sugerencias por nombre con foto --}}
                        <div x-show="open && suggestions.length > 0" x-cloak wire:ignore
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-xl max-h-64 overflow-y-auto overflow-x-hidden">
                            <template x-for="(item, index) in suggestions" :key="'name-' + index">
                                <div @mousedown="$wire.call('buscarPorCodigo', item.codigo); open = false"
                                    class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-neutral-800 transition border-b dark:border-neutral-800 last:border-0">
                                    {{-- Foto --}}
                                    <div
                                        class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-100 dark:bg-neutral-800 flex items-center justify-center">
                                        <template x-if="item.imagen">
                                            <img :src="item.imagen" class="w-full h-full object-cover"
                                                alt="">
                                        </template>
                                        <template x-if="!item.imagen">
                                            <svg class="w-5 h-5 text-gray-300 dark:text-neutral-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M11 4a7 7 0 110 14A7 7 0 0111 4zM21 21l-4.35-4.35" />
                                            </svg>
                                        </template>
                                    </div>
                                    {{-- Info --}}
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white text-[11px] truncate"
                                            x-text="item.nombre"></div>
                                        <div class="text-gray-400 dark:text-neutral-500 text-[10px] font-mono"
                                            x-text="item.codigo || 'Sin categoría'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('nombre')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Categoría (Antes Código) --}}
                <div class="col-span-1 lg:col-span-1" x-data="{
                    query: @entangle('codigo'),
                    open: false,
                    predefinidas: ['REDES', 'CAMARAS', 'FIBRA OPTICA', 'AIRES ACONDICIONADOS', 'ELECTRICIDAD', 'OBRA CIVIL', 'COMPUTACION E IMPRESORAS', 'HERRAMIENTAS', 'EQUIPOS', 'MATERIALES', 'MOBILIARIO'],
                    get categoriasDB() { return $wire.categoriasData || []; },
                    get allCategorias() {
                        let fromDB = Array.isArray(this.categoriasDB) ? this.categoriasDB : Object.values(this.categoriasDB);
                        let combined = [...new Set([...this.predefinidas, ...fromDB])].filter(c => c);
                        return combined.sort((a, b) => a.localeCompare(b));
                    },
                    get suggestions() {
                        const q = (this.query || '').trim().toUpperCase();
                        if (!q) return this.allCategorias.slice(0, 20);
                        return this.allCategorias.filter(c => c && c.toUpperCase().includes(q)).slice(0, 20);
                    },
                    select(val) {
                        this.query = val;
                        this.open = false;
                    }
                
                    <
                    label class ="block text-sm mb-1 font-medium
                    text-gray-700 dark:text-neutral-300">Categoría</label>
                    <div class="relative">
                        <input type="text" x-model="query" @focus="open = true"
                            @input="open = true; query = query.toUpperCase()"
                            @blur="setTimeout(() => open = false, 200)" placeholder="Ej: HERRAMIENTAS"
                            autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 uppercase text-sm font-mono tracking-wider">
                        <div x-show="open && suggestions.length > 0" x-cloak wire:ignore
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-xl max-h-52 overflow-y-auto overflow-x-hidden">
                            <template x-for="(c, index) in suggestions" :key="'cat-' + index">
                                <div @mousedown="select(c)"
                                    class="flex items-center px-3 py-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-neutral-800 transition border-b dark:border-neutral-800 last:border-0">
                                    <span class="text-[11px] font-mono font-bold text-gray-700 dark:text-neutral-300"
                                        x-text="c"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('codigo')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Estado Físico --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Estado Físico
                        <span class="text-red-500">*</span></label>
                    <select wire:model="estado_fisico"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm cursor-pointer">
                        <option value="bueno">Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                        <option value="baja">Baja / Descarte</option>
                    </select>
                </div>

                {{-- Marca --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Marca</label>
                    <input type="text" wire:model="marca" placeholder="Ej: DeWalt"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                </div>

                {{-- Modelo --}}
                <div class="col-span-1 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Modelo /
                        Ref.</label>
                    <input type="text" wire:model="modelo" placeholder="DCD771..."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">
                </div>

                {{-- Unidad --}}
                <div class="col-span-1 lg:col-span-1" x-data="{
                    query: @entangle('unidad'),
                    open: false,
                    unidades: [
                        { label: 'PIEZA', value: 'PZA' },
                        { label: 'JUEGO', value: 'JGO' },
                        { label: 'KIT', value: 'KIT' },
                        { label: 'UNIDAD', value: 'UND' },
                        { label: 'PAR', value: 'PAR' },
                        { label: 'METRO', value: 'MT' },
                        { label: 'METRO CUADRADO', value: 'M²' },
                        { label: 'METRO CÚBICO', value: 'M³' },
                        { label: 'KILOGRAMO', value: 'KG' },
                        { label: 'GRAMO', value: 'GR' },
                        { label: 'LITRO', value: 'LT' },
                        { label: 'GALÓN', value: 'GLN' },
                        { label: 'CAJA', value: 'CJA' },
                        { label: 'BOLSA', value: 'BLS' },
                        { label: 'ROLLO', value: 'RLL' },
                        { label: 'SACO', value: 'SCO' },
                        { label: 'TAMBOR', value: 'TMB' },
                        { label: 'BALDE', value: 'BLD' },
                        { label: 'LOTE', value: 'LTE' },
                        { label: 'GLOBAL', value: 'GLB' },
                    ],
                    get unidadesDB() { return $wire.unidadesData || []; },
                    get allUnidades() {
                        let fromDB = Array.isArray(this.unidadesDB) ? this.unidadesDB : Object.values(this.unidadesDB);
                        const dbOpts = fromDB.filter(u => !this.unidades.some(ud => ud.value === u)).map(u => ({ label: u, value: u }));
                        let combined = [...this.unidades, ...dbOpts];
                        return combined.sort((a, b) => a.label.localeCompare(b.label));
                    },
                    get suggestions() {
                        const q = (this.query || '').trim().toUpperCase();
                        if (!q) return this.allUnidades.slice(0, 20);
                        return this.allUnidades.filter(u =>
                            u.label.toUpperCase().includes(q) || u.value.toUpperCase().includes(q)
                        ).slice(0, 20);
                    },
                    select(val) {
                        this.query = val;
                        this.open = false;
                    }
                }">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Unidad
                        Medida</label>
                    <div class="relative">
                        <input type="text" x-model="query" @focus="open = true"
                            @input="open = true; query = query.toUpperCase()"
                            @blur="setTimeout(() => open = false, 200)" placeholder="Buscar unidad..."
                            autocomplete="off"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm uppercase">
                        <div x-show="open && suggestions.length > 0" x-cloak wire:ignore
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-xl max-h-52 overflow-y-auto overflow-x-hidden">
                            <template x-for="(u, index) in suggestions" :key="'unit-' + index">
                                <div @mousedown="select(u.value)"
                                    class="flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-neutral-800 transition border-b dark:border-neutral-800 last:border-0">
                                    <span class="text-[11px] text-gray-800 dark:text-neutral-200"
                                        x-text="u.label"></span>
                                    <span
                                        class="text-[10px] font-mono font-bold text-gray-400 dark:text-neutral-500 ml-2"
                                        x-text="u.value"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Stock: modo crear = Total / modo editar = Disponible --}}
                @if ($isExistingCode)
                    <div class="col-span-1 lg:col-span-1" wire:key="stock-field-edit">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">
                            Stock Disponible <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model.live="stock_disponible" min="0" disabled
                            class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-neutral-800 border-gray-300 dark:border-neutral-700 text-gray-500 dark:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold text-center opacity-75">
                        <p class="text-[10px] text-gray-400 dark:text-neutral-500 mt-1">
                            Prestado: <strong>{{ $stock_prestado }}</strong> &mdash; Total:
                            <strong>{{ $stock_disponible + $stock_prestado }}</strong>
                        </p>
                        @error('stock_disponible')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div class="col-span-1 lg:col-span-1" wire:key="stock-field-create">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">
                            Stock Total <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model.live="stock_total" min="0"
                            class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold text-center">
                        @error('stock_total')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Precio Unitario --}}
                <div class="col-span-2 lg:col-span-1">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">P. Unitario (Bs)
                        <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" wire:model.live="precio_unitario" min="0"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-right font-medium">
                </div>

                {{-- Fotografía --}}
                <div class="col-span-2 lg:col-span-3">
                    <x-ui.scanner model="imagen" label="Fotografía o Ficha Técnica" :file="$imagen"
                        :existingUrl="$isExistingCode && $foundImagenPath && !$deleteFoundImagen
                            ? asset('storage/' . $foundImagenPath)
                            : null" :existingName="$foundImagenPath ? basename($foundImagenPath) : null" deleteModel="deleteFoundImagen" />
                </div>

                {{-- Descripción --}}
                <div class="col-span-2 lg:col-span-3">
                    <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Descripción /
                        Detalles Adicionales</label>
                    <textarea wire:model="descripcion" rows="2" placeholder="Accesorios incluidos, historial de service..."
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm resize-none"></textarea>
                </div>
            </div>

            {{-- RESUMEN VALORIZACIÓN --}}
            <div
                class="rounded-xl border bg-gray-50/50 dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden divide-y divide-gray-100 dark:divide-neutral-800">
                <div class="px-4 py-2 flex justify-between items-center bg-gray-100/50 dark:bg-black/10">
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-neutral-400">Resumen
                        Almacén</span>
                    <span class="text-[10px] font-bold text-gray-400">VALORES CALCULADOS</span>
                </div>
                <div class="p-3 grid grid-cols-2 gap-4">
                    <div class="space-y-0.5">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Stock Disponible</div>
                        <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $stock_disponible }}
                            {{ $unidad ?: 'Unid.' }}</div>
                    </div>
                    <div class="space-y-0.5 text-right">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Inversión Total estimada</div>
                        <div class="text-sm font-black text-gray-900 dark:text-neutral-100">Bs.
                            {{ number_format((float) $precio_total, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
                <button type="button" wire:click="closeModal"
                    class="w-full sm:w-auto px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" @disabled(!$nombre || !$precio_unitario || (!$isExistingCode && !$stock_total) || ($isExistingCode && !$foundHerramientaId))
                    class="w-full sm:w-auto px-6 py-2 rounded-lg cursor-pointer transition text-sm font-black shadow-lg {{ $isExistingCode ? 'bg-blue-500 hover:bg-blue-600  text-white' : 'bg-black hover:bg-neutral-800 shadow-black/10 text-white' }} disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove
                        wire:target="save">{{ $isExistingCode ? 'Guardar Cambios' : 'Guardar Registro' }}</span>
                    <span wire:loading wire:target="save">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>
