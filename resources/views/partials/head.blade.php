<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Marines of Tahlequah') : config('app.name', 'Marines of Tahlequah') }}
</title>

<link rel="icon" href="/favicon.ico?v=mot" sizes="any">
<link rel="icon" href="/favicon.svg?v=mot" type="image/svg+xml">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=mot">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=mot">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=mot">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    // 1. Immediately apply saved appearance before render to avoid flash
    (() => {
        try {
            const saved = localStorage.getItem('flux.appearance');
            if (saved === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
                if (!saved) localStorage.setItem('flux.appearance', 'dark');
            }
        } catch (e) {
            document.documentElement.classList.add('dark');
        }
    })();

    // 2. Synchronize all theme switch elements in the DOM
    function syncThemeUI(isDark) {
        document.querySelectorAll('[data-theme-switch]').forEach(btn => {
            btn.setAttribute('aria-checked', isDark ? 'true' : 'false');
            btn.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
            btn.setAttribute('aria-label', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');

            if (isDark) {
                btn.classList.add('bg-zinc-800', 'border-zinc-700');
                btn.classList.remove('bg-amber-100', 'border-amber-300');
            } else {
                btn.classList.remove('bg-zinc-800', 'border-zinc-700');
                btn.classList.add('bg-amber-100', 'border-amber-300');
            }

            const knob = btn.querySelector('[data-theme-knob]');
            if (knob) {
                if (isDark) {
                    knob.classList.add('translate-x-6', 'bg-zinc-950', 'text-amber-400');
                    knob.classList.remove('translate-x-0.5', 'bg-white', 'text-amber-500');
                } else {
                    knob.classList.remove('translate-x-6', 'bg-zinc-950', 'text-amber-400');
                    knob.classList.add('translate-x-0.5', 'bg-white', 'text-amber-500');
                }
            }

            const sun = btn.querySelector('[data-icon-sun]');
            const moon = btn.querySelector('[data-icon-moon]');
            if (sun) sun.style.display = isDark ? 'none' : 'inline-block';
            if (moon) moon.style.display = isDark ? 'inline-block' : 'none';
        });

        document.querySelectorAll('[data-theme-menu-light]').forEach(el => {
            el.style.display = isDark ? 'flex' : 'none';
        });
        document.querySelectorAll('[data-theme-menu-dark]').forEach(el => {
            el.style.display = isDark ? 'none' : 'flex';
        });
    }

    // 3. Bulletproof theme toggle function
    let lastThemeToggle = 0;
    window.toggleTheme = function() {
        const now = Date.now();
        if (now - lastThemeToggle < 150) return;
        lastThemeToggle = now;

        const isCurrentlyDark = document.documentElement.classList.contains('dark');
        const nextDark = !isCurrentlyDark;

        if (nextDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        try {
            const val = nextDark ? 'dark' : 'light';
            localStorage.setItem('flux.appearance', val);
            localStorage.setItem('appearance', val);
        } catch (e) {}

        if (window.Flux) {
            try {
                if (typeof window.Flux.applyAppearance === 'function') {
                    window.Flux.applyAppearance(nextDark ? 'dark' : 'light');
                }
                window.Flux.dark = nextDark;
                window.Flux.appearance = nextDark ? 'dark' : 'light';
            } catch (e) {}
        }

        syncThemeUI(nextDark);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: nextDark } }));
        return nextDark;
    };

    // 4. Initial DOM sync & re-sync on events
    document.addEventListener('DOMContentLoaded', () => {
        syncThemeUI(document.documentElement.classList.contains('dark'));
    });
    window.addEventListener('load', () => {
        syncThemeUI(document.documentElement.classList.contains('dark'));
    });
    window.addEventListener('pageshow', () => {
        syncThemeUI(document.documentElement.classList.contains('dark'));
    });

    document.addEventListener('livewire:navigated', () => {
        let isDark = true;
        try {
            if (localStorage.getItem('flux.appearance') === 'light') isDark = false;
        } catch (e) {}

        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        if (window.Flux) {
            try {
                if (typeof window.Flux.applyAppearance === 'function') {
                    window.Flux.applyAppearance(isDark ? 'dark' : 'light');
                }
                window.Flux.dark = isDark;
                window.Flux.appearance = isDark ? 'dark' : 'light';
            } catch (e) {}
        }

        syncThemeUI(isDark);
    });

    document.addEventListener('alpine:init', () => {
        const isDark = document.documentElement.classList.contains('dark');
        if (window.Flux) {
            try {
                if (typeof window.Flux.applyAppearance === 'function') {
                    window.Flux.applyAppearance(isDark ? 'dark' : 'light');
                }
                window.Flux.dark = isDark;
                window.Flux.appearance = isDark ? 'dark' : 'light';
            } catch (e) {}
        }
    });

    // 5. Observe DOM class mutations on documentElement to keep all switches in sync
    try {
        const themeObserver = new MutationObserver(() => {
            syncThemeUI(document.documentElement.classList.contains('dark'));
        });
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    } catch (e) {}
</script>
@fluxAppearance
