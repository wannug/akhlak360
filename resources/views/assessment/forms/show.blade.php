@extends('adminlte::page')

@section('title', 'Fill Assessment')

@section('content_header')
    <div>
        <h1 class="m-0">Fill Assessment</h1>
        <p class="text-muted mb-0">
            {{ $assignment->assessmentPeriod->name }} · {{ ucfirst($assignment->assessor_type) }} assessment for {{ $assignment->assessee->name }}
        </p>
    </div>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    @if ($errors->any())
        <div class="alert alert-danger">
            Please complete all 18 indicators before submitting.
        </div>
    @endif

    <form method="POST" action="{{ route('assessment.submit', $assignment) }}">
        @csrf

        @foreach ($indicators as $coreValue => $items)
            <x-adminlte-card :title="$coreValue" theme="primary" icon="fas fa-star">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 34%">Indicator</th>
                                @foreach ($scale as $value => $label)
                                    <th class="text-center" style="width: 13.2%">
                                        <span class="badge badge-secondary">{{ $value }}</span>
                                        <div class="small font-weight-normal">{{ $label }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $indicator)
                                <tr>
                                    <td>
                                        {{ $indicator }}
                                        @error("scores.{$coreValue}.{$index}")
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    @foreach ($scale as $value => $label)
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-radio d-inline-block">
                                                <input id="score_{{ md5($coreValue.$index.$value) }}"
                                                    name="scores[{{ $coreValue }}][{{ $index }}]"
                                                    type="radio"
                                                    value="{{ $value }}"
                                                    class="custom-control-input"
                                                    @checked((string) old("scores.{$coreValue}.{$index}") === (string) $value)
                                                    required>
                                                <label class="custom-control-label" for="score_{{ md5($coreValue.$index.$value) }}">
                                                    <span class="sr-only">{{ $label }}</span>
                                                </label>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        @endforeach

        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <a href="{{ route('assessment.pending.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <button type="submit" class="btn btn-success" onclick="return confirm('Submit this assessment? You cannot submit it twice.');">
                    <i class="fas fa-paper-plane mr-1"></i> Submit Assessment
                </button>
            </div>
        </div>
    </form>
@stop
