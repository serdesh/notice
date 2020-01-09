<?php

use yii\db\Migration;

/**
 * Handles adding address to table `{{%petition}}`.
 */
class m190422_065457_add_address_column_to_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('petition', 'address', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190422_065457_add_address_column_to_petition_table cannot be reverted.\n";

        return false;
    }
}
