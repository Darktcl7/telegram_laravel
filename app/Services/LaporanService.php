<?php

namespace App\Services;

use App\Models\Laporan;

class LaporanService
{
    public function getAll()
    {
        return Laporan::latest()->get();
    }

    public function find($id)
    {
        return Laporan::findOrFail($id);
    }

    public function store(array $data)
    {
        return Laporan::create($data);
    }

    public function update($id, array $data)
    {
        $laporan = $this->find($id);
        $laporan->update($data);
        return $laporan;
    }

    public function delete($id)
    {
        return Laporan::destroy($id);
    }
}
