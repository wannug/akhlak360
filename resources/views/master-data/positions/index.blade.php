@extends('adminlte::page')

@section('title', 'Positions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Positions</h1>
        <a href="{{ route('master-data.positions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Create Position
        </a>
    </div>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    <x-adminlte-card title="Position List" theme="primary" icon="fas fa-briefcase">
        <form method="GET" action="{{ route('master-data.positions.index') }}" class="mb-3">
            <div class="input-group">
                <input name="search" type="text" value="{{ request('search') }}" class="form-control"
                    placeholder="Search by name or level">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('master-data.positions.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Level</th>
                        <th class="text-right">Employees</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($positions as $position)
                        <tr>
                            <td>{{ $position->name }}</td>
                            <td>{{ $position->level ?? '-' }}</td>
                            <td class="text-right">{{ $position->employees_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('master-data.positions.edit', $position) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('master-data.positions.destroy', $position) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this position? Existing employee records will keep working with empty position references.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No positions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $positions->links() }}
    </x-adminlte-card>
@stop
