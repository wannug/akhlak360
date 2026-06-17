@extends('adminlte::page')

@section('title', 'Edit Assignment')

@section('content_header')
    <h1 class="m-0">Edit Assignment</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Assignment Form" theme="warning" icon="fas fa-user-check">
        <form method="POST" action="{{ route('assessment-cycle.assign-assessors.update', $assignment) }}">
            @method('PUT')
            @include('assessment-cycle.assignments._form')
        </form>
    </x-adminlte-card>
@stop
