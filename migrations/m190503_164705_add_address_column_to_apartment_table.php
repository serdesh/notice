<?php

use yii\db\Migration;

/**
 * Handles adding address to table `{{%apartment}}`.
 */
class m190503_164705_add_address_column_to_apartment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%apartment}}', 'address', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190503_164705_add_address_column_to_apartment_table cannot be reverted.\n";

        return false;
    }
}
