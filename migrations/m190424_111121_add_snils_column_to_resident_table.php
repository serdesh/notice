<?php

use yii\db\Migration;

/**
 * Handles adding snils to table `{{%resident}}`.
 */
class m190424_111121_add_snils_column_to_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('resident', 'snils', $this->string()->comment('СНИЛС'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
