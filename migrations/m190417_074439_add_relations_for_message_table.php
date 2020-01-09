<?php

use yii\db\Migration;

/**
 * Class m190417_074439_add_relations_for_message_table
 */
class m190417_074439_add_relations_for_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-message-petition_id',
            'message',
            'petition_id',
            'petition',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_074439_add_relations_for_message_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_074439_add_relations_for_message_table cannot be reverted.\n";

        return false;
    }
    */
}
