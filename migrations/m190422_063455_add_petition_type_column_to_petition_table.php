<?php

use yii\db\Migration;

/**
 * Handles adding petition_type to table `{{%petition}}`.
 */
class m190422_063455_add_petition_type_column_to_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petition}}', 'petition_type', $this->tinyInteger()
            ->defaultValue(0)
            ->comment('Тип обращения'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190422_063455_add_petition_type_column_to_petition_table cannot be reverted.\n";

        return false;
    }
}
