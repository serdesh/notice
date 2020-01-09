<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%complaint}}`.
 */
class m190417_055259_create_complaint_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%complaint}}', [
            'id' => $this->primaryKey(),
            'text' => $this->text()->comment('Сожержание жалобы'),
            'petition_id' => $this->integer(),
            'resident_id' => $this->integer(),
            'status_id' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%complaint}}');
    }
}
