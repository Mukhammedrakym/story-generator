<?php
namespace app\modules\story\services;

use app\modules\story\clients\StoryApiClient;
use app\modules\story\exceptions\UpstreamException;

final class StoryService {
    public function __construct(private StoryApiClient $client) {}

    public function streamToBrowser(array $payload): void {
        $res = $this->client->stream($payload);

        if ($res->getStatusCode() >= 400) {
            throw new UpstreamException((string)$res->getBody(), $res->getStatusCode());
        }

        $body = $res->getBody();
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type','text/markdown; charset=utf-8');
        $response->headers->set('Cache-Control','no-cache');
        $response->headers->set('X-Accel-Buffering','no');

        @ini_set('output_buffering','off');
        @ini_set('zlib.output_compression','0');
        while (ob_get_level()>0) { @ob_end_flush(); }
        ob_implicit_flush(true);
        set_time_limit(0);

        $response->stream = function() use ($body) {
            while (!$body->eof()) {
                echo $body->read(8192);
                flush();
            }
        };
    }
}

