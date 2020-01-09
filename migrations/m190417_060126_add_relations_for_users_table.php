<?php

use yii\db\Migration;

/**
 * Class m190417_060126_add_relations_for_tables
 */
class m190417_060126_add_relations_for_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-users-company_id',
            'users',
            'company_id',
            'company',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-users-contact_id',
            'users',
            'contact_id',
            'contact',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-users-created_by',
            'users',
            'created_by',
            'users',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_060126_add_relations_for_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_060126_add_relations_for_tables cannot be reverted.\n";

        return false;
    }
    */
}
