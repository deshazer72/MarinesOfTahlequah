<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="p-6 md:p-8">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
