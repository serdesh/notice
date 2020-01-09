<?php

use yii\db\Migration;

/**
 * Class m190417_072103_add_relations_for_apartament_table
 */
class m190417_072103_add_relations_for_apartment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-apartment-house_id',
            'apartment',
            'house_id',
            'house',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_072103_add_relations_for_apartament_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_072103_add_relations_for_apartament_table cannot be reverted.\n";

        return false;
    }
    */
}
