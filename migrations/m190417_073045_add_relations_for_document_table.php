<?php

use yii\db\Migration;

/**
 * Class m190417_073045_add_relations_for_document_table
 */
class m190417_073045_add_relations_for_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-document-created_by',
            'document',
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
        echo "m190417_073045_add_relations_for_document_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_073045_add_relations_for_document_table cannot be reverted.\n";

        return false;
    }
    */
}
