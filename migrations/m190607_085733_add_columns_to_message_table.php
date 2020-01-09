<?php

use yii\db\Migration;

/**
 * Handles adding attachments to table `{{%message}}`.
 */
class m190607_085733_add_columns_to_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('message', 'attachments', $this->string()->comment('Путь к файлам вложений'));
        $this->addColumn('message', 'outer_id', $this->string()->comment('ID письма на почтовом сервере'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('message', 'attachments');
        $this->dropColumn('message', 'outer_id');
    }
}
