<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%street}}`.
 */
class m190417_053702_create_street_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%street}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->comment('Тип. ул., пер., проезд, площадь и пр.'),
            'name' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%street}}');
    }
}
