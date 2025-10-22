<?php
use yii\helpers\Url; use yii\helpers\Html;
$this->title='Ваша сказка';
$url = Url::to(['/story/default/stream']);
?>
<h1><?= Html::encode($this->title) ?></h1>
<div id="live" style="white-space: pre-wrap; border:1px solid #ddd; padding:12px; border-radius:8px; min-height:160px;"></div>
<div id="err" style="color:#b00020; margin-top:12px;"></div>
<script>
    const payload = <?= json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
    __StoryStream.streamStory(<?= json_encode($url) ?>, payload,
        chunk => { document.getElementById('live').textContent += chunk; },
        () => {},
        e => { document.getElementById('err').textContent = 'Ошибка: ' + (e?.message || e); }
    );
</script>
