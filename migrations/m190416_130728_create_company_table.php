<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company}}`.
 */
class m190416_130728_create_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'director' => $this->string(),
            'inn' => $this->string(50)->comment('ИНН'),
            'contact_id' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%company}}');
    }
}
