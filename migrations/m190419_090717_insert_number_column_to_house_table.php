<?php

use yii\db\Migration;

/**
 * Class m190419_090717_insert_number_column_to_house_table
 */
class m190419_090717_insert_number_column_to_house_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%house}}', 'number', $this->string()->comment('Номер'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190419_090717_insert_number_column_to_house_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190419_090717_insert_number_column_to_house_table cannot be reverted.\n";

        return false;
    }
    */
}
