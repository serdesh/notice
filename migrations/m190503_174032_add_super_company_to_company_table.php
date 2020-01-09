<?php

use yii\db\Migration;

/**
 * Class m190503_174032_add_super_company_to_company_table
 */
class m190503_174032_add_super_company_to_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->dropForeignKey('fk-company-contact_id', 'company');

        $this->addForeignKey(
            'fk-company-contact_id',
            'company',
            'contact_id',
            'contact',
            'id',
            'CASCADE'
            );

        $this->insert('company', [
            'name' => 'Супер компания',
            'director' => 'Директор суперкомпании',
            'password' => md5('admin')
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190503_174032_add_super_company_to_company_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190503_174032_add_super_company_to_company_table cannot be reverted.\n";

        return false;
    }
    */
}
