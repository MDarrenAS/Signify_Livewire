<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profil Saya')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(
            variant: 'success',
            text: __('Profile updated.')
        );
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail
            && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail
                && Auth::user()->hasVerifiedEmail());
    }
};
?>

<section class="w-full space-y-8">

    <div class="space-y-2">
        <div class="inline-flex items-center gap-2 rounded-full bg-[#FEF5E0] px-4 py-2 text-xs font-semibold text-[#A87919]">
            <span class="h-2 w-2 rounded-full bg-[#F9B92F]"></span>
            Pengaturan Akun
        </div>

        <h1 class="text-3xl font-bold text-[#252525]">
            Profil Saya
        </h1>

        <p class="text-sm leading-6 text-[#828282]">
            Kelola informasi pribadi dan detail akun Signify milikmu.
        </p>
    </div>

    <div class="rounded-2xl border border-[#EAEAEA] bg-white p-6 sm:p-8">
        <div class="mb-8 flex items-center gap-4 border-b border-[#EEEEEE] pb-6">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#FEF5E0] text-2xl font-bold text-[#D99A19]">
                {{ mb_strtoupper(mb_substr($name ?: 'U', 0, 1)) }}
            </div>

            <div class="min-w-0">
                <h2 class="truncate text-lg font-bold text-[#252525]">
                    {{ $name }}
                </h2>

                <p class="truncate text-sm text-[#828282]">
                    {{ $email }}
                </p>

                <span class="mt-2 inline-flex rounded-full bg-[#FEF5E0] px-3 py-1 text-xs font-medium text-[#A87919]">
                    Pengguna Signify
                </span>
            </div>
        </div>

        <div class="mb-6 space-y-1">
            <h2 class="text-lg font-bold text-[#252525]">
                Informasi Pribadi
            </h2>

            <p class="text-sm text-[#828282]">
                Perbarui nama dan alamat email akunmu.
            </p>
        </div>

        <form wire:submit="updateProfileInformation" class="space-y-6">
            <div class="space-y-2">
                <flux:input
                    wire:model="name"
                    :label="__('Name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                />
            </div>

            <div class="space-y-2">
                <flux:input
                    wire:model="email"
                    :label="__('Email')"
                    type="email"
                    required
                    autocomplete="email"
                />

                @if ($this->hasUnverifiedEmail)
                    <div class="rounded-xl bg-[#FEF8EA] p-4">
                        <p class="text-sm text-[#946C19]">
                            Alamat email kamu belum diverifikasi.
                        </p>

                        <button
                            type="button"
                            wire:click.prevent="resendVerificationNotification"
                            class="mt-2 text-sm font-semibold text-[#A87919] underline underline-offset-2 hover:text-[#805A10]"
                        >
                            Kirim ulang email verifikasi
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-3 text-sm font-medium text-green-700">
                                Link verifikasi baru berhasil dikirim ke email kamu.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-[#EEEEEE] pt-6">
                <p class="text-xs text-[#999999]">
                    Pastikan informasi akunmu sudah benar.
                </p>

                <flux:button
                    variant="primary"
                    type="submit"
                    data-test="update-profile-button"
                    class="!rounded-xl !bg-[#F9B92F] hover:!bg-[#E5A51F]"
                >
                    Simpan Perubahan
                </flux:button>
            </div>
        </form>
    </div>

    @if ($this->showDeleteUser)
        <div class="rounded-2xl border border-[#EAEAEA] bg-white p-6 sm:p-8">
            <div class="mb-5 space-y-1">
                <h2 class="text-lg font-bold text-[#252525]">
                    Hapus Akun
                </h2>

                <p class="text-sm leading-6 text-[#828282]">
                    Kelola penghapusan akun Signify jika sudah tidak ingin menggunakannya.
                </p>
            </div>

            <livewire:pages::settings.delete-user-form />
        </div>
    @endif
</section>