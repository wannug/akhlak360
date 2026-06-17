<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .auth-page {
                align-items: flex-start;
                display: flex;
                justify-content: center;
                min-height: 100dvh;
                overflow-x: hidden;
                padding: 24px 16px;
            }

            .auth-container {
                max-width: 450px;
                width: 100%;
            }

            .auth-branding {
                align-items: center;
                display: flex;
                flex-direction: column;
                text-align: center;
            }

            .auth-logo svg {
                height: 70px;
                width: 70px;
            }

            .auth-brand-copy {
                display: flex;
                flex-direction: column;
                margin-top: 12px;
            }

            .auth-brand-copy p {
                margin: 0;
            }

            .auth-brand-subtitle {
                margin-top: 8px !important;
            }

            .auth-brand-company {
                margin-top: 4px !important;
            }

            .auth-card {
                margin-top: 24px;
                padding: 24px;
                width: 100%;
            }

            .auth-login-form {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .auth-field input {
                margin-top: 4px;
            }

            .auth-password-control {
                margin-top: 4px;
            }

            .auth-password-control input {
                margin-top: 0;
            }

            .auth-error {
                margin-top: 8px;
            }

            .auth-login-options,
            .auth-login-actions {
                margin-top: -2px;
            }

            .auth-sso-section {
                margin-top: 24px;
            }

            .auth-sso-button {
                margin-top: 20px;
            }

            .auth-sso-card h1 {
                margin: 0 0 20px;
            }

            .auth-sso-card p {
                margin: 0;
            }

            .auth-sso-card p + p {
                margin-top: 16px;
            }

            .auth-sso-back-button {
                margin-top: 24px;
            }

            @media (min-width: 768px) {
                .auth-page {
                    align-items: center;
                    padding-bottom: 32px;
                    padding-top: 32px;
                }

                .auth-container {
                    min-height: 560px;
                }
            }

            @media (max-height: 720px) {
                .auth-page {
                    align-items: flex-start;
                }

                .auth-container {
                    min-height: 0;
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="auth-page bg-gray-100">
            <div class="auth-container">
                <div class="auth-branding">
                    <a href="/" class="auth-logo inline-flex">
                        <x-application-logo class="fill-current text-gray-500" />
                    </a>

                    @isset($brand)
                        {{ $brand }}
                    @endisset
                </div>

                <div class="auth-card bg-white shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
