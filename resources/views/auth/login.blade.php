<x-guest-layout>
    <x-slot name="title">AKHLAK360 | Login</x-slot>

    <x-slot name="brand">
        <div class="auth-brand-copy">
            <p class="text-lg font-semibold text-gray-900 leading-tight">AKHLAK360</p>
            <p class="auth-brand-subtitle text-sm text-gray-600 leading-snug">Sistem Penilaian 360° Core Values AKHLAK</p>
            <p class="auth-brand-company text-xs font-medium text-gray-500">PT Energi Nusantara</p>
        </div>
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-login-form" data-login-form>
        @csrf

        <!-- Email Address -->
        <div class="auth-field">
            <x-input-label for="email" value="Email Perusahaan" />
            <x-text-input
                id="email"
                class="block w-full"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="nama@perusahaan.com"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="auth-error text-sm text-red-600" />
        </div>

        <!-- Password -->
        <div class="auth-field">
            <x-input-label for="password" value="Kata Sandi" />

            <div class="auth-password-control relative">
                <x-text-input
                    id="password"
                    class="block w-full pr-12"
                    type="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    required
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-r-md"
                    aria-label="Tampilkan kata sandi"
                    data-password-toggle
                >
                    <svg data-eye-open class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg data-eye-closed class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.73 5.08A10.45 10.45 0 0 1 12 5c6 0 9.75 7 9.75 7a18.55 18.55 0 0 1-2.18 3.19M6.61 6.61C3.88 8.45 2.25 12 2.25 12s3.75 7 9.75 7a9.7 9.7 0 0 0 4.64-1.17" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="auth-error text-sm text-red-600" />
        </div>

        <!-- Remember Me -->
        <div class="auth-login-options">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="auth-login-actions flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button class="ms-3" data-login-button>
                <span data-login-button-text>Masuk</span>
            </x-primary-button>
        </div>
    </form>

    <div class="auth-sso-section">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-2 text-gray-500">atau</span>
            </div>
        </div>

        <a href="{{ route('sso.simulation') }}"
            class="auth-sso-button flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            Masuk dengan Company SSO
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('[data-password-toggle]');
            const eyeOpen = document.querySelector('[data-eye-open]');
            const eyeClosed = document.querySelector('[data-eye-closed]');
            const loginForm = document.querySelector('[data-login-form]');
            const loginButton = document.querySelector('[data-login-button]');
            const loginButtonText = document.querySelector('[data-login-button-text]');

            if (passwordInput && toggleButton) {
                toggleButton.addEventListener('click', function () {
                    const isHidden = passwordInput.type === 'password';

                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleButton.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    eyeOpen?.classList.toggle('hidden', isHidden);
                    eyeClosed?.classList.toggle('hidden', ! isHidden);
                    passwordInput.focus();
                });
            }

            if (loginForm && loginButton && loginButtonText) {
                loginForm.addEventListener('submit', function () {
                    loginButton.disabled = true;
                    loginButton.classList.add('opacity-75', 'cursor-not-allowed');
                    loginButtonText.textContent = 'Memproses...';
                });
            }
        });
    </script>
</x-guest-layout>
