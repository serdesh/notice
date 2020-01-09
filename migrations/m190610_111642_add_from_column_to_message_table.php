<?php

use yii\db\Migration;

/**
 * Handles adding from to table `{{%message}}`.
 */
class m190610_111642_add_from_column_to_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('message', 'from', $this->string()->comment('От кого пришло сообщение'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('message', 'from');
    }
}
