@extends('adminlte::page')

@section('title', 'Create Assignment')

@section('content_header')
    <h1 class="m-0">Create Assignment</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Assignment Form" theme="primary" icon="fas fa-user-check">
        <form method="POST" action="{{ route('assessment-cycle.assign-assessors.store') }}">
            @include('assessment-cycle.assignments._form')
        </form>
    </x-adminlte-card>
@stop
