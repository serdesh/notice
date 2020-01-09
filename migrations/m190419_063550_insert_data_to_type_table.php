<?php

use yii\db\Migration;

/**
 * Class m190419_063550_insert_data_to_type_table
 */
class m190419_063550_insert_data_to_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%type}}', ['full_name', 'short_name'], [
            ['улица','ул'],
            ['аллея', 'аллея'],
            ['бульвар', 'б-р'],
            ['въезд','въезд'],
            ['городок','городок'],
            ['дачный поселок','дп'],
            ['дорога','дор'],
            ['заезд','заезд'],
            ['квартал','кв-л'],
            ['кольцо','кольцо'],
            ['линия','линия'],
            ['микрорайон','мкр'],
            ['Набережная','наб'],
            ['парк','парк'],
            ['переезд','переезд'],
            ['переулок','пер'],
            ['площадь','пл'],
            ['проезд','проезд'],
            ['промышленная зона','промзона'],
            ['проспект','пр-т'],
            ['проулок','проулок'],
            ['разъезд','рзд'],
            ['сквер','сквер'],
            ['тракт','тракт'],
            ['тупик','туп'],
            ['участок','уч-к'],
            ['шоссе','ш'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190419_063550_insert_data_to_type_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190419_063550_insert_data_to_type_table cannot be reverted.\n";

        return false;
    }
    */
}
