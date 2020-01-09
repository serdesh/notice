<?php

use yii\db\Migration;

/**
 * Class m190504_152523_add_relation_for_resident_table
 */
class m190504_152523_add_relation_for_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-resident-apartment_id',
            'resident',
            'apartment_id',
            'apartment',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190504_152523_add_relation_for_resident_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190504_152523_add_relation_for_resident_table cannot be reverted.\n";

        return false;
    }
    */
}
