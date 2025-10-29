<?php

namespace backend\models;

use yii\db\ActiveRecord;

class BookAuthor extends ActiveRecord
{
    public static function tableName()
    {
        return 'book_author';
    }

    public function rules()
    {
        return [
            [['book_id','author_id'], 'required'],
            [['book_id','author_id'], 'integer'],
        ];
    }
}
