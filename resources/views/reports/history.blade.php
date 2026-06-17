@extends('adminlte::page')

@section('title', 'Export History')

@section('content_header')
    <h1 class="m-0">Export History</h1>
    <p class="text-muted mb-0">Generated and failed report export activity.</p>
    @include('partials.breadcrumbs')
@stop

@section('content')
    <x-adminlte-card title="Export History" theme="primary" icon="fas fa-history">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Period</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>File Path</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exports as $export)
                        <tr>
                            <td>{{ $export->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $export->user?->name ?? '-' }}</td>
                            <td>{{ $export->assessmentPeriod?->name ?? 'All periods' }}</td>
                            <td><span class="badge badge-info">{{ strtoupper($export->report_type) }}</span></td>
                            <td>
                                <span class="badge badge-{{ $export->status === 'generated' ? 'success' : 'danger' }}">
                                    {{ ucfirst($export->status) }}
                                </span>
                            </td>
                            <td>{{ $export->file_path ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No export activity found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $exports->links() }}
        </div>
    </x-adminlte-card>
@stop
