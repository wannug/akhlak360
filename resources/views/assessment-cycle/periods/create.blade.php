@extends('adminlte::page')

@section('title', 'Create Assessment Period')

@section('content_header')
    <h1 class="m-0">Create Assessment Period</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Period Form" theme="primary" icon="far fa-calendar-alt">
        <form method="POST" action="{{ route('assessment-cycle.periods.store') }}">
            @include('assessment-cycle.periods._form')
        </form>
    </x-adminlte-card>
@stop
