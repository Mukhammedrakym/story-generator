<?php
namespace app\modules\story;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\story\controllers';
    public $pythonApiUrl = 'http://localhost:8000';

    public function init()
    {
        parent::init();
        if (isset(\Yii::$app->params['python_api_url'])) {
            $this->pythonApiUrl = \Yii::$app->params['python_api_url'];
        }
    }
}