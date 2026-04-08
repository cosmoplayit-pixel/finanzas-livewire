@props([
    'path' => null,
    'sizeClass' => 'w-8 h-8',
    'svgClass' => 'w-3.5 h-3.5',
])

@php
    $isPdf = $path && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    $base = 'flex items-center justify-center rounded-lg border transition-all';
@endphp

@if ($path)
    @if ($isPdf)
        <a href="{{ asset('storage/' . $path) }}" target="_blank"
            class="{{ $sizeClass }} {{ $base }} cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
            title="Ver PDF">
            <svg class="{{ $svgClass }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="9" y1="13" x2="15" y2="13"/>
                <line x1="9" y1="17" x2="15" y2="17"/>
                <line x1="9" y1="9" x2="11" y2="9"/>
            </svg>
        </a>
    @else
        <button type="button"
            @click.stop="$dispatch('open-image-modal', { url: '{{ asset('storage/' . $path) }}' })"
            class="{{ $sizeClass }} {{ $base }} cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
            title="Ver imagen">
            <svg class="{{ $svgClass }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
        </button>
    @endif
@else
    <span class="{{ $sizeClass }} {{ $base }} bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600 dark:border-neutral-700"
        title="Sin comprobante">
        <svg class="{{ $svgClass }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
        </svg>
    </span>
@endif
