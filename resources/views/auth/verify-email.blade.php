@extends('adminlte::page')

@section('title', 'Verifikasi Email')

@section('content_header')
    <h1 class="m-0">Verifikasi Email</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <x-adminlte-card title="Verifikasi Akun" theme="warning" icon="fas fa-envelope-open-text">
                <p class="text-muted">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div class="d-flex flex-column flex-sm-row justify-content-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-paper-plane mr-1"></i> {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2 mt-sm-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt mr-1"></i> {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop
