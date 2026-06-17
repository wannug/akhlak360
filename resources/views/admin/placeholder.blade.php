@extends('adminlte::page')

@section('title', $title ?? 'AKHLAK 360')

@section('content_header')
    <h1 class="m-0">{{ $title ?? 'AKHLAK 360' }}</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card :title="$title ?? 'AKHLAK 360'" theme="primary" icon="fas fa-lock">
        <p class="text-muted mb-0">
            {{ $description ?? 'Halaman ini dilindungi role-based access control untuk MVP Sistem Penilaian 360 Core Values AKHLAK.' }}
        </p>
    </x-adminlte-card>
@stop
