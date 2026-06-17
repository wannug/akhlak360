@extends('adminlte::page')

@section('title', 'Edit IDP Recommendation')

@section('content_header')
    <h1 class="m-0">Edit IDP Recommendation</h1>
    <p class="text-muted mb-0">{{ $recommendation->employee?->name }} - {{ $recommendation->assessmentPeriod?->name }}</p>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    <div class="row">
        <div class="col-lg-5">
            <x-adminlte-card title="Recommendation Detail" theme="info" icon="fas fa-info-circle">
                <dl class="mb-0">
                    <dt>Employee</dt>
                    <dd>{{ $recommendation->employee?->employee_number }} - {{ $recommendation->employee?->name }}</dd>
                    <dt>Department</dt>
                    <dd>{{ $recommendation->employee?->department?->name ?? '-' }}</dd>
                    <dt>Weakest Core Value</dt>
                    <dd><span class="badge badge-info">{{ $recommendation->weakest_core_value }}</span></dd>
                    <dt>Auto Recommendation</dt>
                    <dd>{{ $recommendation->recommendation }}</dd>
                </dl>
            </x-adminlte-card>
        </div>
        <div class="col-lg-7">
            <x-adminlte-card title="Update IDP Follow-up" theme="primary" icon="fas fa-edit">
                <form method="POST" action="{{ route('idp-talent.idp-recommendations.update', $recommendation) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="action_plan">Action Plan</label>
                        <textarea id="action_plan" name="action_plan" rows="5" class="form-control @error('action_plan') is-invalid @enderror">{{ old('action_plan', $recommendation->action_plan) }}</textarea>
                        @error('action_plan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $recommendation->due_date?->toDateString()) }}" class="form-control @error('due_date') is-invalid @enderror">
                        @error('due_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $recommendation->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('idp-talent.idp-recommendations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-save mr-1"></i> Save IDP
                        </button>
                    </div>
                </form>
            </x-adminlte-card>
        </div>
    </div>
@stop
