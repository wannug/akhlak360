@extends('adminlte::page')

@section('title', 'Edit Assessment Period')

@section('content_header')
    <h1 class="m-0">Edit Assessment Period</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Period Form" theme="warning" icon="far fa-calendar-alt">
        <form method="POST" action="{{ route('assessment-cycle.periods.update', $period) }}">
            @method('PUT')
            @include('assessment-cycle.periods._form')
        </form>
    </x-adminlte-card>
@stop
