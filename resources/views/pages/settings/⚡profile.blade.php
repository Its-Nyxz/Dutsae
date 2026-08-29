<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Profil Pengguna - Toko Duta Sae')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
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
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    <x-pages::settings.layout :heading="__('Profil Pengguna')" :subheading="__('Kelola informasi akun, peran otorisasi, dan petunjuk sistem')">
        <!-- Account Status & Role Card -->
        @php $currentUser = Auth::user(); @endphp
        <div class="my-4 p-4 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">Peran Otorisasi</span>
                @if ($currentUser?->isAdmin())
                    <span class="bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/40 text-xs px-2.5 py-0.5 rounded-full font-black">
                        👑 Admin Utama
                    </span>
                @else
                    <span class="bg-sky-500/20 text-sky-700 dark:text-sky-300 border border-sky-500/40 text-xs px-2.5 py-0.5 rounded-full font-bold">
                        👤 Kasir Toko
                    </span>
                @endif
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-300 flex justify-between">
                <span>Toko / Cabang:</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ $currentUser?->store?->name ?? 'Toko Duta Sae' }}</span>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Nama Lengkap')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Alamat Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Simpan Perubahan') }}
                    </flux:button>
                </div>
            </div>
        </form>

        <!-- Quick System Guide Card in Profile -->
        <div class="my-8 p-5 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30 rounded-2xl space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📖</span>
                    <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">Panduan Penggunaan Sistem</h4>
                </div>
                <a href="{{ route('guide.index') }}" wire:navigate class="text-xs font-black text-amber-600 dark:text-amber-400 hover:underline">
                    Lihat Selengkapnya &rarr;
                </a>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Pelajari alur kasir POS cepat, hierarki multi-satuan barang, pelacakan lokasi rak, pencatatan stok supplier, dan pembukuan piutang pelanggan.
            </p>
            <div class="pt-1">
                <a href="{{ route('guide.index') }}" wire:navigate class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-4 py-2 rounded-xl text-xs shadow-sm transition">
                    <span>Buka Dokumentasi & Panduan Lengkap</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
