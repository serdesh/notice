<?php

use yii\db\Migration;

/**
 * Class m190417_070507_add_relations_for_phone_table
 */
class m190417_070507_add_relations_for_phone_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-phone-contact_id',
            'phone',
            'contact_id',
            'contact',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_070507_add_relations_for_phone_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_070507_add_relations_for_phone_table cannot be reverted.\n";

        return false;
    }
    */
}
