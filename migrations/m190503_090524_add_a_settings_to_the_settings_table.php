<?php

use yii\db\Migration;

/**
 * Class m190503_090524_add_a_settings_to_the_settings_table
 */
class m190503_090524_add_a_settings_to_the_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'value', 'name'],[
            ['dadata_token', 'c2ae0bae1eaeb49d2994e4bc31be54543b871251', 'Токен dadata.ru'],
            ['dadata_secret', '4a7e8448a6dc065bb54163b43c9b6e6786963b67', 'Секрет dadata.ru'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190503_090524_add_a_settings_to_the_settings_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190503_090524_add_a_settings_to_the_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
