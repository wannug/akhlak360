@forelse ($notifications as $notification)
    <a href="{{ route('notifications.index') }}" class="dropdown-item">
        <div class="media">
            <div class="media-body">
                <h3 class="dropdown-item-title">
                    {{ $notification->title }}
                    @unless ($notification->read_at)
                        <span class="float-right text-sm text-danger"><i class="fas fa-circle"></i></span>
                    @endunless
                </h3>
                <p class="text-sm text-muted mb-1">{{ Str::limit($notification->message, 70) }}</p>
                <p class="text-sm text-muted mb-0">
                    <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </a>
    <div class="dropdown-divider"></div>
@empty
    <span class="dropdown-item text-muted">No notifications</span>
@endforelse
