<?php

use App\Models\SlideshowImage;
use App\Services\PhotoStorageService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Title('Manage Slideshow - Marines of Tahlequah')]
#[Layout('layouts.app')]
class extends Component {
    use WithFileUploads;

    public $upload_file = null;
    public string $image_url = '';
    public string $title = '';
    public string $caption = '';
    public ?int $deletingId = null;
    public bool $showLibraryModal = false;

    public function mount(): void
    {
        if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin())) {
            abort(403);
        }
    }

    public function getAvailablePhotos(): array
    {
        $dir = public_path('MarinesPictures');
        if (!is_dir($dir)) {
            return [];
        }

        $files = scandir($dir);
        $photos = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $photos[] = [
                    'filename' => $file,
                    'url' => '/MarinesPictures/' . $file,
                ];
            }
        }

        return $photos;
    }

    public function selectLibraryPhoto(string $url): void
    {
        $this->image_url = $url;
        $this->upload_file = null;
        $this->showLibraryModal = false;

        Flux::toast(
            text: __('Photo selected from gallery.'),
            variant: 'success',
        );
    }

    public function addImage(): void
    {
        $this->validate([
            'upload_file' => 'nullable|image|max:10240',
            'image_url' => 'nullable|string|max:500',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        if (!$this->upload_file && empty(trim($this->image_url))) {
            $this->addError('upload_file', 'Please choose an image file, select from gallery, or provide an image URL.');
            return;
        }

        try {
            $finalUrl = trim($this->image_url);

            if ($this->upload_file) {
                $finalUrl = PhotoStorageService::store($this->upload_file, Auth::id());
            }

            $maxOrder = SlideshowImage::max('sort_order') ?? 0;

            SlideshowImage::create([
                'image_url' => $finalUrl,
                'title' => $this->title ?: 'Slideshow Photo',
                'caption' => $this->caption ?: null,
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            $this->reset(['upload_file', 'image_url', 'title', 'caption']);

            Flux::toast(
                text: __('Photo added to slideshow successfully.'),
                heading: __('Added'),
                variant: 'success',
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Slideshow upload error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            Flux::toast(
                text: __('Error adding photo: ') . $e->getMessage(),
                heading: __('Upload Failed'),
                variant: 'danger',
            );
        }
    }

    public function toggleActive(int $id): void
    {
        $photo = SlideshowImage::findOrFail($id);
        $photo->update(['is_active' => !$photo->is_active]);

        Flux::toast(
            text: $photo->is_active ? __('Photo activated in slideshow.') : __('Photo deactivated.'),
            variant: 'success',
        );
    }

    public function moveUp(int $id): void
    {
        $photo = SlideshowImage::findOrFail($id);
        $prev = SlideshowImage::where('sort_order', '<', $photo->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($prev) {
            $currentOrder = $photo->sort_order;
            $photo->update(['sort_order' => $prev->sort_order]);
            $prev->update(['sort_order' => $currentOrder]);
        }
    }

    public function moveDown(int $id): void
    {
        $photo = SlideshowImage::findOrFail($id);
        $next = SlideshowImage::where('sort_order', '>', $photo->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            $currentOrder = $photo->sort_order;
            $photo->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $currentOrder]);
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function deleteImage(): void
    {
        if ($this->deletingId) {
            SlideshowImage::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;

            Flux::toast(
                text: __('Photo removed from slideshow.'),
                heading: __('Removed'),
                variant: 'success',
            );
        }
    }

    public function with(): array
    {
        $photos = SlideshowImage::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return [
            'photos' => $photos,
            'activeCount' => $photos->where('is_active', true)->count(),
            'totalCount' => $photos->count(),
            'libraryPhotos' => $this->getAvailablePhotos(),
        ];
    }
}; ?>

<div class="py-6 sm:py-8 max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">{{ __('Manage Slideshow') }}</flux:heading>
                <flux:badge color="amber" size="sm">{{ $activeCount }} Active / {{ $totalCount }} Total</flux:badge>
            </div>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400 mt-1">
                {{ __('Upload and arrange photos featured on the home page slideshow carousel.') }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="ghost" :href="route('home')" icon="globe-alt" target="_blank">
                {{ __('Preview Home') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('dashboard')" icon="arrow-left" wire:navigate>
                {{ __('Dashboard') }}
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Add Photo Form --}}
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl sticky top-24">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Add New Slide') }}</h2>
                </div>

                <form wire:submit="addImage" class="space-y-4">
                    {{-- Upload from computer --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-1.5">
                            {{ __('Upload Photo from Device') }}
                        </label>
                        <input
                            type="file"
                            wire:model="upload_file"
                            accept="image/*"
                            class="block w-full text-xs text-zinc-600 dark:text-zinc-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-700 file:text-white hover:file:bg-red-600 cursor-pointer border border-zinc-300 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/80 p-1.5 focus:outline-hidden transition"
                        />
                        <div wire:loading wire:target="upload_file" class="mt-2 flex items-center gap-2 text-xs font-semibold text-amber-500 bg-amber-500/10 border border-amber-500/20 rounded-lg p-2.5">
                            <svg class="size-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Uploading photo from device... Please wait for preview before adding.</span>
                        </div>
                        @error('upload_file')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                        @if ($upload_file)
                            <div class="mt-3 relative h-36 w-full rounded-xl overflow-hidden border border-zinc-300 dark:border-zinc-700 bg-black/40">
                                <img src="{{ $upload_file->temporaryUrl() }}" class="h-full w-full object-cover" alt="Upload preview">
                                <button
                                    type="button"
                                    wire:click="$set('upload_file', null)"
                                    class="absolute top-2 right-2 rounded-md bg-black/70 p-1 text-white hover:bg-black/90 transition text-xs flex items-center gap-1"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Remove
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Or Browse Gallery Library --}}
                    <div>
                        <div class="relative flex py-1 items-center">
                            <div class="flex-grow border-t border-zinc-200 dark:border-zinc-800"></div>
                            <span class="flex-shrink mx-3 text-xs uppercase font-semibold text-zinc-400">Or Gallery Library</span>
                            <div class="flex-grow border-t border-zinc-200 dark:border-zinc-800"></div>
                        </div>

                        <button
                            type="button"
                            wire:click="$toggle('showLibraryModal')"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold border border-zinc-300 dark:border-zinc-700 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700/80 text-zinc-800 dark:text-zinc-200 transition cursor-pointer"
                        >
                            <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Browse Gallery Photos ({{ count($libraryPhotos) }})</span>
                        </button>

                        @if ($image_url && !$upload_file)
                            <div class="mt-3 relative h-36 w-full rounded-xl overflow-hidden border border-zinc-300 dark:border-zinc-700 bg-black/40">
                                <img src="{{ $image_url }}" class="h-full w-full object-cover" alt="Selected photo">
                                <div class="absolute bottom-0 inset-x-0 bg-black/75 px-2 py-1 text-[11px] text-zinc-300 truncate">
                                    {{ basename($image_url) }}
                                </div>
                                <button
                                    type="button"
                                    wire:click="$set('image_url', '')"
                                    class="absolute top-2 right-2 rounded-md bg-black/70 p-1 text-white hover:bg-black/90 transition text-xs flex items-center gap-1"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Clear
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Manual path or URL --}}
                    <div>
                        <flux:input wire:model="image_url" :label="__('Or Enter Image URL / Path')" placeholder="/MarinesPictures/IMG_8884.jpg or https://..." />
                    </div>

                    <flux:input wire:model="title" :label="__('Title / Caption (Optional)')" placeholder="e.g. Veterans Day Ceremony" />

                    <flux:textarea wire:model="caption" :label="__('Description (Optional)')" rows="2" placeholder="Optional details..." />

                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="upload_file, addImage"
                        class="w-full bg-red-700 hover:bg-red-600 text-white mt-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="upload_file">
                            {{ __('Add to Slideshow') }}
                        </span>
                        <span wire:loading wire:target="upload_file" class="flex items-center justify-center gap-2">
                            <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            {{ __('Uploading photo...') }}
                        </span>
                    </flux:button>
                </form>
            </div>
        </div>

        {{-- Photos Grid / Sequence --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Slideshow Sequence ({{ $photos->count() }} items)</span>
                <span class="text-xs text-zinc-400">Use arrows to adjust order</span>
            </div>

            @if ($photos->isEmpty())
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-12 text-center text-zinc-500">
                    {{ __('No slideshow images configured yet. Add your first photo above!') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($photos as $index => $photo)
                        <div wire:key="photo-item-{{ $photo->id }}" class="flex items-center gap-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-3 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition">
                            {{-- Order number --}}
                            <div class="flex size-7 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-500 dark:text-zinc-400">
                                {{ $index + 1 }}
                            </div>

                            {{-- Thumbnail --}}
                            <div class="size-16 shrink-0 rounded-lg overflow-hidden bg-black/40 border border-zinc-200 dark:border-zinc-700">
                                <img
                                    src="{{ $photo->image_url }}"
                                    alt="{{ $photo->title }}"
                                    class="h-full w-full object-cover"
                                    onerror="this.src='/MarinesPictures/IMG_8884.jpg'"
                                />
                            </div>

                            {{-- Details --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white truncate">
                                        {{ $photo->title ?: 'Slide #' . ($index + 1) }}
                                    </h3>
                                    @if ($photo->is_active)
                                        <flux:badge color="green" size="sm" inset="top bottom">{{ __('Active') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('Hidden') }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                    {{ $photo->caption ?: $photo->image_url }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 shrink-0">
                                {{-- Move Up --}}
                                <button
                                    type="button"
                                    wire:click="moveUp({{ $photo->id }})"
                                    @disabled($loop->first)
                                    class="p-1.5 rounded-md text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                    title="Move Up"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </button>

                                {{-- Move Down --}}
                                <button
                                    type="button"
                                    wire:click="moveDown({{ $photo->id }})"
                                    @disabled($loop->last)
                                    class="p-1.5 rounded-md text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                    title="Move Down"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>

                                {{-- Toggle Active --}}
                                <button
                                    type="button"
                                    wire:click="toggleActive({{ $photo->id }})"
                                    class="p-1.5 rounded-md {{ $photo->is_active ? 'text-amber-500 hover:text-amber-600' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200' }} hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                    title="{{ $photo->is_active ? 'Hide from Slideshow' : 'Show in Slideshow' }}"
                                >
                                    @if ($photo->is_active)
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @else
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                    @endif
                                </button>

                                {{-- Delete --}}
                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $photo->id }})"
                                    class="p-1.5 rounded-md text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer"
                                    title="Delete Slide"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Gallery Photo Browser Modal --}}
    @if ($showLibraryModal)
        <div wire:key="slideshow-library-modal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="$set('showLibraryModal', false)"></div>

                {{-- Dialog Box --}}
                <div class="relative w-full max-w-4xl max-h-[85vh] flex flex-col rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-2xl text-zinc-900 dark:text-zinc-100 overflow-hidden z-10 text-left">
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Choose from Gallery Photos') }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ __('Click any photo to select it for the slide') }}</p>
                        </div>
                        <button
                            type="button"
                            wire:click="$set('showLibraryModal', false)"
                            class="rounded-lg p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            @foreach ($libraryPhotos as $libPhoto)
                                <div
                                    wire:key="slide-lib-photo-{{ $libPhoto['filename'] }}"
                                    wire:click="selectLibraryPhoto('{{ $libPhoto['url'] }}')"
                                    class="group relative aspect-square rounded-xl overflow-hidden border-2 border-transparent hover:border-amber-500 bg-zinc-100 dark:bg-zinc-800 cursor-pointer shadow-xs transition transform hover:scale-105"
                                    title="{{ $libPhoto['filename'] }}"
                                >
                                    <img
                                        src="{{ $libPhoto['url'] }}"
                                        alt="{{ $libPhoto['filename'] }}"
                                        class="h-full w-full object-cover group-hover:opacity-90"
                                        loading="lazy"
                                    />
                                    <div class="absolute inset-x-0 bottom-0 bg-black/70 px-1.5 py-0.5 text-[10px] text-white truncate text-center opacity-0 group-hover:opacity-100 transition">
                                        {{ $libPhoto['filename'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end shrink-0">
                        <button
                            type="button"
                            wire:click="$set('showLibraryModal', false)"
                            class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                        >
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($deletingId)
        <div wire:key="slideshow-delete-modal-{{ $deletingId }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="cancelDelete"></div>

                {{-- Dialog Box --}}
                <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-2xl text-zinc-900 dark:text-zinc-100 z-10 text-left">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/70 text-red-600 dark:text-red-400 shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Remove Photo from Slideshow') }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This photo will no longer appear in the carousel.') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="cancelDelete"
                            class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="deleteImage"
                            class="px-5 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white shadow-md transition cursor-pointer"
                        >
                            {{ __('Remove Slide') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
