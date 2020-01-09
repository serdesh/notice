<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%history}}`.
 */
class m190720_124910_create_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%history}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'petition_id' => $this->integer()->comment('ID обращения, к которому относится событие'),
            'name' => $this->string()->comment('Наименование события'),
            'description' => $this->text()->comment('Описание события'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%history}}');
    }
}
