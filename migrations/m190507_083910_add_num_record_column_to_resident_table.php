<?php

use yii\db\Migration;

/**
 * Handles adding num_record to table `{{%resident}}`.
 */
class m190507_083910_add_num_record_column_to_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('resident', 'num_record', $this->string()
            ->comment('Номер записи лицевого счета (используется только при импорте)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190507_083910_add_num_record_column_to_resident_table cannot be reverted.\n";

        return false;
    }
}
