<?php

use yii\db\Migration;

/**
 * Handles adding cadastral_number to table `{{%apartment}}`.
 */
class m190710_173728_add_cadastral_number_column_to_apartment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('apartment', 'cadastral_number', $this->string()->comment('Кадастровый номер'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190710_173728_add_cadastral_number_column_to_apartment_table cannot be reverted.\n";

        return false;
    }
}
