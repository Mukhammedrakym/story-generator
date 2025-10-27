(() => {
    const form   = document.getElementById('story-form');
    const out    = document.getElementById('out');
    const err    = document.getElementById('err');
    const status = document.getElementById('status');
    const statusText = document.getElementById('status-text');
    const languageSelect = document.getElementById('language-select');
    const charactersContainer = document.getElementById('characters-container');
    const url    = window.storyConfig.streamUrl;
    const charactersUrl = window.storyConfig.charactersUrl;
    const csrf   = window.storyConfig.csrf;

    async function loadCharacters(language) {
        try {
            const response = await fetch(`${charactersUrl}?language=${language}`);
            const characters = await response.json();

            charactersContainer.innerHTML = '';

            Object.entries(characters).forEach(([key, label]) => {
                const characterItem = document.createElement('div');
                characterItem.className = 'character-item';
                characterItem.style.cssText = 'display: flex; align-items: center; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'StoryForm[characters][]';
                checkbox.value = key;
                checkbox.id = `char_${key}`;
                checkbox.style.cssText = 'margin-right: 8px; transform: scale(1.2);';

                const labelElement = document.createElement('label');
                labelElement.htmlFor = `char_${key}`;
                labelElement.style.cssText = 'margin: 0; cursor: pointer; flex: 1;';
                labelElement.textContent = label;

                characterItem.appendChild(checkbox);
                characterItem.appendChild(labelElement);
                charactersContainer.appendChild(characterItem);
            });
        } catch (error) {
            console.error('Ошибка загрузки персонажей:', error);
            charactersContainer.innerHTML = '<div style="color: red;">Ошибка загрузки персонажей</div>';
        }
    }

    languageSelect.addEventListener('change', function() {
        loadCharacters(this.value);
    });

    marked.setOptions({
        breaks: true,
        gfm: true,
        sanitize: false
    });

    const progressMessages = [
        'Подготовка к генерации...',
        'Отправка запроса к AI...',
        'AI думает над сказкой...',
        'Генерируем текст...',
        'Почти готово...'
    ];

    let progressInterval;

    function showProgress() {
        status.style.display = 'inline-block';
        let messageIndex = 0;

        statusText.textContent = progressMessages[messageIndex];

        progressInterval = setInterval(() => {
            messageIndex = (messageIndex + 1) % progressMessages.length;
            statusText.textContent = progressMessages[messageIndex];
        }, 3000);
    }

    function hideProgress() {
        status.style.display = 'none';
        if (progressInterval) {
            clearInterval(progressInterval);
        }
    }

    function showEstimatedTime() {
        const estimatedTime = document.createElement('div');
        estimatedTime.id = 'estimated-time';
        estimatedTime.style.cssText = 'color: #666; font-size: 12px; margin-top: 5px;';
        estimatedTime.textContent = 'Примерное время генерации: 10-20 секунд';
        status.parentNode.appendChild(estimatedTime);
    }

    function hideEstimatedTime() {
        const elem = document.getElementById('estimated-time');
        if (elem) elem.remove();
    }

    form.addEventListener('submit', async () => {
        const fd = new FormData(form);
        const age = Number(fd.get('StoryForm[age]') || 0);
        const language = fd.get('StoryForm[language]');
        const genre = fd.get('StoryForm[genre]');
        console.log(genre);
        const characters = fd.getAll('StoryForm[characters][]');

        if (age <= 0) {
            err.textContent = 'Возраст должен быть больше 0';
            return;
        }

        if (!language) {
            err.textContent = 'Выберите язык';
            return;
        }

        if (characters.length === 0) {
            err.textContent = 'Выберите хотя бы одного персонажа';
            return;
        }

        out.innerHTML = '';
        err.textContent = '';
        form.querySelector('#go').disabled = true;

        showProgress();
        showEstimatedTime();

        let markdownContent = '';
        let hasStarted = false;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify({ age, language, genre, characters })
            });

            if (!res.ok) {
                hideProgress();
                hideEstimatedTime();

                if (res.status === 422) {
                    const errorData = await res.json();
                    if (errorData.details && errorData.details.characters) {
                        err.textContent = 'Выберите хотя бы одного персонажа';
                    } else {
                        err.textContent = 'Ошибка валидации данных';
                    }
                } else {
                    const txt = await res.text();
                    err.textContent = txt || (res.status + ' ' + res.statusText);
                }
                form.querySelector('#go').disabled = false;
                return;
            }

            if (!res.body) {
                hideProgress();
                hideEstimatedTime();
                err.textContent = 'Ошибка получения данных';
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

                if (!hasStarted && chunk.trim()) {
                    hasStarted = true;
                    statusText.textContent = 'Получение сказки...';
                }

                out.innerHTML = marked.parse(markdownContent);
            }

            hideProgress();
            hideEstimatedTime();
            statusText.textContent = 'Готово!';
            setTimeout(() => {
                status.style.display = 'none';
            }, 2000);

        } catch (e) {
            hideProgress();
            hideEstimatedTime();
            err.textContent = 'Ошибка сети: ' + (e?.message || e);
        } finally {
            form.querySelector('#go').disabled = false;
        }
    });

    loadCharacters(languageSelect.value);
})();