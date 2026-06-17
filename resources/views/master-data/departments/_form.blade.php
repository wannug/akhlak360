@csrf

<div class="form-group">
    <label for="code">Code</label>
    <input id="code" name="code" type="text" value="{{ old('code', $department->code) }}"
        class="form-control @error('code') is-invalid @enderror" maxlength="50">
    @error('code')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="name">Name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $department->name) }}"
        class="form-control @error('name') is-invalid @enderror" required>
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

@if ($department->exists)
    <div class="custom-control custom-switch mb-3">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1"
            class="custom-control-input" @checked(old('is_active', $department->is_active))>
        <label class="custom-control-label" for="is_active">Active</label>
    </div>
@endif

<div class="d-flex justify-content-between">
    <a href="{{ route('master-data.departments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Save
    </button>
</div>
