@extends('adminlte::page')

@section('title', 'Edit Position')

@section('content_header')
    <h1 class="m-0">Edit Position</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Position Form" theme="warning" icon="fas fa-briefcase">
        <form method="POST" action="{{ route('master-data.positions.update', $position) }}">
            @method('PUT')
            @include('master-data.positions._form')
        </form>
    </x-adminlte-card>
@stop
