<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apartment}}`.
 */
class m190416_134352_create_apartment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%apartment}}', [
            'id' => $this->primaryKey(),
            'house_id' => $this->integer()->comment('ID дома'),
            'number' => $this->string()->comment('Номер'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apartment}}');
    }
}
