<?php

use yii\db\Migration;

/**
 * Handles adding trouble_description to table `{{%petition}}`.
 */
class m190513_063757_add_trouble_description_column_to_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('petition', 'trouble_description', $this->text()->comment('Описание неисправности'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190513_063757_add_trouble_description_column_to_petition_table cannot be reverted.\n";

        return false;
    }
}
