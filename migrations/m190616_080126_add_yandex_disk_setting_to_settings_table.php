<?php

use yii\db\Migration;

/**
 * Class m190616_080126_add_yandex_disk_setting_to_settings_table
 */
class m190616_080126_add_yandex_disk_setting_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('settings', 'note', $this->text()->comment('Примечания'));
        //Настройка для суперкомпании
        $this->insert('settings',[
            'key' => 'yandex_disk_client_id',
            'value' => '7622a31534b847209784c0687044b514', //ID для теста приложения
            'name' => 'ID приложения яндекс-диска',
            'company_id' => 1,
            'note' => 'ID для тестов работы приложения',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190616_080126_add_yandex_disk_setting_to_settings_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190616_080126_add_yandex_disk_setting_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
