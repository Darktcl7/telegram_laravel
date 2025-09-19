<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FollowerController extends Controller
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

        return view('laporan_follower_form', compact('selectedUser', 'selectedToko'));
    }

    public function store(Request $request)
    {
        // Normalisasi username: hapus @ di depan
        if ($request->has('username_follower')) {
            $normalized = trim($request->username_follower);
            $normalized = preg_replace('/^@+/', '', $normalized);
            $request->merge(['username_follower' => $normalized]);
        }

        $request->validate([
            'pengguna_id'       => 'required|exists:pengguna,id',
            'toko_id'           => 'required|exists:toko,id',
            'username_follower' => 'required|string|max:255|unique:follower_tik_tok,username_follower',
            'catatan'           => 'nullable|string|max:255',
            'foto'              => 'required',
            'foto.*'            => 'image|mimes:jpeg,png,jpg|max:4096',
        ]);

        // simpan file
        $paths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $paths[] = $file->store('follower', 'public');
                // hasil: storage/app/public/follower/xxx.jpg
            }
        }

        // simpan ke tabel follower_tik_tok
        DB::table('follower_tik_tok')->insert([
            'pengguna_id'       => $request->pengguna_id,
            'toko_id'           => $request->toko_id,
            'username_follower' => $request->username_follower,
            'catatan'           => $request->catatan,
            'bukti_foto_link'   => json_encode($paths), // bisa diubah ke link supabase/gdrive
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ambil data user & toko
        $user = DB::table('pengguna')->where('id', $request->pengguna_id)->first();
        $toko = DB::table('toko')->where('id', $request->toko_id)->first();

        // format notifikasi telegram
        $message = "[Laporan Follower TikTok]\n\n" .
            "🏬 Toko: {$toko->nama_toko}\n" .
            "👤 Petugas: {$user->nama_lengkap}\n" .
            "⏰ Tanggal: " . now()->format('d-m-Y H:i') . "\n\n" .
            "Follower Baru:\n" .
            "- @{$request->username_follower} | Catatan: {$request->catatan}\n" .
            "📸 Bukti: " . count($paths) . " foto terlampir";

        $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), $message);

        return redirect()->route('follower.form', ['user_id' => $request->pengguna_id])
            ->with('success', 'Follower berhasil dicatat dan dikirim ke Telegram.');
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
