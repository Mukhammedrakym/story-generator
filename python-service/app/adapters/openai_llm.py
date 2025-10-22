from typing import AsyncIterator, Iterator
from openai import OpenAI
from starlette.concurrency import iterate_in_threadpool
from app.ports.llm import LLM

class OpenAILLM(LLM):
    """
    Адаптер под OpenAI.
    1) Пытаемся использовать Responses API (client.responses.stream)
    2) Если его нет (старый/неполный SDK) — fallback на Chat Completions (stream=True)
    """

    def __init__(self, model: str, api_key: str):
        self.client = OpenAI(api_key=api_key)
        self.model = model

    # ---- синхронный генератор, который мы обернём в async через iterate_in_threadpool
    def _sync_event_chunks(self, prompt: str) -> Iterator[str]:
        # Попытка №1: Responses API (новый путь)
        if hasattr(self.client, "responses"):
            with self.client.responses.stream(
                model=self.model,
                input=[{"role": "user", "content": prompt}],
                temperature=0.8,
                max_output_tokens=1200,
            ) as stream:
                for event in stream:
                    if event.type == "response.output_text.delta":
                        if event.delta:
                            yield event.delta
                    elif event.type == "response.error":
                        msg = getattr(getattr(event, "error", None), "message", "OpenAI stream error")
                        raise RuntimeError(msg)
                _ = stream.get_final_response()
            return

        # Попытка №2: Chat Completions (совместимо со старыми SDK)
        resp = self.client.chat.completions.create(
            model=self.model,
            messages=[{"role": "user", "content": prompt}],
            temperature=0.8,
            stream=True,
        )
        for chunk in resp:
            # chunk.choices[0].delta.content может быть None
            try:
                piece = chunk.choices[0].delta.content or ""
            except Exception:
                piece = ""
            if piece:
                yield piece

    async def stream_story(self, prompt: str) -> AsyncIterator[str]:
        async for chunk in iterate_in_threadpool(self._sync_event_chunks(prompt)):
            yield chunk
