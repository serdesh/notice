<?php

use yii\db\Migration;

/**
 * Class m190805_064756_update_company_id_from_superadmin
 */
class m190805_064756_update_company_id_from_superadmin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('users', ['company_id' => 1], ['id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190805_064756_update_company_id_from_superadmin cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190805_064756_update_company_id_from_superadmin cannot be reverted.\n";

        return false;
    }
    */
}
