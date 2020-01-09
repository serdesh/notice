<?php

use yii\db\Migration;

/**
 * Handles adding company_id to table `{{%call}}`.
 */
class m190711_075934_add_company_id_column_to_call_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%call}}', 'company_id', $this->integer());
        $this->addForeignKey(
            'fk-call-company_id',
            'call',
            'company_id',
            'company',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190711_075934_add_company_id_column_to_call_table cannot be reverted.\n";

        return false;
    }
}
