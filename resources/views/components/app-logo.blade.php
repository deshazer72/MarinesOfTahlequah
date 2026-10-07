@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Marines of Tahlequah" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-red-700 text-white shadow-sm ring-1 ring-amber-500/30">
            <span class="text-xs font-black tracking-wider text-amber-300">MOT</span>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Marines of Tahlequah" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-red-700 text-white shadow-sm ring-1 ring-amber-500/30">
            <span class="text-xs font-black tracking-wider text-amber-300">MOT</span>
        </x-slot>
    </flux:brand>
@endif
