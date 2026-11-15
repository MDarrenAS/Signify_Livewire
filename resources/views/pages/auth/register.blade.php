
<x-layouts::auth :title="'Buat Profilmu'">
    <div class="register-container">
        <div class="register-header">
            <a href="{{ route('home') }}" class="register-logo">Signify</a>
            <h1>Buat profilmu</h1>
            <p>Yuk, mulai perjalanan belajar bahasa isyaratmu!</p>
        </div>

        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form method="POST" action="{{ route('register.store') }}" class="register-form">
            @csrf

            <div class="form-group">
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Nama lengkap"
                    autocomplete="name"
                    required
                    autofocus
                >
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email"
                    autocomplete="email"
                    required
                >
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <input
                    type="password"
                    name="password"
                    placeholder="Kata sandi"
                    autocomplete="new-password"
                    required
                >
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Konfirmasi kata sandi"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="register-button">
                Mulai
            </button>
        </form>

        <div class="register-login">
            <span>Sudah punya akun?</span>
            <a href="{{ route('login') }}" wire:navigate>Masuk</a>
        </div>
    </div>

    <style>
        .register-container {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
            padding: 35px 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .register-logo {
            display: inline-block;
            font-family: 'Caveat Brush', cursive;
            font-size: 42px;
            color: #F9B92F;
            margin-bottom: 20px;
        }

        .register-header h1 {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 700;
            color: #252525;
            margin-bottom: 12px;
        }

        .register-header p {
            font-size: 14px;
            color: #828282;
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .form-group input {
            width: 100%;
            height: 68px;
            padding: 0 25px;
            border: 1px solid #ADADAD;
            border-radius: 20px;
            background: #F3F3F3;
            color: #252525;
            font-size: 16px;
            font-family: inherit;
            outline: none;
            transition: .2s;
        }

        .form-group input::placeholder {
            color: #828282;
        }

        .form-group input:focus {
            border-color: #F9B92F;
            background: white;
            box-shadow: 0 0 0 3px #F9B92F25;
        }

        .error-message {
            display: block;
            margin: 7px 5px 0;
            color: #DC2626;
            font-size: 12px;
        }

        .register-button {
            width: 100%;
            height: 75px;
            border: none;
            border-radius: 20px;
            background: #F9B92F;
            color: white;
            font-family: inherit;
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 5px 12px #F9B92F35;
            transition: .2s;
        }

        .register-button:hover {
            background: #EBAA1F;
            transform: translateY(-2px);
        }

        .register-login {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 30px;
            font-size: 14px;
            color: #828282;
        }

        .register-login a {
            color: #D99A17;
            font-weight: 700;
        }

        @media (max-width: 600px) {
            .register-container {
                padding: 15px 0;
            }

            .register-form {
                gap: 16px;
            }

            .form-group input {
                height: 58px;
                font-size: 14px;
            }

            .register-button {
                height: 62px;
                font-size: 20px;
            }
        }
    </style>
</x-layouts::auth>