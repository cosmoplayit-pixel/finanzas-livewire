@php
    $code = $code ?? 'Error';
    $heading = $heading ?? 'Ocurrió un problema';
    $subheading = $subheading ?? 'Intenta nuevamente.';
    $helpText = $helpText ?? 'Si el problema persiste, contacta al administrador.';
    $detail = trim($detail ?? ($exception?->getMessage() ?? ''));

    $primaryUrl = $primaryUrl ?? route('dashboard');
    $primaryLabel = $primaryLabel ?? 'Volver al Panel';

    $prev = url()->previous();
    $current = url()->current();
    $secondaryUrl = $secondaryUrl ?? ($prev && $prev !== $current ? $prev : $primaryUrl);
    $secondaryLabel = $secondaryLabel ?? 'Regresar';
@endphp

@section('title', "{$code} - {$heading}")

<x-layouts.app>
    <div class="p-6">
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ $code }}</h1>
                    <p class="text-sm text-gray-600 dark:text-neutral-300">
                        {{ $heading }}
                    </p>
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <div class="max-w-3xl space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $subheading }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-300">
                            {{ $helpText }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ $primaryUrl }}"
                            class="inline-flex items-center justify-center rounded-md bg-black px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                            {{ $primaryLabel }}
                        </a>

                        <a href="{{ $secondaryUrl }}"
                            class="inline-flex items-center justify-center rounded-md border border-neutral-200 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-100 dark:hover:bg-neutral-800">
                            {{ $secondaryLabel }}
                        </a>
                    </div>

                    @if ($detail !== '')
                        <div
                            class="mt-4 rounded-lg border border-neutral-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-neutral-700 dark:bg-neutral-800/50 dark:text-neutral-200">
                            <div class="font-semibold mb-1">Detalles</div>
                            <div class="break-words">{{ $detail }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-sm text-gray-600 dark:text-neutral-300">
                    <div class="font-semibold text-gray-900 dark:text-neutral-100 mb-1">Sugerencias</div>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Verifica tu rol y permisos asignados.</li>
                        <li>Si eres administrador, revisa Roles y Permisos.</li>
                        <li>Revisa el middleware <code class="px-1 rounded bg-gray-100 dark:bg-neutral-800">role</code>
                            / <code class="px-1 rounded bg-gray-100 dark:bg-neutral-800">permission</code>.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
