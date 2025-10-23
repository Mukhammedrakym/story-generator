from typing import AsyncIterator
from google import genai
from app.ports.llm import LLM
import asyncio
from starlette.concurrency import iterate_in_threadpool

class GeminiLLM(LLM):
    def __init__(self, model: str, api_key: str):
        self._client = genai.Client(api_key=api_key)
        self.model = model

    def _sync_stream_chunks(self, prompt: str):
        try:
            # Создаем чат-сессию
            chat = self._client.chats.create(
                model=self.model,
                history=[]
            )

            stream = chat.send_message_stream(prompt)
            for chunk in stream:
                if hasattr(chunk, 'text') and chunk.text:
                    yield chunk.text

        except Exception as e:
            yield f"Ошибка при генерации сказки: {str(e)}"

    async def stream_story(self, prompt: str) -> AsyncIterator[str]:
        async for chunk in iterate_in_threadpool(self._sync_stream_chunks(prompt)):
            yield chunk