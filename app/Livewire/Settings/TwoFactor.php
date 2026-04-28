<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Component;

class TwoFactor extends Component
{
    public bool $showingQrCode = false;
    public bool $showingConfirmation = false;
    public bool $showingRecoveryCodes = false;
    public string $code = '';

    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable)
    {
        $user = Auth::user();
        $enable($user);

        // Forzamos la persistencia de los campos específicos de Fortify
        $user->forceFill([
            'two_factor_secret' => $user->two_factor_secret,
            'two_factor_recovery_codes' => $user->two_factor_recovery_codes,
        ])->save();

        $this->showingQrCode = true;
        $this->showingRecoveryCodes = true;
        
        $this->dispatch('toast', type: 'success', message: 'Autenticación de dos pasos habilitada.');
    }

    public function showRecoveryCodes()
    {
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        $generate(Auth::user());

        $this->showingRecoveryCodes = true;
        $this->dispatch('toast', type: 'success', message: 'Códigos de recuperación regenerados.');
    }

    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable)
    {
        $disable(Auth::user());

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
        
        $this->dispatch('toast', type: 'success', message: 'Autenticación de dos pasos deshabilitada.');
    }

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function render()
    {
        return view('livewire.settings.two-factor');
    }
}
