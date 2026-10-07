@props(['title' => null])
<x-layouts::public :title="$title">
    {{ $slot }}
</x-layouts::public>
