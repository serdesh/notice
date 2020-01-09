<?php

use yii\db\Migration;

/**
 * Class m190710_183549_add_created_by_company_to_resident_table
 */
class m190710_183549_add_created_by_company_to_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('resident', 'created_by_company', $this->integer()->comment('Компания-создатель'));
        $this->addForeignKey(
            'fk-resident-created_by_company',
            'resident',
            'created_by_company',
            'company',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190710_183549_add_created_by_company_to_resident_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190710_183549_add_created_by_company_to_residentent_table cannot be reverted.\n";

        return false;
    }
    */
}
