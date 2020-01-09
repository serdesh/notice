<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%call}}`.
 */
class m190623_092442_create_call_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%call}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'phone_number' => $this->string(),
            'petition_id' => $this->integer(),
            'resident_id' => $this->integer()->comment('Владелец телефонного номера'),
        ]);

        $this->addForeignKey(
            'fk-call-petition_id',
            'call',
            'petition_id',
            'petition',
            'id',
            'CASCADE'
            );

        $this->addForeignKey(
            'fk-call-resident_id',
            'call',
            'resident_id',
            'resident',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%call}}');
    }
}
