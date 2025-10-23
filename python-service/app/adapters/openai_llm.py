from typing import AsyncIterator, Iterator
from openai import OpenAI
from starlette.concurrency import iterate_in_threadpool
from app.ports.llm import LLM

class OpenAILLM(LLM):
    def __init__(self, model: str, api_key: str):
        self.client = OpenAI(api_key=api_key)
        self.model = model

    def _sync_event_chunks(self, prompt: str) -> Iterator[str]:
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

        resp = self.client.chat.completions.create(
            model=self.model,
            messages=[{"role": "user", "content": prompt}],
            temperature=0.8,
            stream=True,
        )
        for chunk in resp:
            try:
                piece = chunk.choices[0].delta.content or ""
            except Exception:
                piece = ""
            if piece:
                yield piece

    async def stream_story(self, prompt: str) -> AsyncIterator[str]:
        async for chunk in iterate_in_threadpool(self._sync_event_chunks(prompt)):
            yield chunk
