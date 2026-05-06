<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name  = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     * Solo se permite cambiar el nombre. El email es gestionado por el administrador.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->fill($validated);
        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>

@section('title', 'Perfil')
<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualiza tu nombre. Para cambiar el correo, contacta al administrador.')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            {{-- Nombre: editable --}}
            <flux:input wire:model="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name" />

            {{-- Email: solo lectura --}}
            <div>
                <flux:input
                    :value="$this->email"
                    :label="__('Correo electrónico')"
                    type="email"
                    disabled
                    readonly
                />
                <p class="mt-1.5 text-xs text-gray-500 dark:text-zinc-400">
                    El correo electrónico solo puede ser modificado por un administrador.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                    {{ __('Guardar') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>