@foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $theme)
    @if (session($key))
        <div class="alert alert-{{ $theme }} alert-dismissible fade show" role="alert">
            {{ session($key) }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endforeach
