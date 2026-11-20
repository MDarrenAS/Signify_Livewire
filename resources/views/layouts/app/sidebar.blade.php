<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #FAFAFA;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1F1F1F;
        }

        .app-layout {
            min-height: 100vh;
            display: flex;
            padding: 14px 16px;
            gap: 18px;
            background: #FAFAFA;
            max-width: 1600px;
            margin: 0 auto;
        }

        .app-sidebar {
            width: 245px;
            min-width: 230px;
            min-height: calc(100vh - 28px);
            background: #FAFAFA;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #EDEDED;
            padding: 28px 18px;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 14px;
            height: fit-content;
            flex-shrink: 0;
        }

        .app-logo {
            font-family: 'Caveat Brush', cursive;
            font-size: 46px;
            color: #F9B92F;
            text-align: center;
            text-decoration: none;
            margin-bottom: 32px;
            line-height: 1.1;
            display: block;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 14px;
            border-radius: 12px;
            color: #8A8A8A;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.18s ease;
            white-space: nowrap;
        }

        .sidebar-menu a:hover {
            background: #FEF8EA;
            color: #1F1F1F;
        }

        .sidebar-menu a.active {
            background: #FEF8EA;
            color: #000000;
        }

        .sidebar-menu a img {
            width: 26px;
            height: 26px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 32px;
        }

        .sidebar-user {
            padding: 12px 14px;
            background: #FEF5E0;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .sidebar-user strong {
            display: block;
            font-size: 14px;
            color: #1F1F1F;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user span {
            font-size: 11px;
            color: #828282;
            overflow-wrap: anywhere;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-button {
            width: 100%;
            padding: 10px;
            background: #FFF;
            border: 1px solid #D4D4D8;
            border-radius: 10px;
            color: #828282;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .logout-button:hover {
            background: #FEF5E0;
            color: #C79426;
            border-color: #F9B92F;
        }

        .app-main {
            flex: 1;
            min-width: 0;
            background: #FAFAFA;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #EDEDED;
            padding: 28px 30px;
        }

        /* Mobile Header */
        .mobile-header {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: #FAFAFA;
            border-radius: 14px;
            padding: 10px 18px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            border: 1px solid #EDEDED;
        }

        .mobile-header .app-logo {
            font-size: 38px;
            margin: 0;
            text-align: left;
        }

        .mobile-toggle-btn {
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #1F1F1F;
            padding: 4px;
            border-radius: 8px;
        }

        @media (max-width: 1024px) {
            .app-sidebar {
                width: 220px;
                min-width: 200px;
                padding: 22px 14px;
            }

            .sidebar-menu a {
                font-size: 15px;
                gap: 10px;
                padding: 9px 11px;
            }

            .sidebar-menu a img {
                width: 22px;
                height: 22px;
            }

            .app-main {
                padding: 22px 20px;
            }
        }

        @media (max-width: 860px) {
            .mobile-header {
                display: flex;
            }

            .app-layout {
                flex-direction: column;
                padding: 10px;
                gap: 12px;
            }

            .app-sidebar {
                display: none;
                width: 100%;
                min-height: auto;
                position: static;
                padding: 18px;
            }

            .app-sidebar.show-mobile {
                display: flex;
            }

            .app-logo {
                display: none;
            }

            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
                gap: 6px;
            }

            .sidebar-menu a {
                font-size: 14px;
                padding: 8px 10px;
                gap: 8px;
            }

            .sidebar-menu a img {
                width: 20px;
                height: 20px;
            }

            .app-main {
                padding: 18px 14px;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <!-- Mobile Header Bar -->
        <header class="mobile-header">
            <a href="{{ route('dashboard') }}" class="app-logo" style="display:inline-block;">
                Signify
            </a>
            <button type="button" class="mobile-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                ☰
            </button>
        </header>

        <aside class="app-sidebar" id="appSidebar">
            <a href="{{ route('dashboard') }}" class="app-logo" wire:navigate>
                Signify
            </a>

            <nav class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" wire:navigate>
                    <img src="{{ asset('images/dashboard/icon-dashboard.svg') }}" alt="" />
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('modul.index') }}" class="{{ request()->routeIs('modul.*') ? 'active' : '' }}" wire:navigate>
                    <img src="{{ asset('images/dashboard/icon-book.svg') }}" alt="" />
                    <span>Modul Belajar</span>
                </a>

                <a href="{{ route('latihan.index') }}" class="{{ request()->routeIs('latihan.*') ? 'active' : '' }}" wire:navigate>
                    <img src="{{ asset('images/dashboard/icon-quiz.svg') }}" alt="" />
                    <span>Latihan dan Kuis</span>
                </a>

                <a href="{{ route('kamus.index') }}" class="{{ request()->routeIs('kamus.*') ? 'active' : '' }}" wire:navigate>
                    <img src="{{ asset('images/dashboard/icon-translate.svg') }}" alt="" />
                    <span>Kamus BISINDO</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" wire:navigate>
                    <img src="{{ asset('images/dashboard/icon-profile.svg') }}" alt="" />
                    <span>Profil</span>
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-user">
                    <strong>{{ auth()->user()->name ?? 'Alifah' }}</strong>
                    <span>{{ auth()->user()->email ?? 'alifah@example.com' }}</span>
                </div>

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">
                            ↪ Keluar
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        <main class="app-main">
            {{ $slot }}
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            if (sidebar) {
                sidebar.classList.toggle('show-mobile');
            }
        }
    </script>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
