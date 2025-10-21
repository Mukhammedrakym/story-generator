from datetime import datetime, timezone
from typing import AsyncIterator, List
from app.domain.value_objects import Age, Language, LANG_LABELS
from app.ports.llm import LLM
from app.core.errors import UpstreamError

def _now_iso() -> str:
    return datetime.now(timezone.utc).replace(microsecond=0).isoformat()

async def generate_story_stream(
    llm: LLM,
    age: Age,
    language: Language,
    characters: List[str],
) -> AsyncIterator[str]:
    labels = LANG_LABELS[language.code]
    chars = ", ".join([c.strip() for c in characters if c.strip()])

    header = (
        f"{labels['title'](age.value)}\n"
        f"{labels['lang_field']}\n"
        f"{labels['chars']} {chars}\n\n"
    )
    prompt = (
        f"Напиши сказку на {labels['lang']} языке для ребёнка {age.value} лет, "
        f"с персонажами: {chars}. Начинай сразу с текста сказки. "
        "6–12 абзацев, добрый и понятный слог. Возвращай чистый Markdown, без списков."
    )
    footer = f"\n\n---\n_Сказка сгенерирована: {_now_iso()}_\n"

    # отдаём шапку сразу
    yield header
    try:
        async for chunk in llm.stream_story(prompt):
            yield chunk
    except Exception as e:
        # маппим любые сбои провайдера в единый апстрим-эксепшн
        raise UpstreamError(str(e))
    finally:
        yield footer
