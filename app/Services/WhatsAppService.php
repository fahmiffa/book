<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl = 'https://broadcast.qlabcode.com/api';
    protected string $senderNumber = '085640431181';

    /**
     * Memeriksa apakah nomor WhatsApp terdaftar/valid.
     */
    public function checkNumber(string $to): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/number", [
                'number' => $this->senderNumber,
                'to'     => $to
            ]);

            return $response->successful() && ($response->json('status') ?? false);
        } catch (\Exception $e) {
            Log::error('WhatsApp Check Number Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengirim pesan WhatsApp.
     */
    public function sendMessage(string $to, string $message): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/send", [
                'number'  => $this->senderNumber,
                'to'      => $to,
                'message' => $message
            ]);

            if ($response->failed()) {
                Log::error('WhatsApp Send Failed: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Message Error: ' . $e->getMessage());
            return false;
        }
    }
}
