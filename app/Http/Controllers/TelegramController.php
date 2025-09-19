<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram update received', $update);

        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $text   = strtolower(trim($update['message']['text'] ?? ''));

            // normalisasi command
            $text = explode(' ', $text)[0];
            $text = explode('@', $text)[0];

            if (str_starts_with($text, '/lapor')) {
                Log::info("Trigger /lapor terdeteksi untuk chatId: " . $chatId);
                $this->handleLapor($chatId);
            } else {
                $this->sendMessage(
                    $chatId,
                    "❌ Perintah tidak dikenal.\n\nGunakan: /lapor"
                );
            }
        }

        return response('OK', 200);
    }

    private function handleLapor($chatId)
    {
        Log::info("Handle /lapor dipanggil untuk chatId: " . $chatId);

        $user = DB::table('pengguna')->where('telegram_user_id', $chatId)->first();

        if (!$user) {
            $this->sendMessage($chatId, "❌ Akun Telegram Anda tidak terdaftar di sistem.");
            return;
        }

        $token = env('TELEGRAM_BOT_TOKEN');
        $api   = "https://api.telegram.org/bot{$token}/sendMessage";

        $laporanUrl  = secure_url('/laporan?user_id=' . $user->id);
        $stokUrl     = secure_url('/stok?user_id=' . $user->id);
        $followerUrl = secure_url('/follower?user_id=' . $user->id);
        $promosiUrl  = secure_url('/promosi?user_id=' . $user->id);

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text'    => '📄 Laporan Penjualan',
                        'web_app' => ['url' => $laporanUrl]
                    ]
                ],
                [
                    [
                        'text'    => '📦 Laporan Stok',
                        'web_app' => ['url' => $stokUrl]
                    ]
                ],
                [
                    [
                        'text'    => '👥 Laporan Follower TikTok',
                        'web_app' => ['url' => $followerUrl]
                    ]
                ],
                [
                    [
                        'text'    => '📢 Laporan Promosi Medsos',
                        'web_app' => ['url' => $promosiUrl]
                    ]
                ]
            ]
        ];

        $response = Http::post($api, [
            'chat_id'      => $chatId,
            'text'         => "Silakan pilih jenis laporan:",
            'reply_markup' => json_encode($keyboard),
        ]);

        Log::info("Response Telegram handleLapor: " . $response->body());
    }


    private function sendMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url   = "https://api.telegram.org/bot{$token}/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text'    => $text,
        ]);

        Log::info("Response Telegram sendMessage: " . $response->body());
    }
}
