<?php

namespace App\Services;

use Illuminate\Http\Response;
use GuzzleHttp\Client;

class StoryService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.python_api.url'), '/');
    }

    public function streamToBrowser(array $payload)
    {
        $client = new Client([
            'timeout' => 0,
            'read_timeout' => 0,
            'http_errors' => false,
        ]);

        $response = $client->request('POST', "{$this->apiUrl}/generate_story", [
            'headers' => [
                'Accept' => 'text/markdown',
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'stream' => true,
        ]);

        if ($response->getStatusCode() >= 400) {
            return response(
                "\n\n---\n_Ошибка: Python-сервис ответил {$response->getStatusCode()}._",
                $response->getStatusCode()
            );
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()->stream(function () use ($response) {
            $body = $response->getBody();

            while (!$body->eof()) {
                $chunk = $body->read(8192);

                if ($chunk === '') {
                    usleep(10000);
                    continue;
                }

                echo $chunk;

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
