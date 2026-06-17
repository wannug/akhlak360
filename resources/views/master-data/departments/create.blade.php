@extends('adminlte::page')

@section('title', 'Create Department')

@section('content_header')
    <h1 class="m-0">Create Department</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Department Form" theme="primary" icon="fas fa-sitemap">
        <form method="POST" action="{{ route('master-data.departments.store') }}">
            @include('master-data.departments._form')
        </form>
    </x-adminlte-card>
@stop
