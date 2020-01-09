<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%petition}}`.
 */
class m190416_131827_create_petition_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%petition}}', [
            'id' => $this->primaryKey(),
            'header' => $this->string()->comment('Наименование заявки'),
            'text' => $this->text()->comment('Содержание заявки'),
            'status_id' => $this->integer()->comment('ID статуса заявки'),
            'specialist_id' => $this->integer(),
            'manager_id' => $this->integer(),
            'resident_id' => $this->integer()->comment('Заявитель'),
            'where_type' => $this->string()->comment('Откуда пришла заявка (Эл. почта, заведена вручную, из виджета)'),
            'relation_petition_id' => $this->integer()->comment('ID связанной заявки'),
            'execution_date' => $this->timestamp()->defaultValue(null)->comment('Дата исполнения заявки'),
            'created_by' => $this->integer()->comment('ID создавшего заявку'),
            'created_at' => $this->timestamp()->defaultValue(new Expression('NOW()'))->comment('Дата создания'),
            'answer' => $this->text()->comment('Ответ заявителю'),
            'closed_user_id' => $this->integer()->comment('Пользователь, закрывший заявку'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%petition}}');
    }
}
