from app.core.config import get_settings
from app.ports.llm import LLM
from app.adapters.openai_llm import OpenAILLM

def get_llm() -> LLM:
    s = get_settings()
    return OpenAILLM(model=s.OPENAI_MODEL, api_key=s.OPENAI_API_KEY)
