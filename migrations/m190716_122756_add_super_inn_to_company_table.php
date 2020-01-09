<?php

use yii\db\Migration;

/**
 * Class m190716_122756_add_super_inn_to_company_table
 */
class m190716_122756_add_super_inn_to_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%company}}', ['inn' => '0278195730'], ['id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        echo "m190716_122756_add_super_inn_to_company_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190716_122756_add_super_inn_to_company_table cannot be reverted.\n";

        return false;
    }
    */
}
