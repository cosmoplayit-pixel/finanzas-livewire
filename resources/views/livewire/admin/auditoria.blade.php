{{-- resources/views/livewire/admin/auditoria.blade.php --}}
<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                🛡️ Registro de Auditoría
            </h1>
            <p class="text-sm text-gray-500 dark:text-zinc-400 mt-0.5">
                Historial de todas las acciones realizadas en el sistema.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por descripción o usuario..."
            icon="magnifying-glass" class="flex-1" />
        <flux:select wire:model.live="filterEvent" class="sm:w-44">
            <flux:select.option value="">Todos los eventos</flux:select.option>
            @foreach ($events as $event)
                <flux:select.option value="{{ $event }}">{{ ucfirst($event) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Tabla --}}
    <div
        class="overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800 text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-zinc-400">Fecha</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-zinc-400">Usuario</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-zinc-400">Evento</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-zinc-400">Descripción</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-zinc-400">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/40 transition-colors">
                            {{-- Fecha --}}
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-zinc-400 text-xs">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </td>

                            {{-- Usuario --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($log->causer)
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="h-6 w-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-xs font-semibold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($log->causer->name, 0, 1)) }}
                                        </div>
                                        <span class="text-gray-800 dark:text-zinc-200 text-xs font-medium">
                                            {{ $log->causer->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs italic">Sistema</span>
                                @endif
                            </td>

                            {{-- Evento badge --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $badgeClass = match ($log->event) {
                                        'created'
                                            => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                        'updated'
                                            => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                        'deleted'
                                            => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                                        default
                                            => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                                    };
                                    $eventLabel = match ($log->event) {
                                        'created' => '✦ Creado',
                                        'updated' => '✎ Editado',
                                        'deleted' => '✕ Eliminado',
                                        default => $log->event ?? 'Log',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded border text-xs font-medium {{ $badgeClass }}">
                                    {{ $eventLabel }}
                                </span>
                            </td>

                            {{-- Descripción --}}
                            <td class="px-4 py-3 text-gray-700 dark:text-zinc-300 max-w-xs">
                                <div class="truncate text-xs" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </div>
                                @if ($log->properties && count($log->properties) > 0 && isset($log->properties['attributes']))
                                    <div class="mt-0.5 text-xs text-gray-400 dark:text-zinc-500 truncate">
                                        {{ collect($log->properties['attributes'])->keys()->take(3)->join(', ') }}
                                    </div>
                                @endif
                            </td>

                            {{-- IP --}}
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400 dark:text-zinc-500 font-mono">
                                {{ $log->properties['ip'] ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400 dark:text-zinc-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8 w-8 text-gray-300 dark:text-zinc-700" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm">No se encontraron registros.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($logs->hasPages())
            <div class="border-t border-gray-200 dark:border-zinc-800 px-4 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
