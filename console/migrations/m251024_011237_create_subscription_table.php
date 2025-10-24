<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscription}}`.
 */
class m251024_011237_create_subscription_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%subscription}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'phone' => $this->string(20)->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk-subscription-author', '{{%subscription}}', 'author_id', '{{%author}}', 'id', 'CASCADE', 'CASCADE');

        $this->batchInsert('{{%subscription}}', ['author_id','phone'], [
            [1, '+79990001122'],
            [2, '+79990003344'],
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-subscription-author', '{{%subscription}}');
        $this->dropTable('{{%subscription}}');
    }
}
