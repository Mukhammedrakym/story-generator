from typing import AsyncIterator, Protocol

class LLM(Protocol):
    async def stream_story(self, prompt: str) -> AsyncIterator[str]:
        """Возвращает текст сказки чанками."""
        ...
