<?php

use yii\db\Migration;

/**
 * Handles adding email to table `{{%users}}`.
 */
class m190424_082059_add_email_column_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'email', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190424_082059_add_email_column_to_users_table cannot be reverted.\n";

        return false;
    }
}
