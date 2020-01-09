<?php

use yii\db\Migration;

/**
 * Handles adding email to table `{{%petition}}`.
 */
class m190813_074611_add_email_column_to_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%petition}}', 'email_id', $this->integer()->comment('Email заявителя'));

        $this->addForeignKey(
            'fk-petition-email',
            '{{%petition}}',
            'email_id',
            'resident_email',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%petition}}', 'email_id');
    }
}
