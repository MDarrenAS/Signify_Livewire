
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #FAFAFA;
        }

        .auth-background {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 24px;
            background: #FAFAFA;
        }

        .auth-background::before {
            content: '';
            position: absolute;
            width: 120%;
            height: 55%;
            left: -10%;
            bottom: -25%;
            background: #FDE9BF;
            border-radius: 50% 50% 0 0;
            z-index: 0;
        }

        .auth-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 650px;
        }

        @media (max-width: 600px) {
            .auth-background {
                padding: 30px 20px;
            }
        }

    </style>
</head>

<body>
    <div class="auth-background">
        <div class="auth-content">
            {{ $slot }}
        </div>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>