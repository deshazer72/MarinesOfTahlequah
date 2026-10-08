<?php

use App\Models\About;
use App\Services\PhotoStorageService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Title('Edit About Content - Marines of Tahlequah')]
#[Layout('layouts.app')]
class extends Component {
    use WithFileUploads;

    public About $about;
    public string $title = '';
    public string $content = '';
    public string $image_url = '';
    public $photo_file = null;
    public int $order = 0;
    public bool $is_published = true;
    public bool $showLibraryModal = false;

    public function mount(About $about): void
    {
        if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin())) {
            abort(403);
        }

        $this->about = $about;
        $this->title = $about->title;
        $this->content = $about->content;
        $this->image_url = $about->image_url ?? '';
        $this->order = $about->order;
        $this->is_published = $about->is_published;
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
        $this->photo_file = null;
        $this->showLibraryModal = false;

        Flux::toast(
            text: __('Photo selected from gallery.'),
            variant: 'success',
        );
    }

    public function update(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|string|max:500',
            'photo_file' => 'nullable|image|max:10240',
            'order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        try {
            if ($this->photo_file) {
                $validated['image_url'] = PhotoStorageService::store($this->photo_file, Auth::id());
            }

            unset($validated['photo_file']);

            $this->about->update($validated);

            Flux::toast(
                text: __('About content updated successfully.'),
                heading: __('Updated'),
                variant: 'success',
            );

            $this->redirect(route('about.index'), navigate: true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('About update error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            Flux::toast(
                text: __('Error updating content: ') . $e->getMessage(),
                heading: __('Update Failed'),
                variant: 'danger',
            );
        }
    }
}; ?>

<div class="max-w-3xl mx-auto py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Edit About Content') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Modify organizational information and imagery') }}</flux:subheading>
        </div>
        <flux:button variant="ghost" :href="route('about.index')" icon="arrow-left" wire:navigate>
            {{ __('Back to About') }}
        </flux:button>
    </div>

    <form wire:submit="update" class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 sm:p-8 space-y-6 shadow-md dark:shadow-none">
        <flux:input wire:model="title" :label="__('Title')" required />

        <flux:textarea wire:model="content" :label="__('Content')" rows="8" required />

        {{-- Photo Selection --}}
        <div class="space-y-4 pt-2">
            <flux:heading size="sm">{{ __('Section Photo') }}</flux:heading>

            {{-- Native file upload --}}
            <div>
                <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-300 mb-1.5">
                    {{ __('Upload New Photo (Optional)') }}
                </label>
                <input
                    type="file"
                    wire:model="photo_file"
                    accept="image/*"
                    class="block w-full text-xs text-zinc-600 dark:text-zinc-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-700 file:text-white hover:file:bg-red-600 cursor-pointer border border-zinc-300 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/80 p-1.5 focus:outline-hidden transition"
                />
                <div wire:loading wire:target="photo_file" class="mt-2 flex items-center gap-2 text-xs font-semibold text-amber-500 bg-amber-500/10 border border-amber-500/20 rounded-lg p-2.5">
                    <svg class="size-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Uploading replacement photo from device... Please wait for preview before saving.</span>
                </div>
                @error('photo_file')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror

                @if ($photo_file)
                    <div class="mt-3 relative min-h-48 max-h-72 w-full max-w-md rounded-xl overflow-hidden border border-zinc-300 dark:border-zinc-700 bg-zinc-950 flex items-center justify-center p-2">
                        <img
                            src="{{ $photo_file->temporaryUrl() }}"
                            alt=""
                            aria-hidden="true"
                            class="absolute inset-0 w-full h-full object-cover filter blur-xl opacity-30 scale-110 pointer-events-none"
                        />
                        <img src="{{ $photo_file->temporaryUrl() }}" class="relative z-10 max-h-64 w-auto max-w-full object-contain rounded-lg shadow-md" alt="Upload preview">
                        <button
                            type="button"
                            wire:click="$set('photo_file', null)"
                            class="absolute top-2 right-2 z-20 rounded-md bg-black/70 p-1.5 text-white hover:bg-black/90 transition text-xs flex items-center gap-1 cursor-pointer"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cancel Upload
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
                    <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Browse Gallery Photos (44 Photos)</span>
                </button>
            </div>

            {{-- Current image / direct URL input --}}
            <flux:input wire:model="image_url" :label="__('Or Enter Image URL / Local Path')" type="text" placeholder="/MarinesPictures/IMG_8884.jpg or https://..." />

            @if($image_url && !$photo_file)
                <div class="mt-2">
                    <span class="text-xs text-zinc-500 block mb-1">Active Section Photo:</span>
                    <div class="relative min-h-48 max-h-72 w-full max-w-md rounded-xl overflow-hidden border border-zinc-300 dark:border-zinc-700 bg-zinc-950 flex items-center justify-center p-2">
                        <img
                            src="{{ $image_url }}"
                            alt=""
                            aria-hidden="true"
                            class="absolute inset-0 w-full h-full object-cover filter blur-xl opacity-30 scale-110 pointer-events-none"
                        />
                        <img src="{{ $image_url }}" alt="Preview" class="relative z-10 max-h-64 w-auto max-w-full object-contain rounded-lg shadow-md">
                        <div class="absolute bottom-0 inset-x-0 z-20 bg-black/75 px-2.5 py-1 text-[11px] text-zinc-300 truncate">
                            {{ basename($image_url) }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <flux:input wire:model="order" :label="__('Display Order')" type="number" min="0" />
            <div class="flex items-center pt-6">
                <flux:checkbox wire:model="is_published" :label="__('Published')" />
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button variant="ghost" :href="route('about.index')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="photo_file, update"
                class="bg-red-700 hover:bg-red-600 text-white disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="photo_file">
                    {{ __('Save Changes') }}
                </span>
                <span wire:loading wire:target="photo_file" class="flex items-center gap-2">
                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    {{ __('Uploading photo...') }}
                </span>
            </flux:button>
        </div>
    </form>

    {{-- Gallery Photo Browser Modal --}}
    @if ($showLibraryModal)
        <div wire:key="about-edit-library-modal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="$set('showLibraryModal', false)"></div>

                {{-- Dialog Box --}}
                <div class="relative w-full max-w-4xl max-h-[85vh] flex flex-col rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-2xl text-zinc-900 dark:text-zinc-100 overflow-hidden z-10 text-left">
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Choose Section Photo from Gallery') }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ __('Click any photo to select it for this section') }}</p>
                        </div>
                        <button
                            type="button"
                            wire:click="$set('showLibraryModal', false)"
                            class="rounded-lg p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            @foreach ($this->getAvailablePhotos() as $libPhoto)
                                <div
                                    wire:key="about-edit-photo-{{ $libPhoto['filename'] }}"
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
</div>
