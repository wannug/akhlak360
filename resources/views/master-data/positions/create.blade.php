@extends('adminlte::page')

@section('title', 'Create Position')

@section('content_header')
    <h1 class="m-0">Create Position</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Position Form" theme="primary" icon="fas fa-briefcase">
        <form method="POST" action="{{ route('master-data.positions.store') }}">
            @include('master-data.positions._form')
        </form>
    </x-adminlte-card>
@stop
