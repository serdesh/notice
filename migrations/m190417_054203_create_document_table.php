<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%document}}`.
 */
class m190417_054203_create_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'outer_id' => $this->string()->comment('ID Документа в облаке'),
            'local_path' => $this->string()->comment('Путь к документу на сервере'),
            'created_by' => $this->integer()->comment('ID добавившего документ'),
            'created_at' => $this->timestamp()->defaultValue(new Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%document}}');
    }
}
