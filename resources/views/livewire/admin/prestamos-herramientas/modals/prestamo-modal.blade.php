    {{-- ===================== MODAL NUEVO PRÉSTAMO ===================== --}}
    <x-ui.modal wire:key="prestamo-create-{{ $openModalPrestamo ? 'open' : 'closed' }}" model="openModalPrestamo"
        title="Nuevo Registro de Préstamo" maxWidth="sm:max-w-2xl md:max-w-5xl" onClose="$set('openModalPrestamo', false)">

        <div class="space-y-5">

            {{-- SECCIÓN 1: DESTINO DEL PRÉSTAMO --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        1</div>
                    <span class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Destino
                        del Préstamo</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

                    {{-- Entidad con autocomplete --}}
                    <div class="col-span-2 lg:col-span-1" x-data="{
                        query: '',
                        open: false,
                        entidades: @js($entidades->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()),
                        get suggestions() {
                            let valid = this.entidades.filter(e => e.nombre && e.nombre.trim() !== '');
                            if (!this.query.trim()) return valid.slice(0, 8);
                            const q = this.query.toUpperCase();
                            return valid.filter(e => e.nombre.toUpperCase().includes(q)).slice(0, 8);
                        }
                    }" x-init="$watch('$wire.entidad_id', v => {
                        if (!v) { query = ''; return; }
                        const found = entidades.find(e => e.id == v);
                        if (found && query !== found.nombre) query = found.nombre;
                    })">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Cliente <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" x-model="query" @focus="open = true"
                                @blur="setTimeout(() => open = false, 200)"
                                @input="open = true; $wire.set('entidad_id', '')"
                                placeholder="Buscar entidad o cliente..."
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm">
                            <div x-show="open && suggestions.length > 0" x-cloak
                                class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-52 overflow-y-auto">
                                <template x-for="item in suggestions" :key="item.id">
                                    <div @mousedown="query = item.nombre; $wire.set('entidad_id', item.id); open = false"
                                        class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-sm border-b dark:border-neutral-800 last:border-0 transition">
                                        <span class="font-semibold text-gray-900 dark:text-white"
                                            x-text="item.nombre"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('entidad_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Proyecto (Select2-style Autocomplete) --}}
                    <div class="col-span-2 lg:col-span-1" wire:key="proyecto-search-{{ $entidad_id }}"
                        x-data="{
                            query: '',
                            open: false,
                            proyectos: @js($this->proyectosFiltrados->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre])->values()),
                            get suggestions() {
                                let valid = this.proyectos.filter(p => p.nombre && p.nombre.trim() !== '');
                                if (!this.query.trim()) return valid.slice(0, 8);
                                const q = this.query.toUpperCase();
                                return valid.filter(p => p.nombre.toUpperCase().includes(q)).slice(0, 8);
                            }
                        }" x-init="$watch('$wire.proyecto_id', v => {
                            if (!v) { query = ''; return; }
                            const found = proyectos.find(p => p.id == v);
                            if (found && query !== found.nombre) query = found.nombre;
                        });
                        if ($wire.proyecto_id) {
                            const found = proyectos.find(p => p.id == $wire.proyecto_id);
                            if (found) query = found.nombre;
                        }">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Proyecto
                            Destino <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" x-model="query" @focus="open = true"
                                @blur="setTimeout(() => open = false, 200)"
                                @input="open = true; $wire.set('proyecto_id', '')"
                                placeholder="{{ $entidad_id ? 'Buscar proyecto...' : 'Primero seleccione entidad' }}"
                                {{ !$entidad_id ? 'disabled' : '' }}
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm disabled:opacity-50 disabled:bg-gray-50">

                            <div x-show="open && suggestions.length > 0" x-cloak
                                class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-52 overflow-y-auto">
                                <template x-for="item in suggestions" :key="item.id">
                                    <div @mousedown="query = item.nombre; $wire.set('proyecto_id', item.id); open = false"
                                        class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-sm border-b dark:border-neutral-800 last:border-0 transition">
                                        <span class="font-semibold text-gray-900 dark:text-white"
                                            x-text="item.nombre"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('proyecto_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                        @if ($entidad_id && $this->proyectosFiltrados->isEmpty())
                            <p class="text-amber-600 text-[10px] mt-1 italic">Esta entidad no tiene proyectos activos.
                            </p>
                        @endif
                    </div>

                    {{-- Agente de Servicio (a quién se le presta) con autocomplete --}}
                    <div class="col-span-2 lg:col-span-1" wire:key="agente-selector" x-data="{
                        agenteQuery: '',
                        openAgentes: false,
                        agenteList: @js($agentes->map(fn($a) => ['id' => $a->id, 'nombre' => $a->nombre])->values()),
                        get suggestions() {
                            const valid = this.agenteList.filter(a => a && a.nombre && a.nombre.trim() !== '');
                            if (!this.agenteQuery.trim()) return valid.slice(0, 8);
                            const q = this.agenteQuery.toUpperCase();
                            return valid.filter(a => a.nombre.toUpperCase().includes(q)).slice(0, 8);
                        }
                    }"
                        x-init="$watch('$wire.agente_id', v => {
                            if (!v) { agenteQuery = $wire.receptor_manual || ''; return; }
                            const found = agenteList.find(a => a.id == v);
                            if (found && agenteQuery !== found.nombre) agenteQuery = found.nombre;
                        })">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Responsable /
                            Agente de Servicio</label>
                        <div class="relative">
                            <input type="text" x-model="agenteQuery" @focus="openAgentes = true"
                                @blur="setTimeout(() => openAgentes = false, 200)"
                                @input="openAgentes = true; $wire.set('agente_id', ''); $wire.set('receptor_manual', agenteQuery)"
                                placeholder="Buscar agente o escribir nombre..."
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm">
                            <div x-show="openAgentes && suggestions.length > 0" x-cloak
                                class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-xl max-h-52 overflow-y-auto">
                                <template x-for="item in suggestions" :key="'a-' + item.id">
                                    <div @mousedown="agenteQuery = item.nombre; $wire.set('agente_id', item.id); $wire.set('receptor_manual', null); openAgentes = false"
                                        class="px-3 py-2.5 cursor-pointer hover:bg-indigo-50 dark:hover:bg-neutral-800 text-sm border-b dark:border-neutral-800 last:border-0 transition">
                                        <div class="flex items-center gap-2">
                                            <div class="size-2 rounded-full bg-indigo-500"></div>
                                            <span class="font-semibold text-gray-900 dark:text-white"
                                                x-text="item.nombre"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <p class="text-[9px] text-gray-500 mt-1 italic">Si no aparece en la lista, simplemente escriba
                            el nombre.</p>
                        @error('agente_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                        @error('receptor_manual')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Fecha Salida --}}
                    <div class="sm:col-span-12 lg:col-span-1">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Fecha de
                            Salida <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.live="fecha_prestamo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm" />
                    </div>

                    {{-- Retorno Estimado --}}
                    <div class="sm:col-span-12 lg:col-span-1">
                        <label class="block text-sm mb-1 font-bold text-indigo-600 dark:text-indigo-400">Retorno
                            Estimado</label>
                        <input type="date" wire:model="fecha_vencimiento"
                            class="w-full rounded-lg border px-3 py-2 bg-indigo-50/30 dark:bg-indigo-900/10 border-indigo-200 dark:border-indigo-800 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-sm" />
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: AGREGAR HERRAMIENTAS --}}
            <div class="border-t border-gray-100 dark:border-neutral-800 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        2</div>
                    <span
                        class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Herramientas
                        a Prestar</span>
                </div>

                {{-- Buscador de herramienta (select2-style) --}}
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end"
                    wire:key="tool-search-box-{{ count($items) }}" x-data="{
                        toolQuery: '',
                        openTools: false,
                        toolList: @js(isset($herramientas) ? $herramientas->map(fn($h) => ['id' => $h->id, 'nombre' => $h->nombre, 'codigo' => $h->codigo, 'disponible' => $h->stock_disponible, 'imagen' => $h->imagen])->values() : []),
                        get suggestions() {
                            const items = this.toolList.filter(h => h && h.nombre && String(h.nombre).trim() !== '' && h.disponible > 0);
                            if (!this.toolQuery.trim()) return items.slice(0, 8);
                            const q = this.toolQuery.toUpperCase();
                            return items.filter(h =>
                                String(h.nombre).toUpperCase().includes(q) || (h.codigo && String(h.codigo).toUpperCase().includes(q))
                            ).slice(0, 10);
                        }
                    }" x-init="$watch('$wire.item_herramienta_id', v => { if (!v) toolQuery = ''; })">

                    {{-- Input búsqueda herramienta --}}
                    <div class="sm:col-span-6 relative">
                        <label
                            class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Herramienta</label>
                        <input type="text" x-model="toolQuery" @focus="openTools = true"
                            @blur="setTimeout(() => openTools = false, 200)"
                            @input="openTools = true; $wire.set('item_herramienta_id', '')"
                            placeholder="Buscar por nombre o código..."
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-sm">

                        {{-- Dropdown con preview --}}
                        <div x-show="openTools && suggestions.length > 0" x-cloak
                            class="absolute z-50 left-0 right-0 top-full mt-1 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-2xl max-h-64 overflow-y-auto">
                            <template x-for="h in suggestions" :key="'h-' + h.id">
                                <div @mousedown="$wire.set('item_herramienta_id', h.id); toolQuery = h.nombre; openTools = false"
                                    class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800 transition border-b dark:border-neutral-800 last:border-0">
                                    <div
                                        class="shrink-0 size-9 rounded-lg bg-gray-100 dark:bg-neutral-800 overflow-hidden flex items-center justify-center">
                                        <template x-if="h.imagen">
                                            <img :src="'/storage/' + h.imagen" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!h.imagen">
                                            <svg class="size-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white text-[13px] truncate"
                                            x-text="h.nombre"></div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-[10px] text-indigo-500 uppercase"
                                                x-text="h.codigo || '—'"></span>
                                            <span class="text-[10px] text-gray-400">·</span>
                                            <span class="text-[10px]"
                                                :class="h.disponible > 0 ? 'text-emerald-600 font-bold' :
                                                    'text-red-500 font-bold'"
                                                x-text="'Disp: ' + h.disponible"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Preview original removido a petición del usuario --}}
                        @error('item_herramienta_id')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cantidad --}}
                    <div class="sm:col-span-3">
                        <label
                            class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Cantidad</label>
                        <input type="number" wire:model.live="item_cantidad" min="1"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-center font-bold text-sm">
                        @error('item_cantidad')
                            <p class="text-red-500 text-[10px] mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botón agregar --}}
                    <div class="sm:col-span-3">
                        <label class="block text-sm mb-1 opacity-0">Agregar</label>
                        <button type="button" wire:click="addItem" @disabled(!$item_herramienta_id)
                            class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition text-sm font-bold cursor-pointer flex items-center justify-center gap-2 shadow-md shadow-indigo-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Agregar
                        </button>
                    </div>
                </div>

                {{-- Error global items --}}
                @error('items')
                    <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                @enderror

                {{-- TABLA DE ÍTEMS --}}
                @if (count($items) > 0)
                    <div wire:key="items-table-container"
                        class="mt-4 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                        <div
                            class="px-4 py-2 bg-gray-50 dark:bg-neutral-800/50 border-b border-gray-200 dark:border-neutral-700 flex justify-between items-center">
                            <span
                                class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-neutral-400">Herramientas
                                a prestar</span>
                            <span
                                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">{{ count($items) }}
                                ítem(s)</span>
                        </div>
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50/50 dark:bg-neutral-900/50 text-gray-500 dark:text-neutral-400 text-[11px] uppercase">
                                <tr>
                                    <th class="px-4 py-2 w-14 font-bold text-center">
                                        <svg class="size-4 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </th>
                                    <th class="px-4 py-2 font-bold text-left">Herramienta</th>
                                    <th class="px-4 py-2 font-bold text-center">Disp.</th>
                                    <th class="px-4 py-2 font-bold text-center">Cant.</th>
                                    <th class="px-4 py-2 font-bold text-center">Nuevo Disp.</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @foreach ($items as $idx => $it)
                                    <tr wire:key="herramienta-item-{{ $idx }}-{{ $it['herramienta_id'] ?? 'nuevo' }}"
                                        class="hover:bg-gray-50/50 dark:hover:bg-neutral-800/20">
                                        <td class="px-4 py-2.5">
                                            @if (isset($it['imagen']) && $it['imagen'])
                                                <img src="{{ Storage::url($it['imagen']) }}"
                                                    alt="{{ $it['nombre'] }}"
                                                    class="size-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-neutral-700 bg-white">
                                            @else
                                                <div
                                                    class="size-10 rounded-lg bg-gray-100 dark:bg-neutral-800 flex items-center justify-center ring-1 ring-gray-200 dark:ring-neutral-700">
                                                    <svg class="size-6 text-gray-400 dark:text-neutral-500"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <div class="font-semibold text-gray-900 dark:text-neutral-100 text-[13px]">

                                                {{ $it['nombre'] }}</div>
                                            <div class="font-mono text-[10px] text-indigo-500 uppercase">
                                                {{ $it['codigo'] }}</div>
                                        </td>
                                        <td
                                            class="px-4 py-2.5 text-center text-gray-500 dark:text-neutral-400 font-medium">
                                            {{ $it['disponible'] }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 font-black text-sm">{{ $it['cantidad'] }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @php $nuevo = $it['disponible'] - $it['cantidad']; @endphp
                                            <span
                                                class="font-black text-sm {{ $nuevo < 0 ? 'text-red-600 animate-pulse' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $nuevo }}</span>
                                        </td>
                                        <td class="px-2 py-2.5 text-center">
                                            <button type="button" wire:click="removeItem({{ $idx }})"
                                                class="size-7 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition flex items-center justify-center cursor-pointer">
                                                <svg class="size-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div
                        class="mt-3 rounded-xl border-2 border-dashed border-gray-200 dark:border-neutral-700 p-6 text-center">
                        <p class="text-[13px] text-gray-400 dark:text-neutral-500">Busque y agregue herramientas al
                            préstamo</p>
                    </div>
                @endif
            </div>

            {{-- SECCIÓN 3: EVIDENCIA FOTOGRÁFICA --}}
            <div class="border-t border-gray-100 dark:border-neutral-800 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        3</div>
                    <span
                        class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Evidencia
                        de Salida</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-sm mb-1 font-medium text-gray-700 dark:text-neutral-300">Evidencia
                            (Fotos o PDF) del estado al salir <span class="text-red-500">*</span></label>
                        <input type="file" wire:model.live="temp_fotos_salida" multiple
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4 file:cursor-pointer
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-400
                                border border-gray-200 dark:border-neutral-700 rounded-lg bg-white dark:bg-neutral-900 p-1" />
                        <div wire:loading wire:target="temp_fotos_salida" class="text-xs text-indigo-500 mt-1">
                            Subiendo
                            archivos...</div>
                        @if ($fotos_salida && is_array($fotos_salida) && count($fotos_salida) > 0)
                            <div class="mt-2 flex gap-2 flex-wrap">
                                @foreach ($fotos_salida as $idx => $f)
                                    @if ($f)
                                        <div class="relative group">
                                            @php
                                                $isPdf = strtolower($f->getClientOriginalExtension()) === 'pdf';
                                            @endphp
                                            @if ($isPdf)
                                                <div class="size-16 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 flex flex-col items-center justify-center text-red-500"
                                                    title="{{ $f->getClientOriginalName() }}">
                                                    <svg class="size-6" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    <span class="text-[8px] font-bold mt-1 uppercase">PDF</span>
                                                </div>
                                            @else
                                                <img src="{{ $f->temporaryUrl() }}"
                                                    class="size-16 rounded-lg object-cover border border-gray-200 shadow-sm shadow-gray-200/50"
                                                    title="{{ $f->getClientOriginalName() }}">
                                            @endif

                                            {{-- Botón Eliminar Foto --}}
                                            <button type="button" wire:click="removeFotoSalida({{ $idx }})"
                                                class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition shadow-md z-10 cursor-pointer">
                                                <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @error('fotos_salida.*')
                                <span class="text-xs text-red-500 mt-1 block italic">{{ $message }}</span>
                            @enderror
                        @endif
                        @error('fotos_salida')
                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            {{-- SECCIÓN 4: FIRMA DIGITAL --}}
            <div class="border-t border-gray-100 dark:border-neutral-800 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="size-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        4</div>
                    <span class="text-sm font-bold text-gray-700 dark:text-neutral-200 uppercase tracking-wide">Firma
                        de Conformidad</span>
                </div>

                <x-ui.signature-pad model="firma_salida" label="Firma de quien recibe" />
            </div>
        </div>

        @slot('footer')
            <div class="w-full grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
                <button type="button" @click="$set('openModalPrestamo', false)"
                    class="w-full sm:w-auto px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="savePrestamo" wire:loading.attr="disabled"
                    @disabled(!$entidad_id || !$proyecto_id || !$fecha_prestamo || empty($items) || empty($fotos_salida) || !$firma_salida)
                    class="px-8 py-2 rounded-lg cursor-pointer bg-black text-white hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-neutral-900/10 tracking-wide flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="savePrestamo">Confirmar Salida
                        ({{ count($items) ?? 0 }})</span>
                    <span wire:loading wire:target="savePrestamo">Procesando...</span>
                </button>
            </div>
        @endslot
    </x-ui.modal>
