<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\story\models\StoryForm;
use app\modules\story\assets\StoryAsset;

/** @var $model StoryForm */
$this->title = 'Генератор сказок';
$streamUrl = Url::to(['/story/default/stream']);
$charactersUrl = Url::to(['/story/default/get-characters']);
$csrf = Yii::$app->request->getCsrfToken();

StoryAsset::register($this);
?>

<div style="max-width:800px;margin:24px auto">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
            'id' => 'story-form',
            'action' => '',
            'options' => ['onsubmit' => 'return false;'],
    ]); ?>

    <?= $form->field($model,'age')->label('Возраст')->input('number',['min'=>1,'value'=>$model->age]) ?>
    <?= $form->field($model,'language')->label('Язык')->dropDownList(['ru'=>'Русский','kk'=>'Қазақша'],['value'=>$model->language, 'id'=>'language-select']) ?>

    <div class="form-group">
        <label class="control-label">Персонажи</label>
        <div id="characters-container" class="characters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
            <!-- Персонажи будут загружены через AJAX -->
        </div>
        <div class="help-block">Выберите одного или нескольких персонажей</div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сгенерировать', ['class'=>'btn btn-primary', 'id'=>'go']) ?>
        <div id="status" style="margin-left:10px;display:none;">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span id="status-text">Подготовка к генерации...</span>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div id="out" style="border:1px solid #ddd;padding:20px;border-radius:8px;min-height:160px;background:#f9f9f9;font-family:Georgia,serif;line-height:1.6"></div>
    <div id="err" style="color:#b00020;margin-top:8px"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>

<script>
    window.storyConfig = {
        streamUrl: <?= json_encode($streamUrl) ?>,
        charactersUrl: <?= json_encode($charactersUrl) ?>,
        csrf: <?= json_encode($csrf) ?>
    };
</script>