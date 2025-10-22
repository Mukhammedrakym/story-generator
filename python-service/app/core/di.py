from app.core.config import get_settings
from app.adapters.gemini_llm import GeminiLLM
from app.adapters.openai_llm import OpenAILLM
from app.ports.llm import LLM

def get_llm() -> LLM:
    s = get_settings()

    if s.LLM_PROVIDER == "gemini":
        if not s.GEMINI_API_KEY:
            raise ValueError("GEMINI_API_KEY не настроен")
        return GeminiLLM(model=s.GEMINI_MODEL, api_key=s.GEMINI_API_KEY)

    elif s.LLM_PROVIDER == "openai":
        if not s.OPENAI_API_KEY:
            raise ValueError("OPENAI_API_KEY не настроен")
        return OpenAILLM(model=s.OPENAI_MODEL, api_key=s.OPENAI_API_KEY)

    else:
        raise ValueError(f"Неподдерживаемый провайдер: {s.LLM_PROVIDER}")