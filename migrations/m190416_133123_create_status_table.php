<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%petition_status}}`.
 */
class m190416_133123_create_status_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%status}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()
        ]);

        $this->batchInsert('{{%status}}', ['name'], [
            ['Новое'], ['В работе'], ['Решено'], ['Отменено'], ['Архивировано'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%petition_status}}');
    }
}
