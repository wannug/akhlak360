@csrf

<div class="form-group">
    <label for="name">Name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $position->name) }}"
        class="form-control @error('name') is-invalid @enderror" required>
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="level">Level</label>
    <input id="level" name="level" type="text" value="{{ old('level', $position->level) }}"
        class="form-control @error('level') is-invalid @enderror" maxlength="100">
    @error('level')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('master-data.positions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Save
    </button>
</div>
