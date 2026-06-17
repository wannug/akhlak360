@extends('adminlte::page')

@section('title', 'Konfirmasi Password')

@section('content_header')
    <h1 class="m-0">Konfirmasi Password</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <x-adminlte-card title="Area Aman" theme="primary" icon="fas fa-shield-alt">
                <p class="text-muted">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="form-group">
                        <label for="password">{{ __('Password') }}</label>
                        <input id="password" class="form-control @error('password') is-invalid @enderror"
                            type="password" name="password" required autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> {{ __('Confirm') }}
                    </button>
                </form>
            </x-adminlte-card>
        </div>
    </div>
@stop
