@extends('layouts.app')

@section('title', 'Laporan Promosi Medsos')

@section('content')
@if(isset($selectedUser))
    @include('components.business-card', ['user' => $selectedUser, 'toko' => $selectedToko])
@endif

@if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(isset($selectedUser))
<form method="POST" action="{{ route('promosi.store') }}" enctype="multipart/form-data" id="promosi-form">
    @csrf
    <input type="hidden" name="pengguna_id" value="{{ $selectedUser->id }}">
    @if(isset($selectedToko))
        <input type="hidden" name="toko_id" value="{{ $selectedToko->id }}">
    @endif

    <div id="promosi-wrapper">
        {{-- Block pertama --}}
        <div class="promosi-block border rounded p-3 mb-3" data-index="0">
            <h6>Promosi #1</h6>

            <div class="mb-2">
                <label>Platform</label>
                <select name="promosi[0][platform]" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="TikTok">TikTok</option>
                    <option value="Instagram">Instagram</option>
                    <option value="WhatsApp">WhatsApp</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Offline">Offline</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
                @error('promosi.0.platform') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-2">
                <label>Catatan</label>
                <input type="text" name="promosi[0][catatan]" class="form-control"
                       placeholder="contoh: Posting TikTok unboxing Oppo A57">
                @error('promosi.0.catatan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-2">
                <label>Foto Bukti (bisa banyak)</label>
                <input type="file" name="promosi[0][foto][]" class="form-control" multiple required>
                @error('promosi.0.foto') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-success mb-3" id="add-promosi">
        <i class="fas fa-plus"></i> Tambah Platform
    </button>

    <button type="submit" class="btn btn-primary w-100">Simpan</button>
</form>
@else
    <div class="alert alert-warning mt-4">User tidak ditemukan.</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let idx = 1;
    document.getElementById('add-promosi').addEventListener('click', function() {
        const wrapper = document.getElementById('promosi-wrapper');
        const div = document.createElement('div');
        div.className = 'promosi-block border rounded p-3 mb-3';
        div.setAttribute('data-index', idx);

        div.innerHTML = `
            <div class="mb-2">
                <label>Platform</label>
                <select name="promosi[${idx}][platform]" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="TikTok">TikTok</option>
                    <option value="Instagram">Instagram</option>
                    <option value="WhatsApp">WhatsApp</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Offline">Offline</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Catatan</label>
                <input type="text" name="promosi[${idx}][catatan]" class="form-control"
                       placeholder="contoh: Share promo ke grup WhatsApp alumni">
            </div>
            <div class="mb-2">
                <label>Foto Bukti (bisa banyak)</label>
                <input type="file" name="promosi[${idx}][foto][]" class="form-control" multiple required>
            </div>
            <button type="button" class="btn btn-outline-danger remove-block">Hapus</button>
        `;
        wrapper.appendChild(div);

        div.querySelector('.remove-block').addEventListener('click', () => div.remove());
        idx++;
    });
});
</script>
@endpush
