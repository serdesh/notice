<?php

use yii\db\Migration;

/**
 * Class m190417_071014_add_relations_for_resident_table
 */
class m190417_071014_add_relations_for_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-resident-contact_id',
            'resident',
            'contact_id',
            'contact',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_071014_add_relations_for_resident_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_071014_add_relations_for_resident_table cannot be reverted.\n";

        return false;
    }
    */
}
