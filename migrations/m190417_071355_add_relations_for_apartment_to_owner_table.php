<?php

use yii\db\Migration;

/**
 * Class m190417_071355_add_relations_for_apartament_to_owner_table
 */
class m190417_071355_add_relations_for_apartment_to_owner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-apartment_to_owner-apartment_id',
            'apartment_to_owner',
            'apartment_id',
            'apartment',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-apartment_to_owner-owner_id',
            'apartment_to_owner',
            'owner_id',
            'resident',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_071355_add_relations_for_apartament_to_owner_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_071355_add_relations_for_apartament_to_owner_table cannot be reverted.\n";

        return false;
    }
    */
}
