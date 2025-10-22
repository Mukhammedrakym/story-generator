<?php
// modules/story/controllers/DefaultController.php
namespace app\modules\story\controllers;

use yii\web\Controller;
use yii\web\Response;
use app\modules\story\models\StoryForm;
use app\modules\story\clients\StoryApiClient;
use app\modules\story\services\StoryService;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $model = new StoryForm();
        // можно дефолты
        $model->age = 6;
        $model->language = 'kk';
        $model->characters = ['Заяц', 'Алдар Көсе'];

        return $this->render('index', ['model' => $model]);
    }

    public function actionStream()
    {
        $payload = \Yii::$app->request->getBodyParams();

        $model = new StoryForm();
        $model->attributes = $payload;

        if (!$model->validate()) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            \Yii::$app->response->statusCode = 422;
            return ['error' => 'ValidationError', 'details' => $model->getErrors()];
        }

        $client  = new StoryApiClient($this->module->pythonApiUrl);
        $service = new StoryService($client);

        $service->streamToBrowser([
            'age'        => (int)$model->age,
            'language'   => $model->language,
            'characters' => array_values($model->characters),
        ]);

        // Этот return никогда не выполнится из-за exit в streamToBrowser
        return '';
    }
}