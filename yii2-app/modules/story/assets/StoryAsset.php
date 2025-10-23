<?php
namespace app\modules\story\assets;

use yii\web\AssetBundle;

class StoryAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/story/assets';
    public $baseUrl = '@web';

    public $css = [
        'css/story.css',
    ];

    public $js = [
        'js/story.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}