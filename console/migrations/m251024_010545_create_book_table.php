<?php

use yii\db\Migration;

class m251024_010545_create_book_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%book}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'year' => $this->integer()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(20),
            'cover_url' => $this->string(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->batchInsert('{{%book}}', ['title','year','description','isbn','cover_url'], [
            ['Преступление и наказание', 1866, 'Роман Ф.М. Достоевского', '978-5-17-118366-3', 'cover1.jpg'],
            ['Война и мир', 1869, 'Роман Л.Н. Толстого', '978-5-17-112366-3', 'cover2.jpg'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%book}}');
    }
}