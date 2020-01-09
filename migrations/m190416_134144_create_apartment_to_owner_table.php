<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apartment_to_owner}}`.
 */
class m190416_134144_create_apartment_to_owner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%apartment_to_owner}}', [
            'id' => $this->primaryKey(),
            'apartment_id' => $this->integer(),
            'owner_id' => $this->integer()->comment('ID владельца помещения/квартиры')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apartment_to_owner}}');
    }
}
