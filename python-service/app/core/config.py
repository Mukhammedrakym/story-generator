from functools import lru_cache
from typing import List, Optional, Literal
from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict

class Settings(BaseSettings):
    LLM_PROVIDER: Literal["openai", "gemini"] = "gemini"

    # OpenAI
    OPENAI_API_KEY: Optional[str] = Field(default=None)
    OPENAI_MODEL: str = Field("gpt-4o-mini")

    # Gemini - используем правильные названия моделей из документации
    GEMINI_API_KEY: Optional[str] = Field(default=None)
    GEMINI_MODEL: str = Field("gemini-2.5-flash")  # Согласно документации

    CORS_ORIGINS: List[str] = ["*"]
    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8")

@lru_cache()
def get_settings() -> Settings:
    return Settings()