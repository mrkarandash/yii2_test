<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use League\Flysystem\Filesystem;

class Book extends ActiveRecord
{
    public $coverFile;       // Для формы загрузки обложки
    public $authorsArray = []; // Для множественных авторов

    public static function tableName()
    {
        return 'book';
    }

    public function rules()
    {
        return [
            [['title', 'year'], 'required'],
            [['year'], 'integer'],
            [['description'], 'string'],
            [['title','isbn','cover_url'], 'string', 'max' => 255],
            [['authorsArray'], 'safe'],
            [['coverFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'cover_url' => 'Фото',
            'authorsArray' => 'Авторы',
        ];
    }

    // Связь многие-ко-многим через промежуточную таблицу book_author
    public function getBookAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['book_id'=>'id']);
    }

    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id'=>'author_id'])->via('bookAuthors');
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->authorsArray = $this->getAuthors()->select('id')->column();
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function() { return date('Y-m-d H:i:s'); }, // DATETIME формат
            ],
        ];
    }
    public function uploadCover(): bool
    {
        if (!$this->coverFile) {
            // Если файл не выбран — ничего не делаем
            return false;
        }

        $fileName = uniqid() . '.' . $this->coverFile->extension;

        $s3 = Yii::$app->s3;

        $s3->write(
            $fileName,
            file_get_contents($this->coverFile->tempName),
            ['visibility' => 'public']
        );

        // Сохраняем только имя файла, не полный URL
        $this->cover_url = 'http://localhost:9001/books/' . $fileName;

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Сохраняем связь многие-ко-многим
        BookAuthor::deleteAll(['book_id' => $this->id]);

        if (is_array($this->authorsArray)) {
            foreach ($this->authorsArray as $authorId) {
                $ba = new BookAuthor();
                $ba->book_id = $this->id;
                $ba->author_id = $authorId;
                $ba->save();
            }
        }
    }
}
