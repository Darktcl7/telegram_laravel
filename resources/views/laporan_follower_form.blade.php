@extends('layouts.app')

@section('title', 'Laporan Follower TikTok')

@section('content')

    @if(isset($selectedUser))
        @include('components.business-card', ['user' => $selectedUser, 'toko' => $selectedToko])
    @endif

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    @if(isset($selectedUser))
        <form method="POST" action="{{ route('follower.store') }}" enctype="multipart/form-data" id="follower-form">
            @csrf
            <input type="hidden" name="pengguna_id" value="{{ $selectedUser->id }}">
            @if(isset($selectedToko))
                <input type="hidden" name="toko_id" value="{{ $selectedToko->id }}">
            @endif

            <div class="mb-3">
                <label class="form-label">Username Follower</label>
                <input type="text" name="username_follower" class="form-control" placeholder="@username" required>
                @error('username_follower')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <input type="text" name="catatan" class="form-control" placeholder="contoh: hasil promosi mall">
            </div>

            <div class="mb-3">
                <label class="form-label">Bukti Foto</label>
                <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required onchange="previewImages(event)">
                <div id="preview" class="mt-2 d-flex flex-wrap"></div>
                @error('foto')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan</button>
        </form>
    @else
        <div class="alert alert-warning mt-4">User tidak ditemukan.</div>
    @endif

@endsection

@push('scripts')
<script>
function previewImages(event) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    [...event.target.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '120px';
            img.style.height = '120px';
            img.style.objectFit = 'cover';
            img.style.marginRight = '8px';
            img.style.marginBottom = '8px';
            img.style.borderRadius = '6px';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

// sebelum submit, hapus leading @
document.getElementById('follower-form').addEventListener('submit', function () {
    const input = this.querySelector('input[name="username_follower"]');
    if (input && input.value) {
        input.value = input.value.trim().replace(/^@+/, '');
    }
});
</script>
@endpush
