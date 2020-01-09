<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%room}}`.
 */
class m190506_132848_create_room_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%room}}', [
            'id' => $this->primaryKey(),
            'apartment_id' => $this->integer(),
            'number' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk-room-apartment_id',
            'room',
            'apartment_id',
            'apartment',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%room}}');
    }
}
