<?php

use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 */
class m180601_154502_create_users_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'fio' => $this->string(255)->comment('ФИО'),
            'permission' => $this->string(255)->comment('Должность'),
            'login' => $this->string(255)->unique()->comment('Логин'),
            'password' => $this->string(255)->comment('Пароль'),
        ]);

        $this->insert('users',array(
            'fio' => 'Иванов Иван Иванович',
            'permission' => 'super_administrator',
            'login' => 'admin',
            'password' => md5('admin'),
        ));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('users');
    }
}
