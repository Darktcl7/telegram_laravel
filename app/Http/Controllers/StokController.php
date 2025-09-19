<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class StokController extends Controller
{
    public function store(Request $request)
    {
        $user = DB::table('pengguna')->where('id', $request->promotor_id)->first();
        $toko = DB::table('toko')->where('id', $request->toko_id)->first();
        $now  = now()->format('d-m-Y H:i');

        if ($request->status === 'tidak') {
            $message = "[ Laporan Cek Stok ]\n\n" .
                "🏬 Toko: {$toko->nama_toko}\n" .
                "📌 Status: Tidak ada barang baru masuk hari ini\n" .
                "👤 Petugas: {$user->nama_lengkap}\n" .
                "⏰ Waktu: {$now}";

            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), $message);

            return back()->with('success', 'Laporan cek stok berhasil dicatat.');
        }

        if ($request->status === 'ada') {
            $request->validate([
                'produk' => 'required|array|min:1',
                'produk.*.produk_id' => 'required|exists:produk,id',
                'produk.*.imei' => 'required|array|min:1',
                'produk.*.imei.*' => 'distinct|unique:stok,imei',
            ]);

            $totalCount = 0;
            $messages = [];

            foreach ($request->produk as $produkData) {
                $produk = DB::table('produk')->where('id', $produkData['produk_id'])->first();
                $count = 0;

                foreach ($produkData['imei'] as $imei) {
                    if (!empty($imei)) {
                        DB::table('stok')->insert([
                            'produk_id'   => $produk->id,
                            'promotor_id' => $user->id,
                            'toko_id'     => $toko->id,
                            'imei'        => $imei
                        ]);
                        $count++;
                        $totalCount++;
                    }
                }

                if ($count > 0) {
                    $messages[] = "📦 Produk: {$produk->nama_model} - {$produk->varian_ram_rom} - {$produk->warna}\n" .
                        "🔢 Jumlah: {$count} unit";
                }
            }

            $message = "[ Barang Baru Masuk ]\n\n" .
                "🏬 Toko: {$toko->nama_toko}\n" .
                "👤 Petugas: {$user->nama_lengkap}\n" .
                implode("\n\n", $messages) . "\n" .
                "⏰ Waktu: {$now}";

            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), $message);

            return back()->with('success', "Barang baru ({$totalCount} unit) berhasil dicatat.");
        }
    }

    private function sendTelegramMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        try {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal kirim pesan Telegram: " . $e->getMessage());
        }
    }

    public function showForm(Request $request)
    {
        $produks = DB::table('produk')->get();
        $selectedUser = null;
        $selectedToko = null;

        if ($request->has('user_id')) {
            $selectedUser = DB::table('pengguna')->where('id', $request->query('user_id'))->first();
            if ($selectedUser && $selectedUser->toko_id) {
                $selectedToko = DB::table('toko')->where('id', $selectedUser->toko_id)->first();
            }
        }

        return view('stok_form', compact('produks', 'selectedUser', 'selectedToko'));
    }
}
