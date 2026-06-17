@extends('adminlte::page')

@section('title', 'IT Admin Dashboard')

@section('content_header')
    <h1 class="m-0">IT Admin Dashboard</h1>
    <p class="text-muted mb-0">System overview, HRIS sync monitoring, and audit activity.</p>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['users'] }}" text="Users" icon="fas fa-user-lock" theme="primary"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['employees'] }}" text="Staff Profiles" icon="fas fa-users" theme="info"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['departments'] }}" text="Departments" icon="fas fa-building" theme="success"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['auditLogs'] }}" text="Audit Logs" icon="fas fa-shield-alt" theme="secondary"/></div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['hrisSyncs'] }}" text="HRIS Sync Logs" icon="fas fa-sync" theme="primary"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['failedHrisSyncs'] }}" text="Failed HRIS Syncs" icon="fas fa-times-circle" theme="danger"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['queuedJobs'] }}" text="Queued Jobs" icon="fas fa-stream" theme="warning"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-small-box title="{{ $stats['failedJobs'] }}" text="Failed Jobs" icon="fas fa-bug" theme="danger"/></div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <x-adminlte-card title="Recent HRIS Sync Logs" theme="primary" icon="fas fa-database">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead><tr><th>Time</th><th>Type</th><th>Status</th><th class="text-right">Records</th><th>Message</th></tr></thead>
                        <tbody>
                            @forelse ($hrisSyncLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $log->sync_type)) }}</td>
                                    <td><span class="badge badge-{{ $log->status === 'success' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span></td>
                                    <td class="text-right">{{ $log->success_records }}/{{ $log->total_records }}</td>
                                    <td>{{ $log->message ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No HRIS sync logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>
        <div class="col-lg-6">
            <x-adminlte-card title="Recent Audit Logs" theme="secondary" icon="fas fa-history">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead><tr><th>Time</th><th>User</th><th>Module</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse ($auditLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $log->user?->name ?? 'System' }}</td>
                                    <td><span class="badge badge-info">{{ $log->module }}</span></td>
                                    <td>{{ $log->action }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No audit logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop
