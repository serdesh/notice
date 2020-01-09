<?php

use yii\db\Migration;

/**
 * Handles adding address to table `{{%house}}`.
 */
class m190503_154517_add_address_column_to_house_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('house', 'address', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190503_154517_add_address_column_to_house_table cannot be reverted.\n";

        return false;
    }
}
