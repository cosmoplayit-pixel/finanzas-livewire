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

    /**
     * Reenviar el correo de verificación.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();

        $this->dispatch('toast', type: 'success', message: 'Se ha enviado un nuevo enlace de verificación a su correo.');
    }
}; ?>

@section('title', 'Perfil')
<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualiza tu nombre. Para cambiar el correo, contacta al administrador.')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            {{-- Nombre: editable --}}
            <flux:input wire:model="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name" />

            {{-- Email: solo lectura con estado de verificación --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <flux:label>{{ __('Correo electrónico') }}</flux:label>
                    
                    @if (auth()->user()->hasVerifiedEmail())
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full border border-green-200 dark:border-green-500/20">
                            <flux:icon.shield-check variant="micro" class="size-3.5" />
                            {{ __('Verificado') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-500/20">
                            <flux:icon.shield-exclamation variant="micro" class="size-3.5" />
                            {{ __('No verificado') }}
                        </span>
                    @endif
                </div>

                <flux:input
                    :value="$this->email"
                    type="email"
                    disabled
                    readonly
                />
                
                <p class="mt-1.5 text-xs text-gray-500 dark:text-zinc-400">
                    El correo electrónico solo puede ser modificado por un administrador.
                </p>

                @if (! auth()->user()->hasVerifiedEmail())
                    <div class="mt-2">
                        <flux:button wire:click="sendVerification" variant="ghost" size="sm" class="text-xs font-normal">
                            {{ __('¿No recibiste el correo? Reenviar enlace') }}
                        </flux:button>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                    {{ __('Guardar') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>