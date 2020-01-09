<?php

use yii\db\Migration;

/**
 * Handles adding room_id to table `{{%resident}}`.
 */
class m190507_052718_add_room_id_column_to_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('resident', 'room_id', $this->integer()->comment('ID Комнаты'));

        $this->addForeignKey(
            'fk-resident-room_id',
            'resident',
            'room_id',
            'room',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190507_052718_add_room_id_column_to_resident_table cannot be reverted.\n";

        return false;
    }
}
