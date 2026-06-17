@extends('adminlte::page')

@section('title', 'Pending Assessments')

@section('content_header')
    <h1 class="m-0">Pending Assessments</h1>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    <x-adminlte-card title="Assessment Queue" theme="primary" icon="fas fa-clipboard-list">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Assessee</th>
                        <th>Department</th>
                        <th>Assessor Type</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->assessmentPeriod->name }}</td>
                            <td>{{ $assignment->assessee->name }}</td>
                            <td>{{ $assignment->assessee->department?->name ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($assignment->assessor_type) }}</span></td>
                            <td>{{ $assignment->assessmentPeriod->end_date->format('d M Y') }}</td>
                            <td><span class="badge badge-warning">{{ ucfirst($assignment->status) }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('assessment.fill.show', $assignment) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-pen mr-1"></i> Fill Assessment
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No pending assessments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $assignments->links() }}
    </x-adminlte-card>
@stop
