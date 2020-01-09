<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%house}}`.
 */
class m190417_052715_create_house_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%house}}', [
            'id' => $this->primaryKey(),
            'street_id' => $this->integer(),
            'fias_number' => $this->string()->comment('Номер из ФИАС'),
            'cadastral_number' => $this->string()->comment('Кадастровый номер'),
            'residential_number' => $this->integer(2)->comment('Кол-во жилых помещений'),
            'non_residential_number' => $this->integer(2) ->comment('Кол-во не жилых помещений'),
            'additional_info' => $this->text()->comment('Доп. информация'),
            'document_id' => $this->integer(),
            'company_id' => $this->integer()->comment('ID компании, управляющей данным домом')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%house}}');
    }
}
