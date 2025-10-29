<?php

namespace backend\models;

use yii\db\ActiveRecord;

class Subscription extends ActiveRecord
{
    public static function tableName()
    {
        return 'subscription';
    }

    public function rules()
    {
        return [
            [['author_id','phone'],'required'],
            [['author_id'], 'integer'],
            [['phone'], 'string', 'max' => 20],
        ];
    }

    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id'=>'author_id']);
    }
}
