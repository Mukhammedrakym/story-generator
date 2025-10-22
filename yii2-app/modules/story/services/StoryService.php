<?php
namespace app\modules\story\services;

use Yii;
use yii\web\Response;
use app\modules\story\clients\StoryApiClient;

final class StoryService
{
    public function __construct(private StoryApiClient $client) {}

    public function streamToBrowser(array $payload): void
    {
        // Отключаем буферизацию вывода
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Устанавливаем заголовки
        header('Content-Type: text/markdown; charset=UTF-8');
        header('Cache-Control: no-cache, no-transform');
        header('X-Accel-Buffering: no');

        // Отключаем обработку ошибок Yii для этого запроса
        Yii::$app->errorHandler->discardExistingOutput = true;

        $resp = $this->client->stream($payload);

        if ($resp->getStatusCode() >= 400) {
            echo "\n\n---\n_Ошибка: Python-сервис ответил {$resp->getStatusCode()}._";
            return;
        }

        $body = $resp->getBody();
        // читаем и шлем чанки напрямую
        while (!$body->eof()) {
            $chunk = $body->read(8192);
            if ($chunk === '') {
                usleep(10000);
                continue;
            }
            echo $chunk;
        }

        // Завершаем выполнение
        exit;
    }
}