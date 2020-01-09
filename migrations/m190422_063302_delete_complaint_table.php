<?php

use yii\db\Migration;

/**
 * Class m190422_063302_delete_complain_table
 */
class m190422_063302_delete_complaint_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('complaint');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190422_063302_delete_complain_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190422_063302_delete_complain_table cannot be reverted.\n";

        return false;
    }
    */
}
