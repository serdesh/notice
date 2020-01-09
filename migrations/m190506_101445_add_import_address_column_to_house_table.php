<?php

use yii\db\Migration;

/**
 * Handles adding import_address to table `{{%house}}`.
 */
class m190506_101445_add_import_address_column_to_house_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('house', 'import_address', $this->text()->comment('Адрес в формате из файла импорта'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190506_101445_add_import_address_column_to_house_table cannot be reverted.\n";

        return false;
    }
}
