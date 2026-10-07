@props([
    'title',
    'description',
])

<div class="flex flex-col gap-2 text-center">
    <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
