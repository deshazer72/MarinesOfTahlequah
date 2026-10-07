<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('password.edit')" :current="request()->routeIs('password.edit')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance')" :current="request()->routeIs('appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden my-4" />

    <div class="flex-1 self-stretch max-md:pt-4">
        <flux:heading size="lg">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading class="mb-6 text-zinc-400">{{ $subheading ?? '' }}</flux:subheading>

        <div class="w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
