<?php

use yii\db\Migration;

/**
 * Class m190417_073209_add_relations_for_petition_table
 */
class m190417_073209_add_relations_for_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-petition-status_id',
            'petition',
            'status_id',
            'status',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-petition-specialist_id',
            'petition',
            'specialist_id',
            'users',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-petition-manager_id',
            'petition',
            'manager_id',
            'users',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-petition-created_by',
            'petition',
            'created_by',
            'users',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-petition-closed_user_id',
            'petition',
            'closed_user_id',
            'users',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-petition-resident_id',
            'petition',
            'resident_id',
            'resident',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-petition-relation_petition_id',
            'petition',
            'relation_petition_id',
            'petition',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_073209_add_relations_for_petition_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_073209_add_relations_for_petition_table cannot be reverted.\n";

        return false;
    }
    */
}
