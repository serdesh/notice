<?php

use yii\db\Migration;

/**
 * Handles adding enabled to table `{{%company}}`.
 */
class m190505_132031_add_enabled_column_to_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'enabled', $this->tinyInteger()->defaultValue(1));
        $this->addColumn('company', 'notes', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'enabled');
        $this->dropColumn('company', 'notes');
    }
}
