<?php

use yii\db\Migration;

/**
 * Handles adding type to table `{{%message}}`.
 */
class m190612_095453_add_is_incoming_column_to_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('message', 'is_incoming', $this->smallInteger(1)->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('message', 'is_incoming');
    }
}
