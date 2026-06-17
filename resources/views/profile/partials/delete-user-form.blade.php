<p class="text-muted">
    Menghapus akun akan menghapus seluruh data terkait akun ini secara permanen.
</p>

<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirm-user-deletion">
    <i class="fas fa-trash mr-1"></i> Hapus Akun
</button>

<div class="modal fade" id="confirm-user-deletion" tabindex="-1" role="dialog" aria-labelledby="confirm-user-deletion-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf
            @method('delete')

            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="confirm-user-deletion-label">Konfirmasi Hapus Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Cancel') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Masukkan password untuk mengonfirmasi penghapusan akun.
                </p>
                <div class="form-group mb-0">
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" placeholder="Password"
                        class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif">
                    @foreach ($errors->userDeletion->get('password') as $message)
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Hapus Permanen
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    @section('js')
        @parent
        <script>
            $('#confirm-user-deletion').modal('show');
        </script>
    @stop
@endif
