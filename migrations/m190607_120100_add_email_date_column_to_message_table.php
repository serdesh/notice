<?php

use yii\db\Migration;

/**
 * Handles adding mail_date to table `{{%message}}`.
 */
class m190607_120100_add_email_date_column_to_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('message', 'email_date', $this->timestamp()->defaultValue(null)->comment('Дата указанная в письме'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('message', 'email_date');
    }
}
