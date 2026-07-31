@php
    $userName = auth()->user()?->name ?? 'Admin OD';
    $userEmail = auth()->user()?->email ?? 'Employee Data Completion';
    $userInitial = strtoupper(substr(trim($userName), 0, 1));

    $navigation = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Upload Excel',
            'route' => 'employee.import.create',
            'active' => request()->routeIs('employee.import.*'),
            'icon' => 'upload',
        ],
        [
            'label' => 'Set PIC',
            'route' => 'set-pic.index',
            'active' => request()->routeIs('set-pic.*'),
            'icon' => 'PIC',
        ],
        [
            'label' => 'Fill Employee Profile',
            'route' => 'hr-form.index',
            'active' => request()->routeIs('hr-form.*'),
            'icon' => 'form',
        ],
    ];
@endphp

<aside class="app-sidebar" aria-label="Main navigation">
    <div class="app-sidebar__brand">
        <div class="app-sidebar__logo">
            <img src="{{ asset('img/kanmo-logo.jpeg') }}" alt="Kanmo Group"
                onerror="this.style.display='none'; this.parentElement.textContent='K';">
        </div>

        <div class="app-sidebar__brand-copy min-w-0">
            <p class="truncate text-[15px] font-extrabold text-[#3f281b]">Kanmo Group</p>
            <p class="truncate text-[11px] font-medium text-[#97684b]">Employee Data Completion</p>
        </div>

        <button type="button" class="app-sidebar__close" data-sidebar-close aria-label="Close sidebar">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="app-sidebar__nav">
        <p class="app-sidebar__section-label">Workspace</p>

        @foreach ($navigation as $item)
            <a href="{{ route($item['route']) }}" class="app-sidebar__nav-link"
                @if ($item['active']) aria-current="page" @endif data-sidebar-nav-link
                title="{{ $item['label'] }}">
                @if ($item['icon'] === 'dashboard')
                    <svg class="app-sidebar__nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 3.75h6.5v6.5h-6.5v-6.5zm10 0h6.5v6.5h-6.5v-6.5zm-10 10h6.5v6.5h-6.5v-6.5zm10 0h6.5v6.5h-6.5v-6.5z" />
                    </svg>
                @elseif ($item['icon'] === 'PIC')
                    <svg class="app-sidebar__nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 1a3 3 0 10-4-2.83" />
                    </svg>
                @elseif ($item['icon'] === 'form')
                    <svg class="app-sidebar__nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5h6m-6 4h6m-6 4h3m-6 7h12a2 2 0 002-2V6a2 2 0 00-2-2h-2.5a2.5 2.5 0 00-5 0H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                @else
                    <svg class="app-sidebar__nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4" />
                    </svg>
                @endif

                <span class="app-sidebar__nav-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="app-sidebar__footer">
        <div class="app-sidebar__user">
            <div class="app-sidebar__avatar">{{ $userInitial }}</div>
            <div class="min-w-0 flex-1">
                <p class="truncate font-bold text-[#4b2d1d]">{{ $userName }}</p>
                <p class="truncate text-xs text-[#9a755e]">{{ $userEmail }}</p>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="mt-3">
            @csrf

            <button type="submit" class="kanmo-btn-secondary w-full">
                Logout
            </button>
        </form>
    </div>
</aside>
