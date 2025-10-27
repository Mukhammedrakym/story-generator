<?php
namespace app\modules\story\models;

use yii\base\Model;

class StoryForm extends Model
{
    public $age;
    public $language;
    public $genre;
    public $characters = [];

    public function rules()
    {
        return [
            [['age','language', 'genre'], 'required'],
            ['age','integer','min'=>1],
            ['language','in','range'=>['ru','kk']],
            ['characters','each','rule'=>['string']],
            ['characters', 'validateCharacters'],
        ];
    }

    public function validateCharacters($attribute, $params)
    {
        if (!is_array($this->$attribute) || count(array_filter($this->$attribute)) < 1) {
            $this->addError($attribute, 'Выберите хотя бы одного персонажа.');
        }
    }
    public static function availableCharacters($language = 'ru')
    {
        $characters = [
            'ru' => [
                'Заяц' => 'Заяц',
                'Волк' => 'Волк',
                'Лиса' => 'Лиса',
                'Медведь' => 'Медведь',
                'Принц' => 'Принц',
                'Принцесса' => 'Принцесса',
            ],
            'kk' => [
                'Қоян' => 'Қоян',
                'Қасқыр' => 'Қасқыр',
                'Түлкі' => 'Түлкі',
                'Аю' => 'Аю',
                'Алдар Көсе' => 'Алдар Көсе',
                'Арыстан' => 'Арыстан'
            ]
        ];

        return $characters[$language] ?? $characters['ru'];
    }
}