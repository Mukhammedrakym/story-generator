from typing import List, Literal, AsyncIterator
from fastapi import APIRouter, Depends, HTTPException
from fastapi.responses import StreamingResponse
from pydantic import BaseModel, Field, validator

from app.core.di import get_llm
from app.ports.llm import LLM
from app.domain.value_objects import Age, Language
from app.usecases.generate_story import generate_story_stream

router = APIRouter()

class StoryDTO(BaseModel):
    age: int = Field(..., gt=0)
    language: Literal["ru", "kk"]
    characters: List[str] = Field(..., min_items=1)

    @validator("characters", each_item=True)
    def non_empty(cls, v: str):
        if not v or not v.strip():
            raise ValueError("Character must be non-empty")
        return v.strip()

@router.post("/generate_story")
def generate_story(dto: StoryDTO, llm: LLM = Depends(get_llm)):
    try:
        age = Age(dto.age)
        lang = Language(dto.language)
    except ValueError as e:
        raise HTTPException(status_code=422, detail=str(e))

    async def gen() -> AsyncIterator[bytes]:
        async for chunk in generate_story_stream(llm, age, lang, dto.characters):
            yield chunk.encode("utf-8")

    return StreamingResponse(gen(), media_type="text/markdown; charset=utf-8")
