from dataclasses import dataclass
from typing import Literal

LanguageCode = Literal["ru", "kk"]

@dataclass(frozen=True)
class Age:
    value: int
    def __post_init__(self):
        if self.value <= 0:
            raise ValueError("age must be > 0")

@dataclass(frozen=True)
class Language:
    code: LanguageCode
    def __post_init__(self):
        if self.code not in ("ru", "kk"):
            raise ValueError("language must be 'ru' or 'kk'")

LANG_LABELS = {
    "ru": {
        "lang": "русский",
        "lang_field": "**Язык:** русский",
        "title": lambda age: f"# Сказка для {age}-летнего ребёнка",
        "chars": "**Персонажи:**",
    },
    "kk": {
        "lang": "қазақша",
        "lang_field": "**Тіл:** қазақша",
        "title": lambda age: f"# {age} жасар балаға арналған ертегі",
        "chars": "**Кейіпкерлер:**",
    },
}
