@extends('layouts.app')

@section('content')
<div class="container">
    <h3>{{ isset($laporan) ? 'Edit Laporan' : 'Tambah Laporan' }}</h3>

    <form method="POST" 
          action="{{ isset($laporan) ? route('laporan.update', $laporan->id) : route('laporan.store') }}">
        @csrf
        @if(isset($laporan))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="judul" class="form-label">Judul</label>
            <input type="text" name="judul" id="judul" class="form-control" 
                   value="{{ old('judul', $laporan->judul ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control">{{ old('deskripsi', $laporan->deskripsi ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control" 
                   value="{{ old('tanggal', $laporan->tanggal ?? '') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection

@push('scripts')
    @vite('resources/js/laporan.js')
@endpush
