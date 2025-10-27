(() => {
    const form = document.getElementById("story-form");
    const out = document.getElementById("out");
    const err = document.getElementById("err");
    const status = document.getElementById("status");
    const statusText = document.getElementById("status-text");
    const languageSelect = document.getElementById("language-select");
    const charactersSelect = document.getElementById("characters-select");

    const url = window.storyConfig.streamUrl;
    const charactersUrl = window.storyConfig.charactersUrl;
    const csrf = window.storyConfig.csrf;

    async function loadCharacters(language) {
        try {
            const response = await fetch(
                `${charactersUrl}?language=${language}`
            );
            const characters = await response.json();

            charactersSelect.innerHTML = "";

            Object.entries(characters).forEach(([key, character]) => {
                const option = document.createElement("option");
                option.value = key;
                option.textContent = character;
                charactersSelect.appendChild(option);
            });

            if ($(charactersSelect).hasClass("select2-hidden-accessible")) {
                $(charactersSelect).select2("destroy");
            }

            $(charactersSelect).select2({
                theme: "bootstrap-5",
                placeholder:
                    language === "kk"
                        ? "Кейіпкерлерді таңдаңыз"
                        : "Выберите персонажей",
                allowClear: true,
                width: "100%",
            });
        } catch (error) {
            console.error("Ошибка загрузки персонажей:", error);
            err.textContent = "Ошибка загрузки персонажей";
        }
    }

    languageSelect.addEventListener("change", function () {
        loadCharacters(this.value);
    });

    marked.setOptions({
        breaks: true,
        gfm: true,
    });

    const progressMessages = [
        "Подготовка к генерации...",
        "Отправка запроса к AI...",
        "AI думает над сказкой...",
        "Генерируем текст...",
        "Почти готово...",
    ];

    let progressInterval;

    function showProgress() {
        status.style.display = "inline-block";
        let messageIndex = 0;
        statusText.textContent = progressMessages[messageIndex];

        progressInterval = setInterval(() => {
            messageIndex = (messageIndex + 1) % progressMessages.length;
            statusText.textContent = progressMessages[messageIndex];
        }, 3000);
    }

    function hideProgress() {
        status.style.display = "none";
        if (progressInterval) {
            clearInterval(progressInterval);
        }
    }

    function showEstimatedTime() {
        const estimatedTime = document.createElement("div");
        estimatedTime.id = "estimated-time";
        estimatedTime.style.cssText =
            "color: #666; font-size: 12px; margin-top: 5px;";
        estimatedTime.textContent = "Примерное время генерации: 10-20 секунд";
        status.parentNode.appendChild(estimatedTime);
    }

    function hideEstimatedTime() {
        const elem = document.getElementById("estimated-time");
        if (elem) elem.remove();
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const age = Number(formData.get("age") || 0);
        const language = formData.get("language");
        const genre = formData.get("genre");
        const characters = formData
            .getAll("characters[]")
            .filter((c) => c.trim());

        if (age <= 0) {
            err.textContent = "Возраст должен быть больше 0";
            return;
        }

        if (!language) {
            err.textContent = "Выберите язык";
            return;
        }

        if (characters.length === 0) {
            err.textContent = "Выберите хотя бы одного персонажа";
            return;
        }

        out.innerHTML = "";
        err.textContent = "";
        form.querySelector("#go").disabled = true;

        showProgress();
        showEstimatedTime();

        let markdownContent = "";
        let hasStarted = false;

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                },
                body: JSON.stringify({ age, language, genre, characters }),
            });

            if (!res.ok) {
                hideProgress();
                hideEstimatedTime();

                if (res.status === 422) {
                    const errorData = await res.json();
                    if (errorData.errors && errorData.errors.characters) {
                        err.textContent = "Выберите хотя бы одного персонажа";
                    } else {
                        err.textContent = "Ошибка валидации данных";
                    }
                } else {
                    const txt = await res.text();
                    err.textContent = txt || res.status + " " + res.statusText;
                }
                form.querySelector("#go").disabled = false;
                return;
            }

            if (!res.body) {
                hideProgress();
                hideEstimatedTime();
                err.textContent = "Ошибка получения данных";
                form.querySelector("#go").disabled = false;
                return;
            }

            const reader = res.body.getReader();
            const dec = new TextDecoder();

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;

                const chunk = dec.decode(value, { stream: true });
                markdownContent += chunk;

                if (!hasStarted && chunk.trim()) {
                    hasStarted = true;
                    statusText.textContent = "Получение сказки...";
                }

                out.innerHTML = marked.parse(markdownContent);
            }

            hideProgress();
            hideEstimatedTime();
            statusText.textContent = "Готово!";
            setTimeout(() => {
                status.style.display = "none";
            }, 2000);
        } catch (e) {
            hideProgress();
            hideEstimatedTime();
            err.textContent = "Ошибка сети: " + (e?.message || e);
        } finally {
            form.querySelector("#go").disabled = false;
        }
    });

    // Загрузить персонажей при загрузке страницы
    loadCharacters(languageSelect.value);
})();
