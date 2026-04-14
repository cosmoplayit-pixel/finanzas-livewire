{{-- ===================== MODAL EDITAR TITULAR ===================== --}}
<x-ui.modal wire:key="edit-titular-{{ $openModalEditTitular ? 'open' : 'closed' }}" model="openModalEditTitular"
    title="Editar Nombre del Titular" maxWidth="sm:max-w-md" onClose="$set('openModalEditTitular', false)">

    <div class="space-y-4">
        <div x-data="{ len: {{ strlen($editTitularNombre) }} }">
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-[11px] font-black uppercase text-gray-500 dark:text-neutral-400">
                    Nombre completo <span class="text-red-500">*</span>
                </label>
                <span class="text-[11px] tabular-nums"
                    :class="len > 150 ? 'text-red-500 font-bold' : (len > 130 ? 'text-blue-500' :
                        'text-gray-400 dark:text-neutral-500')">
                    <span x-text="len"></span>/150
                </span>
            </div>
            <input type="text" wire:model="editTitularNombre" wire:keydown.enter="saveEditTitular"
                x-on:input="len = $el.value.length" maxlength="150" placeholder="Nombre del titular..."
                class="w-full rounded-lg border px-3 py-2.5 text-sm bg-white dark:bg-neutral-900 text-gray-900 dark:text-white focus:ring-2 focus:outline-none transition
                    @error('editTitularNombre') border-red-400 dark:border-red-600 focus:ring-red-500/20 @else border-gray-300 dark:border-neutral-700 focus:ring-blue-500/30 focus:border-blue-400 dark:focus:border-blue-600 @enderror">
            @error('editTitularNombre')
                <span class="text-[11px] text-red-500 italic mt-1 block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @slot('footer')
        <div class="flex justify-end gap-3">
            <button type="button" @click="openModalEditTitular = false"
                class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-500 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                Cancelar
            </button>
            <button type="button" wire:click="saveEditTitular" wire:loading.attr="disabled"
                class="px-8 py-2 rounded-lg cursor-pointer bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-black transition shadow-lg shadow-blue-500/20">
                <span wire:loading.remove wire:target="saveEditTitular">Guardar</span>
                <span wire:loading wire:target="saveEditTitular">Guardando...</span>
            </button>
        </div>
    @endslot

</x-ui.modal>
