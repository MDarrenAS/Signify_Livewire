
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
            color: #252525;
        }

        .app-layout {
            min-height: 100vh;
            display: flex;
            padding: 12px;
            gap: 28px;
        }

        .app-sidebar {
            width: 340px;
            min-width: 280px;
            min-height: calc(100vh - 24px);
            background: #FFF;
            border-radius: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,.08);
            padding: 35px 24px;
            display: flex;
            flex-direction: column;
        }

        .app-logo {
            font-family: 'Caveat Brush', cursive;
            font-size: 64px;
            color: #F9B92F;
            text-align: center;
            text-decoration: none;
            margin-bottom: 55px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 18px;
            border-radius: 14px;
            color: #828282;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: .2s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #FEF5E0;
            color: #D99B19;
        }

        .sidebar-icon {
            width: 23px;
            height: 23px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 35px;
        }

        .sidebar-user {
            padding: 16px;
            background: #FEF5E0;
            border-radius: 15px;
            margin-bottom: 14px;
        }

        .sidebar-user strong {
            display: block;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .sidebar-user span {
            font-size: 12px;
            color: #828282;
            overflow-wrap: anywhere;
        }

        .logout-button {
            width: 100%;
            padding: 13px;
            background: #FFF;
            border: 1px solid #ADADAD;
            border-radius: 12px;
            color: #828282;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #FEF5E0;
            color: #D99B19;
        }

        .app-main {
            flex: 1;
            min-width: 0;
            padding: 28px 25px;
        }

        .mobile-menu-button {
            display: none;
        }

        @media (max-width: 900px) {
            .app-layout {
                padding: 10px;
                gap: 12px;
            }

            .app-sidebar {
                width: 250px;
                min-width: 220px;
                padding: 25px 15px;
            }

            .app-logo {
                font-size: 52px;
            }

            .app-main {
                padding: 20px 12px;
            }
        }

        @media (max-width: 650px) {
            .app-layout {
                display: block;
                padding: 0;
            }

            .app-sidebar {
                width: 100%;
                min-height: auto;
                border-radius: 0;
                padding: 12px 18px;
                box-shadow: 0 2px 8px rgba(0,0,0,.06);
            }

            .app-logo {
                font-size: 42px;
                margin: 0;
                text-align: left;
            }

            .sidebar-menu {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 5px;
            }

            .sidebar-menu a {
                padding: 9px 11px;
                font-size: 12px;
                gap: 6px;
            }

            .sidebar-icon {
                font-size: 16px;
                width: 18px;
            }

            .sidebar-bottom {
                padding-top: 12px;
            }

            .sidebar-user {
                margin-bottom: 8px;
                padding: 10px;
            }

            .logout-button {
                padding: 9px;
            }

            .app-main {
                padding: 22px 16px;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <aside class="app-sidebar">
            <a href="{{ route('dashboard') }}" class="app-logo" wire:navigate>
                Signify
            </a>

            <nav class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="active" wire:navigate>
                    <span class="sidebar-icon">⌂</span>
                    Dashboard
                </a>

            <a href="{{ route('modul.index') }}" wire:navigate>
                Modul Belajar
            </a>

                <a href="{{ route('latihan.index') }}" wire:navigate>
                    <span class="sidebar-icon">✎</span>
                    Latihan dan Kuis
                </a>

                <a href="{{ route('kamus.index') }}" wire:navigate>
                    <span class="sidebar-icon">☷</span>
                    Kamus BISINDO
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-user">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->email }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">
                        ↪ Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="app-main">
            {{ $slot }}
        </main>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
