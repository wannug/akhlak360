<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="update_password_current_password">Password Saat Ini</label>
        <input id="update_password_current_password" name="current_password" type="password"
            class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
            autocomplete="current-password">
        @foreach ($errors->updatePassword->get('current_password') as $message)
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @endforeach
    </div>

    <div class="form-group">
        <label for="update_password_password">Password Baru</label>
        <input id="update_password_password" name="password" type="password"
            class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
            autocomplete="new-password">
        @foreach ($errors->updatePassword->get('password') as $message)
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @endforeach
    </div>

    <div class="form-group">
        <label for="update_password_password_confirmation">Konfirmasi Password Baru</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password"
            class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
            autocomplete="new-password">
        @foreach ($errors->updatePassword->get('password_confirmation') as $message)
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @endforeach
    </div>

    <button type="submit" class="btn btn-warning">
        <i class="fas fa-key mr-1"></i> Perbarui Password
    </button>

    @if (session('status') === 'password-updated')
        <span class="badge badge-success ml-2">{{ __('Saved.') }}</span>
    @endif
</form>
