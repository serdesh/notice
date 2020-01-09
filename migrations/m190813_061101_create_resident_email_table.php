<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%resident_email}}`.
 */
class m190813_061101_create_resident_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%resident_email}}', [
            'id' => $this->primaryKey(),
            'resident_id' => $this->integer()->comment('Жилец/Заявитель'),
            'email' => $this->string(),
            'note' => $this->text(),
        ]);

        $this->addForeignKey(
            'fk-resident_email-resident_id',
            '{{%resident_email}}',
            'resident_id',
            '{{%resident}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%resident_email}}');
    }
}
