<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\modules\story\models\StoryForm;

/** @var $model StoryForm */
$this->title = 'Генератор сказок';

$form = ActiveForm::begin([
    'action' => ['/story/default/result'], // ✅ Добавьте action
    'method' => 'post',
    'id' => 'story-form'
]);

echo $form->field($model,'age')->input('number',['min'=>1,'value'=>6]);
echo $form->field($model,'language')->dropDownList(['ru'=>'Русский','kk'=>'Қазақша'],['prompt'=>'Выберите язык']);
echo $form->field($model,'characters')->checkboxList(StoryForm::availableCharacters());
echo Html::submitButton('Сгенерировать', ['class'=>'btn btn-primary']);

ActiveForm::end();