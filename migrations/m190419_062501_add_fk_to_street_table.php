<?php

use yii\db\Migration;

/**
 * Class m190419_062501_add_fk_to_street_table
 */
class m190419_062501_add_fk_to_street_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-street-type_id',
            'street',
            'type_id',
            'type',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190419_062501_add_fk_to_street_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190419_062501_add_fk_to_street_table cannot be reverted.\n";

        return false;
    }
    */
}
