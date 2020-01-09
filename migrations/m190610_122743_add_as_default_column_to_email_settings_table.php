<?php

use yii\db\Migration;

/**
 * Handles adding as_default to table `{{%message}}`.
 */
class m190610_122743_add_as_default_column_to_email_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('email_settings', 'as_default', $this->smallInteger(1)
            ->defaultValue(0)
            ->comment('Использовать по умолчанию'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('email_settings', 'as_default');
    }
}
