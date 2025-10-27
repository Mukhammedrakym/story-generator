<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoryRequest;
use App\Services\StoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoryController extends Controller
{
    private $storyService;
    public function __construct(StoryService $storyService) {
        $this->storyService = $storyService;
    }

    public function index(): View
    {
        return view('story.index', [
            'age' => 6,
            'language' => 'kk',
            'genre' => 'adventure',
        ]);
    }

public function characters(Request $request): \Illuminate\Http\JsonResponse
{
    $language = $request->get('language', 'kk');
    $characters = $this->getAvailableCharacters($language);

    return response()->json($characters);
}

    public function generate(StoryRequest $request)
    {
        $charactersList = $this->getAvailableCharacters($request->language);
        $selectedCharacters = [];
        
        foreach ($request->characters as $charKey) {
            if (isset($charactersList[$charKey])) {
                $selectedCharacters[] = $charactersList[$charKey];
            }
        }

        $payload = [
            'age' => (int) $request->age,
            'language' => $request->language,
            'genre' => $request->genre,
            'characters' => $selectedCharacters,
        ];

        return $this->storyService->streamToBrowser($payload);
    }

    private function getAvailableCharacters(string $language = 'ru'): array
    {
        $characters = [
            'ru' => [
                'zayac' => 'Заяц',
                'volk' => 'Волк',
                'lisa' => 'Лиса',
                'medved' => 'Медведь',
                'prince' => 'Принц',
                'princess' => 'Принцесса',
            ],
            'kk' => [
                'qoyan' => 'Қоян',
                'qasqyr' => 'Қасқыр',
                'tulki' => 'Түлкі',
                'ayu' => 'Аю',
                'aldar' => 'Алдар Көсе',
                'arystan' => 'Арыстан',
            ],
        ];

        return $characters[$language] ?? $characters['ru'];
    }

}
