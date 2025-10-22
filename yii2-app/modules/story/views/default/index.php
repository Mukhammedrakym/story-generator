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

    <!-- Улучшенные чекбоксы -->
    <div class="form-group">
        <label class="control-label">Персонажи</label>
        <div class="characters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
            <?php foreach (StoryForm::availableCharacters() as $key => $label): ?>
                <div class="character-item" style="display: flex; align-items: center; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;">
                    <input type="checkbox"
                           name="StoryForm[characters][]"
                           value="<?= Html::encode($key) ?>"
                           id="char_<?= $key ?>"
                            <?= in_array($key, $model->characters) ? 'checked' : '' ?>
                           style="margin-right: 8px; transform: scale(1.2);">
                    <label for="char_<?= $key ?>" style="margin: 0; cursor: pointer; flex: 1;">
                        <?= Html::encode($label) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="help-block">Выберите одного или нескольких персонажей</div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сгенерировать', ['class'=>'btn btn-primary', 'id'=>'go']) ?>
        <span id="status" style="margin-left:10px;color:#666"></span>
    </div>
    <?php ActiveForm::end(); ?>

    <!-- Изменяем pre на div для HTML контента -->
    <div id="out" style="border:1px solid #ddd;padding:20px;border-radius:8px;min-height:160px;background:#f9f9f9;font-family:Georgia,serif;line-height:1.6"></div>
    <div id="err" style="color:#b00020;margin-top:8px"></div>
</div>

<!-- Подключаем библиотеку для рендеринга Markdown -->
<script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>

<script>
    (() => {
        const form   = document.getElementById('story-form');
        const out    = document.getElementById('out');
        const err    = document.getElementById('err');
        const status = document.getElementById('status');
        const url    = <?= json_encode($streamUrl) ?>;
        const csrf   = <?= json_encode($csrf) ?>;

        // Настройка marked для красивого рендеринга
        marked.setOptions({
            breaks: true,
            gfm: true,
            sanitize: false
        });

        form.addEventListener('submit', async () => {
            // собрать payload из формы
            const fd = new FormData(form);
            const age = Number(fd.get('StoryForm[age]') || 0);
            const language = fd.get('StoryForm[language]');
            const characters = fd.getAll('StoryForm[characters][]');

            out.innerHTML = '';
            err.textContent = '';
            status.textContent = 'Генерация...';
            form.querySelector('#go').disabled = true;

            let markdownContent = '';

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

                    const chunk = dec.decode(value, {stream: true});
                    markdownContent += chunk;

                    // Рендерим накопленный Markdown в HTML
                    out.innerHTML = marked.parse(markdownContent);
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

<style>
    /* Стили для чекбоксов */
    .characters-grid {
        margin-top: 10px;
    }

    .character-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .character-item:hover {
        background: #e9ecef !important;
        border-color: #007bff !important;
    }

    .character-item input[type="checkbox"]:checked + label {
        font-weight: bold;
        color: #007bff;
    }

    .character-item:has(input[type="checkbox"]:checked) {
        background: #e3f2fd !important;
        border-color: #007bff !important;
    }

    /* Дополнительные стили для красивого отображения */
    #out h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    #out h2 {
        color: #34495e;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    #out p {
        margin-bottom: 15px;
        text-align: justify;
    }

    #out strong {
        color: #2980b9;
        font-weight: 600;
    }

    #out em {
        color: #7f8c8d;
        font-style: italic;
    }

    #out hr {
        border: none;
        border-top: 1px solid #bdc3c7;
        margin: 20px 0;
    }

    #out blockquote {
        border-left: 4px solid #3498db;
        margin: 20px 0;
        padding-left: 20px;
        color: #7f8c8d;
        font-style: italic;
    }
</style>