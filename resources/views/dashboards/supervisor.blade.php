@extends('adminlte::page')

@section('title', 'Supervisor Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Supervisor Dashboard</h1>
            <p class="text-muted mb-0">Team assessment monitoring and approvals.</p>
        </div>
        <span class="badge badge-{{ $activePeriod ? 'success' : 'secondary' }} px-3 py-2">{{ $activePeriod?->name ?? 'No Active Period' }}</span>
    </div>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <div class="row">
        <div class="col-lg-2 col-6"><x-adminlte-small-box title="{{ $stats['teamMembers'] }}" text="Team Members" icon="fas fa-users" theme="primary"/></div>
        <div class="col-lg-2 col-6"><x-adminlte-small-box title="{{ $stats['completionRate'] }}%" text="Team Completion" icon="fas fa-tasks" theme="success"/></div>
        <div class="col-lg-2 col-6"><x-adminlte-small-box title="{{ $stats['pendingApprovals'] }}" text="Pending Approvals" icon="fas fa-user-check" theme="warning"/></div>
        <div class="col-lg-2 col-6"><x-adminlte-small-box title="{{ $stats['pendingAssessments'] }}" text="My Pending Assessments" icon="fas fa-edit" theme="info"/></div>
        <div class="col-lg-2 col-6"><x-adminlte-small-box title="{{ $stats['teamAverageScore'] ?? '-' }}" text="Team Avg Score" icon="fas fa-star" theme="primary"/></div>
        <div class="col-lg-2 col-6"><x-adminlte-small-box title="{{ $stats['belowThreshold'] }}" text="Below Threshold" icon="fas fa-exclamation-triangle" theme="danger"/></div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <x-adminlte-card title="Team Average per Core Value" theme="primary" icon="fas fa-chart-bar">
                @if (array_sum($coreValueChart['data']) > 0)
                    <canvas id="teamCoreValueChart" height="150"></canvas>
                @else
                    <div class="alert alert-light mb-0">No team assessment results available yet.</div>
                @endif
            </x-adminlte-card>
        </div>
        <div class="col-lg-5">
            <x-adminlte-card title="Team Members" theme="success" icon="fas fa-sitemap">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Department</th><th>Position</th></tr></thead>
                        <tbody>
                            @forelse ($teamMembers as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->department?->name ?? '-' }}</td>
                                    <td>{{ $member->position?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No team members linked to this supervisor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js')
    <script>
        const teamCoreValueData = @json($coreValueChart);
        if (document.getElementById('teamCoreValueChart')) {
            new Chart(document.getElementById('teamCoreValueChart'), {
                type: 'bar',
                data: { labels: teamCoreValueData.labels, datasets: [{ label: 'Team Average', data: teamCoreValueData.data, backgroundColor: '#007bff' }] },
                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 5 } } }
            });
        }
    </script>
@stop
