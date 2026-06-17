@extends('adminlte::page')

@section('title', 'Notifications')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Notifications</h1>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-double mr-1"></i> Mark All as Read
            </button>
        </form>
    </div>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    <x-adminlte-card title="Notification Center" theme="primary" icon="fas fa-bell">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr class="{{ $notification->read_at ? '' : 'font-weight-bold' }}">
                            <td>
                                <span class="badge badge-{{ $notification->read_at ? 'secondary' : 'danger' }}">
                                    {{ $notification->read_at ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td>{{ $notification->title }}</td>
                            <td>{{ $notification->message }}</td>
                            <td><span class="badge badge-info">{{ str_replace('_', ' ', $notification->type) }}</span></td>
                            <td>{{ $notification->created_at->format('d M Y H:i') }}</td>
                            <td class="text-right">
                                @unless ($notification->read_at)
                                    <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check mr-1"></i> Mark as Read
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No notifications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $notifications->links() }}
    </x-adminlte-card>
@stop
