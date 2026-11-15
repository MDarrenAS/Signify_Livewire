<x-layouts::auth :title="'Masuk'">
    <div class="login-container">
        <h1 class="login-title">Masuk</h1>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="login-form">
            @csrf

            <div class="login-input-group">
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email atau Username"
                    required
                    autofocus
                    autocomplete="email"
                >
                @error('email')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-input-group">
                <input
                    type="password"
                    name="password"
                    placeholder="Kata Sandi"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-forgot">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate>
                        Lupa kata sandi?
                    </a>
                @endif
            </div>

            <button type="submit" class="login-button">
                Masuk
            </button>

            <div class="login-register-text">
                Belum Punya Akun?
            </div>

            <a href="{{ route('register') }}" wire:navigate class="login-register-button">
                Daftar
            </a>
        </form>
    </div>

    <style>
        .login-container {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .login-title {
            text-align: center;
            font-size: 48px;
            font-weight: 600;
            line-height: 1.2;
            color: #000;
            margin: 0 0 105px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .login-input-group {
            margin-bottom: 26px;
        }

        .login-input-group input {
            box-sizing: border-box;
            width: 100%;
            height: 68px;
            padding: 16px 20px;
            background: #F3F3F3;
            border: 1px solid #ADADAD;
            border-radius: 20px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            color: #333;
            outline: none;
        }

        .login-input-group input::placeholder {
            color: #828282;
            opacity: 1;
        }

        .login-input-group input:focus {
            border-color: #F9B92F;
        }

        .login-error {
            display: block;
            margin: 6px 0 0 10px;
            color: #dc2626;
            font-size: 13px;
        }

        .login-forgot {
            margin: 2px 0 38px 20px;
        }

        .login-forgot a {
            color: #E0A72A;
            font-size: 20px;
            text-decoration: none;
        }

        .login-button {
            width: 100%;
            height: 82px;
            background: #F9B92F;
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 4px rgba(0, 0, 0, 0.25);
            color: #FFFCEF;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 32px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-register-text {
            margin: 48px 0 28px 20px;
            color: #828282;
            font-size: 20px;
        }

        .login-register-button {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 85px;
            box-sizing: border-box;
            background: #FAFAFA;
            border: 1px solid #ADADAD;
            border-radius: 20px;
            box-shadow: 0 6px 4px rgba(0, 0, 0, 0.25);
            color: #F9B92F;
            font-size: 36px;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 700px) {
            .login-title {
                font-size: 38px;
                margin-bottom: 75px;
            }

            .login-input-group input {
                font-size: 16px;
            }

            .login-forgot a,
            .login-register-text {
                font-size: 16px;
            }

            .login-button {
                height: 68px;
                font-size: 26px;
            }

            .login-register-button {
                height: 70px;
                font-size: 28px;
            }
        }
    </style>
</x-layouts::auth>
