<?php
namespace app\modules\story\clients;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

final class StoryApiClient {
    private Client $http;
    public function __construct(string $baseUrl) {
        $this->http = new Client(['base_uri'=>$baseUrl,'timeout'=>0,'http_errors'=>false]);
    }
    public function stream(array $payload): ResponseInterface {
        return $this->http->post('/generate_story', [
            'headers'=>['Accept'=>'text/markdown','Content-Type'=>'application/json'],
            'json'=>$payload,
            'stream'=>true,
        ]);
    }
}
