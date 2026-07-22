@props([
    'title',
    'subtitle' => null,
])

<div class="app-shell" data-app-shell>
    <x-sidebar />

    <div class="app-shell__body">
        <header class="app-topbar">
            <div class="app-topbar__left">
                <button
                    type="button"
                    class="app-topbar__toggle"
                    data-sidebar-toggle
                    aria-label="Toggle sidebar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <h1 class="app-topbar__title">{{ $title }}</h1>
            </div>

            @isset($topbarActions)
                <div class="flex items-center gap-2">{{ $topbarActions }}</div>
            @endisset
        </header>

        <main class="app-content">
            <section class="app-page-head">
                <div>
                    <p class="app-page-head__eyebrow">{{ $title }}</p>

                    @if ($subtitle)
                        <p class="app-page-head__subtitle">{{ $subtitle }}</p>
                    @endif
                </div>

                @isset($actions)
                    <div class="app-page-head__actions">{{ $actions }}</div>
                @endisset
            </section>

            {{ $slot }}
        </main>
    </div>

    <button
        type="button"
        class="app-overlay"
        data-sidebar-overlay
        aria-label="Close sidebar"
    ></button>
</div>
