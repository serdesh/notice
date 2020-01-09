<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%email_settings}}`.
 */
class m190604_102432_create_email_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%email_settings}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'type' => $this->integer()->comment('0-IMAP, 1-POP'),
            'incoming_server' => $this->text()->comment('Сервер входящей почты'),
            'incoming_port' =>$this->integer()->comment('Порт для входящих сообщений'),
            'smtp_server' => $this->text()->comment('Сервер исходящей почты'),
            'smtp_port' => $this->text()->comment('Порт сервера исходящей почты'),
            'protection' => $this->integer()->comment('Защита соединения/Протокол шифрования'),
            'login' => $this->string()->comment('Логин от почтового ящика'),
            'password' => $this->string()->comment('Пароль от почтового ящика'),
            'checked' => $this->smallInteger(1)->defaultValue(0)->comment('Флаг проверки ящика на работоспособность')
        ]);

        $this->addForeignKey(
            'fk-email_settings-user_id',
            'email_settings',
            'user_id',
            'users',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%email_settings}}');
    }
}
