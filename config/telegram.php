<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Bot Name
    |--------------------------------------------------------------------------
    |
    | Kalau hanya pakai 1 bot, isi saja "mybot".
    |
    */

    'default' => 'mybot',

    /*
    |--------------------------------------------------------------------------
    | Daftar Bot
    |--------------------------------------------------------------------------
    |
    | Kamu bisa daftarkan lebih dari satu bot.
    | Masing-masing bot punya token sendiri.
    |
    */

    'bots' => [
        'mybot' => [
            'token' => env('TELEGRAM_BOT_TOKEN', ''), // ambil dari .env
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    |
    | Opsi tambahan untuk Guzzle (http client).
    |
    */

    'http' => [
        'timeout'  => 30,
        'connect_timeout' => 30,
    ],

];
