<?php

use yii\db\Migration;

/**
 * Class m190422_093228_insert_data_to_status_table
 */
class m190422_093228_insert_data_to_status_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('status', ['name' => 'Просрочено']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190422_093228_insert_data_to_status_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190422_093228_insert_data_to_status_table cannot be reverted.\n";

        return false;
    }
    */
}
