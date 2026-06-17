<x-guest-layout>
    <x-slot name="title">AKHLAK360 | Company SSO</x-slot>

    <x-slot name="brand">
        <div class="auth-brand-copy">
            <p class="text-lg font-semibold text-gray-900 leading-tight">AKHLAK360</p>
            <p class="auth-brand-subtitle text-sm text-gray-600 leading-snug">Sistem Penilaian 360° Core Values AKHLAK</p>
            <p class="auth-brand-company text-xs font-medium text-gray-500">PT Energi Nusantara</p>
        </div>
    </x-slot>

    <div class="auth-sso-card text-center">
        <h1 class="text-xl font-semibold text-gray-900">Company SSO</h1>
        <p class="text-sm text-gray-600 leading-6">
            Fitur Company SSO pada aplikasi ini merupakan simulasi untuk kebutuhan academic MVP.
        </p>
        <p class="text-sm text-gray-600 leading-6">
            Implementasi pada lingkungan produksi memerlukan integrasi dengan identity provider perusahaan menggunakan protokol seperti OIDC atau SAML.
        </p>
        <p class="text-sm text-gray-600 leading-6">
            Untuk demonstrasi aplikasi, silakan masuk menggunakan email dan kata sandi akun demo yang telah disediakan.
        </p>

        <a href="{{ route('login') }}"
            class="auth-sso-back-button inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Kembali ke Login
        </a>
    </div>

</x-guest-layout>
