@extends('layouts.app')

@section('title', 'Генератор сказок')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/story.css') }}">
@endpush

@section('content')
    <div style="max-width:800px;margin:0 auto;padding:24px">
        <h1>Генератор сказок</h1>

        <form id="story-form">
            @csrf

            <div class="form-group mb-3">
                <label for="age" class="form-label">Возраст</label>
                <input type="number" class="form-control" id="age" name="age"
                       value="{{ $age }}" min="1" required>
            </div>

            <div class="form-group mb-3">
                <label for="language-select" class="form-label">Язык</label>
                <select class="form-select" id="language-select" name="language">
                    <option value="ru" {{ $language === 'ru' ? 'selected' : '' }}>Русский</option>
                    <option value="kk" {{ $language === 'kk' ? 'selected' : '' }}>Қазақша</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="genre-select" class="form-label">Жанры</label>
                <select class="form-select" id="genre-select" name="genre">
                    <option value="adventure" {{ $genre === 'adventure' ? 'selected' : '' }}>Приключения</option>
                    <option value="fantasy" {{ $genre === 'fantasy' ? 'selected' : '' }}>Фэнтези</option>
                    <option value="fairy_tale" {{ $genre === 'fairy_tale' ? 'selected' : '' }}>Волшебная сказка</option>
                    <option value="comedy" {{ $genre === 'comedy' ? 'selected' : '' }}>Комедия</option>
                    <option value="drama" {{ $genre === 'drama' ? 'selected' : '' }}>Драма</option>
                    <option value="animal_tale" {{ $genre === 'animal_tale' ? 'selected' : '' }}>Сказка о животных</option>
                    <option value="family_tale" {{ $genre === 'family_tale' ? 'selected' : '' }}>Семейная сказка</option>
                    <option value="educational_tale" {{ $genre === 'educational_tale' ? 'selected' : '' }}>Поучительная сказка</option>
                    <option value="detective" {{ $genre === 'detective' ? 'selected' : '' }}>Детектив</option>
                    <option value="travel" {{ $genre === 'travel' ? 'selected' : '' }}>Путешествие</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Персонажи</label>
                <div id="characters-container" class="characters-grid"></div>
                <div class="form-text">Выберите одного или нескольких персонажей</div>
            </div>

            <div class="form-group mb-3">
                <button type="submit" class="btn btn-primary" id="go">Сгенерировать</button>
                <div id="status" style="margin-left:10px;display:none;">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    <span id="status-text">Подготовка к генерации...</span>
                </div>
            </div>
        </form>

        <div id="out" style="border:1px solid #ddd;padding:20px;border-radius:8px;min-height:160px;background:#f9f9f9;font-family:Georgia,serif;line-height:1.6"></div>
        <div id="err" style="color:#b00020;margin-top:8px"></div>
    </div>
@endsection

@push('scripts')
    <script>
        window.storyConfig = {
            streamUrl: '{{ route("story.generate") }}',
            charactersUrl: '{{ route("story.characters") }}',
            csrf: '{{ csrf_token() }}'
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>

    <script src="{{ asset('js/story.js') }}"></script>
@endpush
