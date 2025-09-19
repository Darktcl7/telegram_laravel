<?php

namespace App\Services;

use Illuminate\Support\Str;
use GuzzleHttp\Client;

class SupabaseStorageService
{
    protected $client;
    protected $url;
    protected $bucket;
    protected $key;

    public function __construct()
    {
        $this->url    = env('SUPABASE_URL');
        $this->bucket = env('SUPABASE_BUCKET');

        // ⚡ gunakan SERVICE_ROLE_KEY untuk server-side
        $this->key    = env('SUPABASE_SERVICE_ROLE_KEY');

        $this->client = new Client([
            'base_uri' => $this->url,
        ]);
    }

    /**
     * Upload file ke Supabase Storage
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return string public URL
     * @throws \Exception
     */
    public function upload($file, $folder = 'promosi')
    {
        $path = trim($folder, '/') . '/' . time() . '_' . Str::random(8) . '_' . $file->getClientOriginalName();

        $response = $this->client->request('POST', "/storage/v1/object/{$this->bucket}/{$path}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type'  => $file->getMimeType(),
                'x-upsert'      => 'true',
            ],
            'body' => file_get_contents($file->getRealPath()),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Upload ke Supabase gagal: " . $response->getBody());
        }

        // URL publik
        return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
    }
}
