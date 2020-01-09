<?php

use yii\db\Migration;

/**
 * Handles adding is_residental to table `{{%apartment}}`.
 */
class m190503_114922_add_is_residential_column_to_apartment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%apartment}}', 'is_residential', $this->tinyInteger()
            ->defaultValue(1)
            ->comment('Жилое или не жилое помещение. (по умолчанию - жилое)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190503_114922_add_is_residential_column_to_apartment_table cannot be reverted.\n";

        return false;
    }
}
