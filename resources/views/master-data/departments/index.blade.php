@extends('adminlte::page')

@section('title', 'Departments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Departments</h1>
        <a href="{{ route('master-data.departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Create Department
        </a>
    </div>
    @include('partials.breadcrumbs')
@stop

@section('content')
    @include('partials.flash')

    <x-adminlte-card title="Department List" theme="primary" icon="fas fa-sitemap">
        <form method="GET" action="{{ route('master-data.departments.index') }}" class="mb-3">
            <div class="input-group">
                <input name="search" type="text" value="{{ request('search') }}" class="form-control"
                    placeholder="Search by code or name">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('master-data.departments.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th class="text-right">Employees</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>{{ $department->code ?? '-' }}</td>
                            <td>{{ $department->name }}</td>
                            <td>
                                <span class="badge badge-{{ $department->is_active ? 'success' : 'secondary' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">{{ $department->employees_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('master-data.departments.edit', $department) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('master-data.departments.destroy', $department) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this department? Related employees will cause it to be deactivated instead.');">
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
                            <td colspan="5" class="text-center text-muted">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $departments->links() }}
    </x-adminlte-card>
@stop
