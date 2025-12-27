<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Inicia sesión en tu cuenta')" :description="__('Ingrese su correo electrónico y contraseña a continuación para iniciar sesión')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input name="email" :label="__('Dirección de correo electrónico')" :value="old('email')" type="email"
                required autofocus autocomplete="email" placeholder="email@ejemplo.com" />

            <!-- Password -->
            <div class="relative">
                <flux:input name="password" :label="__('Contraseña')" type="password" required
                    autocomplete="current-password" :placeholder="__('Contraseña')" viewable />
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Ingresar') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts.auth>