<?php

use yii\db\Migration;

/**
 * Class m190717_075735_change_login_column_to_users_table
 */
class m190717_075735_change_login_column_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('login', '{{%users}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m190717_075735_change_login_column_to_users_table cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190717_075735_change_login_column_to_users_table cannot be reverted.\n";

        return false;
    }
    */
}
