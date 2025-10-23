# Story Generator Python API

REST API сервис для генерации сказок с использованием OpenAI или Google Gemini API.

## Установка

### Предварительные требования

- Python 3.9+
- OpenAI API ключ или Google Gemini API ключ

### Установка зависимостей

```bash
# Создайте виртуальное окружение
python -m venv venv

# Активируйте виртуальное окружение
# Linux/Mac:
source venv/bin/activate
# Windows:
venv\Scripts\activate

# Установите зависимости
pip install -r requirements.txt
```

### Конфигурация

1. **Создайте файл `.env` в корне папки `python-service`:**
   ```env
   # Выберите провайдера LLM: "openai" или "gemini"
   LLM_PROVIDER=openai
   
   # OpenAI настройки
   OPENAI_API_KEY=your_openai_api_key_here
   OPENAI_MODEL=gpt-4o-mini
   
   # Gemini настройки
   GEMINI_API_KEY=your_gemini_api_key_here
   GEMINI_MODEL=gemini-2.5-flash
   
   # CORS настройки
   CORS_ORIGINS=["*"]
   ```

2. **Получите API ключи:**
    - OpenAI: https://platform.openai.com/api-keys
    - Google Gemini: https://makersuite.google.com/app/apikey

## ️ Запуск

```bash
# Запуск в режиме разработки
python main.py

# Или через uvicorn напрямую
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

Сервис будет доступен по адресу: http://localhost:8000

## API Документация

### Endpoints

#### POST /generate_story

Генерирует сказку с заданными параметрами.

**Параметры запроса:**
```json
{
  "age": 6,
  "language": "ru",
  "characters": ["Заяц", "Волк", "Лиса"]
}
```
**Параметры:**
- `age` (int, required): Возраст ребенка (должен быть > 0)
- `language` (string, required): Язык сказки ("ru" или "kk")
- `characters` (array, required): Список персонажей (минимум 1 элемент)

**Ответ:**
Потоковый ответ в формате Markdown:
```markdown
# Сказка для 6-летнего ребёнка
**Язык:** русский
**Персонажи:** Заяц, Волк, Лиса

Жил-был Заяц в большом лесу...

---
_Сказка сгенерирована: 22.10.2025 в 14:30_
```

#### GET /healthz

Проверка состояния сервиса.

**Ответ:**
```json
{
  "status": "ok"
}
```