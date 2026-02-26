    {{-- MODAL: Eliminar inversión completa (requiere contraseña) --}}
    <x-ui.modal wire:key="delete-all-{{ $openEliminarTodoModal ? 'open' : 'closed' }}" model="openEliminarTodoModal"
        title="Eliminar inversión completa" maxWidth="sm:max-w-xl" onClose="closeEliminarTodoModal">

        <div class="space-y-3">
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                Esta acción eliminará <b>TODO</b> el registro de la inversión y revertirá bancos/operaciones.
                Es una acción irreversible.
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                    Confirme con su contraseña
                </label>

                <flux:input wire:model.defer="deleteAllPassword" name="deleteAllPassword" type="password" required
                    autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />
                @error('deleteAllPassword')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeEliminarTodoModal"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                <button type="button" wire:click="confirmarEliminarTodo"
                    class="px-4 py-2 rounded-lg cursor-pointer
                       bg-red-600 text-white hover:bg-red-700">
                    Eliminar todo
                </button>
            </div>
        @endslot
    </x-ui.modal>
