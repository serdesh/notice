<?php

use yii\db\Migration;

/**
 * Class m190417_065834_add_relations_for_company_table
 */
class m190417_065834_add_relations_for_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-company-contact_id',
            'company',
            'contact_id',
            'contact',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_065834_add_relations_for_company_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_065834_add_relations_for_company_table cannot be reverted.\n";

        return false;
    }
    */
}
