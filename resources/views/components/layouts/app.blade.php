<title>@yield('title', config('app.name'))</title>

<x-layouts.app.sidebar>
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
