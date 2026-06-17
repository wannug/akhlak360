@extends('adminlte::page')

@section('title', 'Edit Employee')

@section('content_header')
    <h1 class="m-0">Edit Employee</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Employee Form" theme="warning" icon="fas fa-id-badge">
        <form method="POST" action="{{ route('master-data.employees.update', $employee) }}">
            @method('PUT')
            @include('master-data.employees._form')
        </form>
    </x-adminlte-card>
@stop
