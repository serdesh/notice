<?php

use yii\db\Migration;

/**
 * Handles adding trouble_id to table `{{%petition}}`.
 */
class m190513_062645_add_trouble_id_column_to_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('petition', 'trouble_id', $this->integer());

        $this->addForeignKey(
            'fk-petition-trouble_id',
            'petition',
            'trouble_id',
            'troubleshooting_period',
            'id',
            'SET NULL'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190513_062645_add_trouble_id_column_to_petition_table cannot be reverted.\n";

        return false;
    }
}
