<x-layouts.public title="Home - Marines of Tahlequah">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold text-amber-400 mb-4">
                    <span>Semper Fidelis &bull; Serving Veterans & Community Since 2015</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-zinc-900 dark:text-white mb-4">
                    Marines of <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-amber-500">Tahlequah</span>
                </h1>
                <p class="max-w-2xl mx-auto text-base sm:text-lg text-zinc-600 dark:text-zinc-400">
                    Dedicated advocates for service members, veterans, and their families across the Tahlequah and Cherokee County community.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <flux:button variant="primary" :href="route('donate')" icon="heart" wire:navigate class="bg-red-700 hover:bg-red-600 text-white font-semibold shadow-xs">
                        Support Our Veterans
                    </flux:button>
                    <flux:button variant="ghost" :href="route('events.index')" icon="calendar" wire:navigate class="border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        View Upcoming Events
                    </flux:button>
                </div>
            </div>

            {{-- Interactive Photo Slideshow --}}
            @php
                $slideshowUrls = [];
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('slideshow_images')) {
                        $dbSlideshow = \App\Models\SlideshowImage::where('is_active', true)
                            ->orderBy('sort_order', 'asc')
                            ->orderBy('id', 'asc')
                            ->get();

                        if ($dbSlideshow->isNotEmpty()) {
                            $slideshowUrls = $dbSlideshow->map(function ($item) {
                                $url = $item->image_url;
                                return \Illuminate\Support\Str::startsWith($url, ['http://', 'https://']) ? $url : asset(ltrim($url, '/'));
                            })->values()->toArray();
                        }
                    }
                } catch (\Throwable $e) {}

                if (empty($slideshowUrls)) {
                    $slideshowUrls = [
                        asset('MarinesPictures/IMG_8884.jpg'),
                        asset('MarinesPictures/IMG_8885.jpg'),
                        asset('MarinesPictures/pic1.jpg'),
                    ];
                }
            @endphp

            @auth
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <div class="flex justify-end max-w-4xl mx-auto mb-2">
                        <flux:button variant="ghost" size="sm" :href="route('admin.slideshow')" icon="photo" wire:navigate class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-500 bg-amber-500/10 border border-amber-500/30">
                            {{ __('Manage Slideshow Photos') }}
                        </flux:button>
                    </div>
                @endif
            @endauth

            <div
                id="slideshow-container"
                tabindex="0"
                class="relative mx-auto max-w-4xl overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-2xl focus:outline-hidden focus:ring-2 focus:ring-amber-500/50"
            >
                <div class="relative h-[320px] sm:h-[460px] md:h-[520px] w-full flex items-center justify-center bg-black/60 overflow-hidden">
                    {{-- Active Slideshow Image (SSR initial render so it never appears blank) --}}
                    <img
                        id="slideshow-image"
                        src="{{ $slideshowUrls[0] ?? '' }}"
                        alt="Marines of Tahlequah"
                        class="absolute inset-0 h-full w-full object-contain p-2 transition-opacity duration-300 ease-in-out"
                    />

                    {{-- Navigation Controls --}}
                    <button
                        type="button"
                        onclick="window.slideshowPrev && window.slideshowPrev()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 z-10 flex size-10 items-center justify-center rounded-full bg-zinc-900/80 text-white border border-zinc-700/60 shadow-lg backdrop-blur-sm transition hover:bg-red-700 hover:scale-105 cursor-pointer"
                        aria-label="Previous Photo"
                    >
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button
                        type="button"
                        onclick="window.slideshowNext && window.slideshowNext()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 flex size-10 items-center justify-center rounded-full bg-zinc-900/80 text-white border border-zinc-700/60 shadow-lg backdrop-blur-sm transition hover:bg-red-700 hover:scale-105 cursor-pointer"
                        aria-label="Next Photo"
                    >
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>

                    {{-- Image Counter Badge --}}
                    <div class="absolute bottom-4 right-4 z-10 rounded-full bg-zinc-950/80 px-3 py-1 text-xs font-medium text-zinc-300 border border-zinc-800 backdrop-blur-sm">
                        <span id="slideshow-counter">1</span> / <span>{{ count($slideshowUrls) }}</span>
                    </div>
                </div>
            </div>

            <script>
                (() => {
                    const images = @json($slideshowUrls);
                    let current = 0;
                    let timer = null;

                    function init() {
                        const imgEl = document.getElementById('slideshow-image');
                        const counterEl = document.getElementById('slideshow-counter');
                        const containerEl = document.getElementById('slideshow-container');
                        if (!imgEl || !containerEl) return;

                        if (timer) clearInterval(timer);

                        function show(index) {
                            current = (index + images.length) % images.length;
                            imgEl.style.opacity = '0.3';
                            const nextSrc = images[current];
                            const tempImg = new Image();
                            tempImg.onload = () => {
                                imgEl.src = nextSrc;
                                imgEl.style.opacity = '1';
                            };
                            tempImg.onerror = () => {
                                imgEl.src = nextSrc;
                                imgEl.style.opacity = '1';
                            };
                            tempImg.src = nextSrc;

                            if (counterEl) counterEl.textContent = current + 1;

                            // Preload upcoming photo
                            const preload = new Image();
                            preload.src = images[(current + 1) % images.length];
                        }

                        window.slideshowNext = function() {
                            show(current + 1);
                            resetTimer();
                        };

                        window.slideshowPrev = function() {
                            show(current - 1);
                            resetTimer();
                        };

                        function startTimer() {
                            stopTimer();
                            timer = setInterval(() => { show(current + 1); }, 4500);
                        }

                        function stopTimer() {
                            if (timer) { clearInterval(timer); timer = null; }
                        }

                        function resetTimer() {
                            startTimer();
                        }

                        containerEl.onmouseenter = stopTimer;
                        containerEl.onmouseleave = startTimer;

                        // Touch gestures
                        let touchStartX = 0;
                        containerEl.ontouchstart = (e) => {
                            touchStartX = e.changedTouches[0].screenX;
                        };
                        containerEl.ontouchend = (e) => {
                            const touchEndX = e.changedTouches[0].screenX;
                            if (touchStartX - touchEndX > 50) window.slideshowNext();
                            if (touchEndX - touchStartX > 50) window.slideshowPrev();
                        };

                        startTimer();
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', init);
                    } else {
                        init();
                    }
                    document.addEventListener('livewire:navigated', init);
                })();
            </script>
        </div>
    </section>

    {{-- Mission Section --}}
    <section class="py-16 border-t border-zinc-200 dark:border-zinc-900 bg-white/60 dark:bg-zinc-950/50">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Our Mission</h2>
                <div class="mx-auto mt-2 h-1 w-20 rounded bg-red-600"></div>
                <p class="mt-6 text-lg leading-relaxed text-zinc-700 dark:text-zinc-300 max-w-3xl mx-auto">
                    Since 2015, the Marines of Tahlequah (MOT) have been dedicated advocates for service members, veterans, and their families. While initially founded as a group of Marines celebrating our shared heritage, we have grown into a vibrant community organization committed to improving the lives of those who have served our country. We consider it our highest honor to serve our community and fellow service members.
                </p>
            </div>

            {{-- Support Initiatives Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 shadow-sm hover:border-red-600/50 transition">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-lg bg-red-700/20 text-red-500">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Disaster & Emergency Relief</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Assisting flood victims, organizing food, water, and clothing drives, and mobilizing rapid aid during community emergencies.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 shadow-sm hover:border-amber-600/50 transition">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-lg bg-amber-600/20 text-amber-500">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Shelter & Housing Support</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Supporting local women's shelters, men's homes, and halfway houses, as well as providing direct housing assistance to prevent evictions and cover utility costs for veterans.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 shadow-sm hover:border-red-600/50 transition">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-lg bg-red-700/20 text-red-500">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Charitable Partnerships</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Collaborating closely with local charities serving the unhoused, and partnering with the Cherokee Nation on their Elder Care Programs and the Angel Tree initiative.
                    </p>
                </div>
            </div>

            {{-- Partnership Recognition --}}
            <div class="mt-12 rounded-xl border border-amber-500/30 bg-amber-500/10 p-6 text-center">
                <p class="text-sm italic text-amber-900 dark:text-amber-200">
                    "We are deeply grateful for the unwavering support of our community, including the <span class="font-semibold text-amber-700 dark:text-amber-400">Turnpike Troubadours</span>, whose partnership has been instrumental in our fundraising efforts to better serve the Tahlequah area."
                </p>
            </div>
        </div>
    </section>
</x-layouts.public>
