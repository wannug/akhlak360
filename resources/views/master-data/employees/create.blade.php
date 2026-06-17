@extends('adminlte::page')

@section('title', 'Create Employee')

@section('content_header')
    <h1 class="m-0">Create Employee</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Employee Form" theme="primary" icon="fas fa-id-badge">
        <form method="POST" action="{{ route('master-data.employees.store') }}">
            @include('master-data.employees._form')
        </form>
    </x-adminlte-card>
@stop
