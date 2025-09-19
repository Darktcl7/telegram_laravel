<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Services\LaporanService;

class LaporanController extends Controller
{
    protected $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index()
    {
        $laporan = $this->laporanService->getAll();
        return view('laporan.index', compact('laporan'));
    }

    public function create()
    {
        return view('laporan_form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal'   => 'required|date',
        ]);

        $this->laporanService->store($validated);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    public function edit($id)
    {
        $laporan = $this->laporanService->find($id);
        return view('laporan_form', compact('laporan'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal'   => 'required|date',
        ]);

        $this->laporanService->update($id, $validated);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->laporanService->delete($id);
        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }
}
