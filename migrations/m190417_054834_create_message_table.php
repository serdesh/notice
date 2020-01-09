<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%message}}`.
 */
class m190417_054834_create_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
            'header' => $this->string()->comment('Заголовок'),
            'text' => $this->text()->comment('Содержание'),
            'petition_id' => $this->integer()->comment('ID Заявки'),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%message}}');
    }
}
