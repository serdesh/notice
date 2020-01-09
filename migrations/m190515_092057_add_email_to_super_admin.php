<?php

use yii\db\Migration;

/**
 * Class m190515_092057_add_email_to_superadmin
 */
class m190515_092057_add_email_to_super_admin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('users', ['email' => 'superadmin@notice.ru'],['permission' => 'super_administrator']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190515_092057_add_email_to_superadmin cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190515_092057_add_email_to_superadmin cannot be reverted.\n";

        return false;
    }
    */
}
