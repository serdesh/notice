<?php

use yii\db\Migration;

/**
 * Class m190417_072815_add_relations_for_house_table
 */
class m190417_072815_add_relations_for_house_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-house-street_id',
            'house',
            'street_id',
            'street',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-house-document_id',
            'house',
            'document_id',
            'document',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_072815_add_relations_for_house_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_072815_add_relations_for_house_table cannot be reverted.\n";

        return false;
    }
    */
}
