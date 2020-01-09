<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%resident}}`.
 */
class m190416_133231_create_resident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%resident}}', [
            'id' => $this->primaryKey(),
            'owner' => $this->smallInteger(1)->defaultValue(0)->comment('Собственник помещения'),
            'last_name' => $this->string()->comment('Фамилия'),
            'first_name' => $this->string()->comment('Имя'),
            'patronymic' => $this->string()->comment('Отчество'),
            'birth_date' => $this->date()->comment('Дата рождения'),
            'contact_id' => $this->integer()->comment('ID контакта'),
            'related_degree' => $this->string()->comment('Степень родства с собственником'),
            'additional_info' => $this->text()->comment('Дополнительная информация'),
            'apartment_id' => $this->integer()->comment('ID квартиры'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%resident}}');
    }
}
