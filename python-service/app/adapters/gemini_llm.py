from typing import AsyncIterator
from google import genai
from app.ports.llm import LLM
import asyncio
from starlette.concurrency import iterate_in_threadpool

class GeminiLLM(LLM):
    """Адаптер под новый Google GenAI SDK с потоковой передачей."""

    def __init__(self, model: str, api_key: str):
        self._client = genai.Client(api_key=api_key)
        self.model = model

    def _sync_stream_chunks(self, prompt: str):
        """Синхронная функция для получения потока чанков."""
        try:
            # Создаем чат-сессию
            chat = self._client.chats.create(
                model=self.model,
                history=[]
            )

            # Используем sendMessageStream для потоковой передачи
            # Это возвращает обычный генератор, не async
            stream = chat.send_message_stream(prompt)

            # Итерируемся по потоку ответа
            for chunk in stream:
                if hasattr(chunk, 'text') and chunk.text:
                    yield chunk.text

        except Exception as e:
            yield f"Ошибка при генерации сказки: {str(e)}"

    async def stream_story(self, prompt: str) -> AsyncIterator[str]:
        """Асинхронная обертка для синхронного потока."""
        async for chunk in iterate_in_threadpool(self._sync_stream_chunks(prompt)):
            yield chunk