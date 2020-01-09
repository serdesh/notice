<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%type}}`.
 */
class m190419_062150_create_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%type}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(),
            'short_name' => $this->string(),
        ]);

        $this->renameColumn('street', 'type', 'type_id');
        $this->alterColumn('street', 'type_id', $this->integer()->comment('ID типа улицы'));
    }



    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%type}}');
    }
}
