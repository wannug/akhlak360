@extends('adminlte::page')

@section('title', 'Assessment Weights')

@section('content_header')
    <h1 class="m-0">Assessment Weights</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    <div class="row">
        <div class="col-lg-4">
            <x-adminlte-card title="Select Period" theme="primary" icon="far fa-calendar-alt">
                <form method="GET" action="{{ route('assessment-cycle.weights.index') }}">
                    <div class="form-group">
                        <label for="assessment_period_id">Assessment Period</label>
                        <select id="assessment_period_id" name="assessment_period_id" class="form-control" onchange="this.form.submit()">
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected($selectedPeriod?->id === $period->id)>
                                    {{ $period->name }} - {{ ucfirst($period->status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-filter mr-1"></i> Load
                    </button>
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-lg-8">
            <x-adminlte-card title="Weight Configuration" theme="success" icon="fas fa-balance-scale">
                @if (! $selectedPeriod)
                    <p class="text-muted mb-0">Create an assessment period before configuring weights.</p>
                @else
                    @error('weights')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('assessment-cycle.weights.update') }}">
                        @csrf
                        <input type="hidden" name="assessment_period_id" value="{{ $selectedPeriod->id }}">

                        <div class="row">
                            @foreach ([
                                'supervisor' => ['label' => 'Supervisor', 'icon' => 'fas fa-user-tie'],
                                'peer' => ['label' => 'Peer', 'icon' => 'fas fa-user-friends'],
                                'subordinate' => ['label' => 'Subordinate', 'icon' => 'fas fa-users'],
                                'self' => ['label' => 'Self', 'icon' => 'fas fa-user'],
                            ] as $type => $meta)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="weight_{{ $type }}">
                                            <i class="{{ $meta['icon'] }} mr-1"></i> {{ $meta['label'] }}
                                        </label>
                                        <div class="input-group">
                                            <input id="weight_{{ $type }}" name="weights[{{ $type }}]" type="number"
                                                value="{{ old('weights.'.$type, $weights[$type]) }}"
                                                class="form-control weight-input @error('weights.'.$type) is-invalid @enderror"
                                                min="0" max="100" step="0.01" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('weights.'.$type)
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span>Total Weight</span>
                            <strong><span id="weight-total">100</span>%</strong>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Save Weights
                        </button>
                    </form>
                @endif
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js')
    <script>
        const weightInputs = document.querySelectorAll('.weight-input');
        const weightTotal = document.getElementById('weight-total');

        function updateWeightTotal() {
            if (!weightTotal) {
                return;
            }

            const total = Array.from(weightInputs).reduce((sum, input) => sum + Number(input.value || 0), 0);
            weightTotal.textContent = total.toFixed(2).replace(/\.00$/, '');
            weightTotal.parentElement.classList.toggle('text-danger', Math.round(total * 100) / 100 !== 100);
        }

        weightInputs.forEach((input) => input.addEventListener('input', updateWeightTotal));
        updateWeightTotal();
    </script>
@stop
