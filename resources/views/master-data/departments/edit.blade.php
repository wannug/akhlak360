@extends('adminlte::page')

@section('title', 'Edit Department')

@section('content_header')
    <h1 class="m-0">Edit Department</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Department Form" theme="warning" icon="fas fa-sitemap">
        <form method="POST" action="{{ route('master-data.departments.update', $department) }}">
            @method('PUT')
            @include('master-data.departments._form')
        </form>
    </x-adminlte-card>
@stop
