<?php
namespace app\modules\story\models;

use yii\base\Model;

class StoryForm extends Model
{
    public $age;
    public $language;
    public $characters = [];

    public function rules()
    {
        return [
            [['age','language'], 'required'],
            ['age','integer','min'=>1],
            ['language','in','range'=>['ru','kk']],
            ['characters','each','rule'=>['string']],
            ['characters', function($attr){ if (!is_array($this->$attr) || count(array_filter($this->$attr))<1)
                $this->addError($attr,'Выберите хотя бы одного персонажа.'); }],
        ];
    }

    public static function availableCharacters()
    {
        return ['Заяц'=>'Заяц','Волк'=>'Волк','Лиса'=>'Лиса','Алдар Көсе'=>'Алдар Көсе','Әйел Арстан'=>'Әйел Арстан'];
    }
}

