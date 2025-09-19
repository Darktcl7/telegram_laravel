<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\SupabaseStorageService;

class PromosiMedsosController extends Controller
{
    public function form(Request $request)
    {
        $selectedUser = null;
        $selectedToko = null;

        if ($request->has('user_id')) {
            $selectedUser = DB::table('pengguna')->where('id', $request->query('user_id'))->first();
            if ($selectedUser && $selectedUser->toko_id) {
                $selectedToko = DB::table('toko')->where('id', $selectedUser->toko_id)->first();
            }
        }

        return view('laporan_promosi_form', compact('selectedUser', 'selectedToko'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengguna_id'          => 'required|exists:pengguna,id',
            'toko_id'              => 'required|exists:toko,id',
            'promosi'              => 'required|array|min:1',
            'promosi.*.platform'   => 'required|string',
            'promosi.*.catatan'    => 'nullable|string|max:255',
            'promosi.*.foto'       => 'required|array|min:1',
            'promosi.*.foto.*'     => 'image|mimes:jpeg,png,jpg|max:8192',
        ]);

        $storage = new SupabaseStorageService();

        // Insert header promosi
        $promosiHeaderId = DB::table('promosi_medsos')->insertGetId([
            'pengguna_id' => $request->pengguna_id,
            'toko_id'     => $request->toko_id,
            'created_at'  => now(),
        ]);

        $detailsLines = [];
        $totalFoto = 0;

        $promosiList = $request->input('promosi', []);

        foreach ($promosiList as $i => $item) {
            $platform = $item['platform'] ?? '';
            $catatan  = $item['catatan'] ?? '';
            $fotoUrls = [];

            $files = $request->file("promosi.{$i}.foto");

            if ($files && (is_array($files) || $files instanceof \Illuminate\Http\UploadedFile)) {
                // If it's a single file (e.g., if 'multiple' attribute was missing or only one file uploaded)
                if ($files instanceof \Illuminate\Http\UploadedFile) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $fotoUrls[] = $storage->upload($file, 'promosi');
                        $totalFoto++;
                    } else {
                        Log::warning("Invalid file found for platform {$platform} at index {$i}.");
                    }
                }
            } else {
                Log::warning("No valid files array/object found for platform {$platform} at index {$i}. Received type: " . gettype($files));
            }

            // simpan detail
            DB::table('promosi_medsos_detail')->insert([
                'promosi_id' => $promosiHeaderId,
                'platform'   => $platform,
                'catatan'    => $catatan,
                'foto'       => json_encode($fotoUrls),
                'created_at' => now(),
            ]);

            $detailsLines[] = "- {$platform}: {$catatan}";
        }

        // Kirim notifikasi Telegram
        $user = DB::table('pengguna')->where('id', $request->pengguna_id)->first();
        $toko = DB::table('toko')->where('id', $request->toko_id)->first();
        $now  = now()->format('d-m-Y H:i');

        $message = "[Laporan Promosi Medsos]\n\n" .
            "🏬 Toko: {$toko->nama_toko}\n" .
            "👤 Petugas: {$user->nama_lengkap}\n" .
            "⏰ Tanggal: {$now}\n\n" .
            "📊 Total Promosi: {$totalFoto}\n" .
            implode("\n", $detailsLines);

        $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), $message);

        return redirect()->route('promosi.form', ['user_id' => $request->pengguna_id])
            ->with('success', 'Laporan promosi berhasil dicatat.');
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
}
