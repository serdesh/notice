<?php

use yii\db\Migration;

/**
 * Handles adding password to table `{{%company}}`.
 */
class m190424_090450_add_password_column_to_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'password', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190424_090450_add_password_column_to_company_table cannot be reverted.\n";

        return false;
    }
}
