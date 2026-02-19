@props([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
])

<?php if ($expandable && $heading): ?>

<ui-disclosure {{ $attributes->class('group/navgroup') }} @if ($expanded === true) open @endif
    data-flux-navlist-group>
    <button type="button"
        class="group/navgroup-btn mb-[2px] flex h-10 w-full items-center rounded-xl px-2
               text-zinc-600 hover:bg-zinc-900/5 hover:text-zinc-900
               lg:h-9
               focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40
               dark:text-white/75 dark:hover:bg-white/[7%] dark:hover:text-white">
        <div class="flex w-7 items-center justify-center">
            <flux:icon.chevron-down
                class="hidden size-3! text-zinc-400 group-data-open/navgroup-btn:block dark:text-white/50" />
            <flux:icon.chevron-right
                class="block size-3! text-zinc-400 group-data-open/navgroup-btn:hidden dark:text-white/50" />
        </div>

        <span class="flex-1 truncate text-[13px] font-semibold tracking-tight">
            {{ $heading }}
        </span>

        {{-- subtle divider dot / spacer for balance --}}
        <span
            class="ms-2 hidden h-1.5 w-1.5 rounded-full bg-zinc-200 group-hover/navgroup-btn:bg-zinc-300 dark:bg-white/10 dark:group-hover/navgroup-btn:bg-white/20 lg:block"></span>
    </button>

    <div class="relative hidden space-y-[2px] ps-8 data-open:block" @if ($expanded === true) data-open @endif>
        {{-- left rail --}}
        <div class="absolute inset-y-1 start-0 ms-4 w-px bg-zinc-200/80 dark:bg-white/20"></div>

        {{ $slot }}
    </div>
</ui-disclosure>

<?php elseif ($heading): ?>

<div {{ $attributes->class('block space-y-[2px]') }}>
    <div class="px-2 pt-3 pb-2">
        <div class="text-[11px] font-semibold tracking-wider text-zinc-400 uppercase dark:text-white/35">
            {{ $heading }}
        </div>
    </div>

    <div class="space-y-[2px]">
        {{ $slot }}
    </div>
</div>

<?php else: ?>

<div {{ $attributes->class('block space-y-[2px]') }}>
    {{ $slot }}
</div>

<?php endif; ?>
