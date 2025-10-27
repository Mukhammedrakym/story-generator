from dataclasses import dataclass
from typing import Literal

LanguageCode = Literal["ru", "kk"]
GenreCode = Literal[
    "adventure",
    "fantasy",
    "fairy_tale",
    "comedy",
    "drama",
    "animal_tale",
    "family_tale",
    "educational_tale",
    "detective",
    "travel"
]

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

@dataclass(frozen=True)
class Genre:
    code: GenreCode
    
    def __post_init__(self):
        valid_genres = (
            "adventure", "fantasy", "fairy_tale", "comedy", "drama",
            "animal_tale", "family_tale", "educational_tale", "detective", "travel"
        )
        if self.code not in valid_genres:
            raise ValueError(f"genre must be one of: {valid_genres}")

GENRE_LABELS = {
    "adventure": {"ru": "Приключения", "kk": "Сəуегерлік"},
    "fantasy": {"ru": "Фэнтези", "kk": "Фэнтези"},
    "fairy_tale": {"ru": "Волшебная сказка", "kk": "Сиқырлы ертегі"},
    "comedy": {"ru": "Комедия", "kk": "Комедия"},
    "drama": {"ru": "Драма", "kk": "Драма"},
    "animal_tale": {"ru": "Сказка о животных", "kk": "Жануарлар туралы ертегі"},
    "family_tale": {"ru": "Семейная сказка", "kk": "Отбасылық ертегі"},
    "educational_tale": {"ru": "Поучительная сказка", "kk": "Құнды мансиз ертегі"},
    "detective": {"ru": "Детектив", "kk": "Детектив"},
    "travel": {"ru": "Путешествие", "kk": "Саяхат"},
}

LANG_LABELS = {
    "ru": {
        "lang": "русский",
        "lang_field": "**Язык:** русский",
        "title": lambda age: f"# Сказка для {age}-летнего ребёнка",
        "chars": "**Персонажи:**",
        "genre": "**Жанр:**",
    },
    "kk": {
        "lang": "қазақша",
        "lang_field": "**Тіл:** қазақша",
        "title": lambda age: f"# {age} жасар балаға арналған ертегі",
        "chars": "**Кейіпкерлер:**",
        "genre": "**Жанр:**",
    },
}
