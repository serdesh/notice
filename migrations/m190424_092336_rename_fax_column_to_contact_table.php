<?php

use yii\db\Migration;

/**
 * Class m190424_092336_rename_fax_column_to_contact_table
 */
class m190424_092336_rename_fax_column_to_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('contact', 'fax', 'phone');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190424_092336_rename_fax_column_to_contact_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190424_092336_rename_fax_column_to_contact_table cannot be reverted.\n";

        return false;
    }
    */
}
