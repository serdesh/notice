<?php

use yii\db\Migration;

/**
 * Handles adding room_number to table `{{%apartment}}`.
 */
class m190506_083945_add_room_number_column_to_apartment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('apartment', 'room_number', $this->string()->comment('Номер комнаты'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190506_083945_add_room_number_column_to_apartment_table cannot be reverted.\n";

        return false;
    }
}
