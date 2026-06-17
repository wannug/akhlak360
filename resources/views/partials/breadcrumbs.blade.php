@once
    <style>
        .content-header .breadcrumb {
            background: transparent;
            margin-bottom: 0;
            padding: .25rem 0 0;
            font-size: .875rem;
        }

        .card .table-responsive {
            margin-bottom: 0;
        }

        .table th {
            white-space: nowrap;
        }

        .badge {
            font-weight: 600;
        }

        canvas {
            max-height: 320px;
        }

        .small-box h3 {
            font-size: 1.75rem;
        }

        .alert-light {
            border: 1px dashed #ced4da;
            color: #6c757d;
        }

        @media (max-width: 767.98px) {
            .content-header h1 {
                font-size: 1.5rem;
            }

            .small-box h3 {
                font-size: 1.45rem;
            }
        }
    </style>
@endonce

@php
    $segments = collect(request()->segments());
    $label = fn (string $segment) => str($segment)->replace('-', ' ')->title();
@endphp

<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </li>
    @foreach ($segments as $segment)
        @php($isLast = $loop->last)
        <li class="breadcrumb-item {{ $isLast ? 'active' : '' }}" @if ($isLast) aria-current="page" @endif>
            {{ $label($segment) }}
        </li>
    @endforeach
</ol>
