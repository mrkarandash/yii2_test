<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%author}}`.
 */
class m251024_011213_create_author_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%author}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->batchInsert('{{%author}}', ['full_name'], [
            ['Фёдор Достоевский'],
            ['Лев Толстой'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%author}}');
    }
}
