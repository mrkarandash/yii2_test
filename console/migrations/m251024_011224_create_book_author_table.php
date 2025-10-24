<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%book_author}}`.
 */
class m251024_011224_create_book_author_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%book_author}}', [
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk-book_author', '{{%book_author}}', ['book_id','author_id']);
        $this->addForeignKey('fk-book_author-book', '{{%book_author}}', 'book_id', '{{%book}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-book_author-author', '{{%book_author}}', 'author_id', '{{%author}}', 'id', 'CASCADE', 'CASCADE');

        $this->batchInsert('{{%book_author}}', ['book_id','author_id'], [
            [1,1],
            [2,2],
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-book_author-book', '{{%book_author}}');
        $this->dropForeignKey('fk-book_author-author', '{{%book_author}}');
        $this->dropTable('{{%book_author}}');
    }
}
