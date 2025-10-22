<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\story\models\StoryForm;

/** @var $model StoryForm */
$this->title = 'Генератор сказок';
$streamUrl = Url::to(['/story/default/stream']);
$csrf = Yii::$app->request->getCsrfToken();
?>
<div style="max-width:800px;margin:24px auto">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'story-form',
        'action' => '', // остаёмся на той же странице
        'options' => ['onsubmit' => 'return false;'], // запретим обычный submit
    ]); ?>

    <?= $form->field($model,'age')->input('number',['min'=>1,'value'=>$model->age]) ?>
    <?= $form->field($model,'language')->dropDownList(['ru'=>'Русский','kk'=>'Қазақша'],['value'=>$model->language]) ?>
    <?= $form->field($model,'characters')->checkboxList(StoryForm::availableCharacters(), ['value'=>$model->characters]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сгенерировать', ['class'=>'btn btn-primary', 'id'=>'go']) ?>
        <span id="status" style="margin-left:10px;color:#666"></span>
    </div>
    <?php ActiveForm::end(); ?>

    <pre id="out" style="white-space:pre-wrap;border:1px solid #ddd;padding:12px;border-radius:8px;min-height:160px"></pre>
    <div id="err" style="color:#b00020;margin-top:8px"></div>
</div>

<script>
    (() => {
        const form   = document.getElementById('story-form');
        const out    = document.getElementById('out');
        const err    = document.getElementById('err');
        const status = document.getElementById('status');
        const url    = <?= json_encode($streamUrl) ?>;
        const csrf   = <?= json_encode($csrf) ?>;

        form.addEventListener('submit', async () => {
            // собрать payload из формы
            const fd = new FormData(form);
            const age = Number(fd.get('StoryForm[age]') || 0);
            const language = fd.get('StoryForm[language]');
            const characters = fd.getAll('StoryForm[characters][]');

            out.textContent = '';
            err.textContent = '';
            status.textContent = 'Генерация...';
            form.querySelector('#go').disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrf
                    },
                    body: JSON.stringify({ age, language, characters })
                });

                if (!res.ok || !res.body) {
                    status.textContent = '';
                    const txt = await res.text();
                    err.textContent = txt || (res.status + ' ' + res.statusText);
                    form.querySelector('#go').disabled = false;
                    return;
                }

                const reader = res.body.getReader();
                const dec = new TextDecoder();
                while (true) {
                    const {value, done} = await reader.read();
                    if (done) break;
                    out.textContent += dec.decode(value, {stream: true});
                }
                status.textContent = 'Готово';
            } catch (e) {
                err.textContent = 'Ошибка сети: ' + (e?.message || e);
                status.textContent = '';
            } finally {
                form.querySelector('#go').disabled = false;
            }
        });
    })();
</script>
