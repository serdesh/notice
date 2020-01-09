<?php

use yii\db\Migration;

/**
 * Class m190417_074108_add_relations_for_complaint_table
 */
class m190417_074108_add_relations_for_complaint_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-complaint-petition_id',
            'complaint',
            'petition_id',
            'petition',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-complaint-resident_id',
            'complaint',
            'resident_id',
            'resident',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'fk-complaint-status_id',
            'complaint',
            'status_id',
            'status',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_074108_add_relations_for_complaint_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_074108_add_relations_for_complaint_table cannot be reverted.\n";

        return false;
    }
    */
}
