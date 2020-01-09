<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m190416_125254_expand_users_table
 */
class m190416_125254_expand_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'company_id', $this->integer());
        $this->addColumn('users', 'contact_id', $this->integer());
        $this->addColumn('users', 'inn', $this->string());
        $this->addColumn('users', 'created_at', $this->timestamp()->defaultValue(new Expression('NOW()')));
        $this->addColumn('users', 'created_by', $this->integer());
        $this->addColumn('users', 'snils', $this->string()->comment('СНИЛС'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190416_125254_expand_users_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190416_125254_expand_users_table cannot be reverted.\n";

        return false;
    }
    */
}
