<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "room".
 *
 * @property int $id
 * @property int $apartment_id
 * @property string $number
 *
 * @property Apartment $apartment
 */
class Room extends ActiveRecord
{
    public $room_list;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apartment_id'], 'integer'],
            [['number'], 'string', 'max' => 255],
            [
                ['apartment_id', 'number'],
                'unique',
                'targetAttribute' => ['apartment_id', 'number'],
                'message' => 'Комната уже существует'
            ],
            [
                ['apartment_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Apartment::className(),
                'targetAttribute' => ['apartment_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apartment_id' => 'Apartment ID',
            'number' => 'Number',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->number ?? null){
            $this->number = trim($this->number);
        } else {
            return false;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApartment()
    {
        return $this->hasOne(Apartment::className(), ['id' => 'apartment_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\RoomQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\RoomQuery(get_called_class());
    }

    /**
     * Ищет дубли
     * @param \app\models\Room $model Еще не сохраненная модель
     * @return bool
     */
    public static function isAvailable($model)
    {
        $result = self::find()
                ->andWhere(['apartment_id' => $model->apartment_id])
                ->andWhere(['number' => $model->number])
                ->one()
                ->id ?? null;

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @param int $id ID Помещения
     * @return string
     */
    public static function getListForApartment($id)
    {
        $rooms = '';

        foreach (Room::find()->andWhere(['apartment_id' => $id])->each() as $room) {
            if ($room->number) {
//                $rooms .= 'комн ' . $room->number . ', ';
                if ($rooms){
                    $rooms .= ', ' . $room->number ;
                } else {
                    $rooms = $room->number;
                }

            }
        }

        if ($rooms) {
            return $rooms;
        }

        return 'Нет';

    }

    /**
     * @param int $id ID комнаты
     * @return string
     */
    public function getFullAddress($id)
    {
        $room_model = self::findOne($id);

        $apartment_num = $room_model->apartment->number;
        $house_addr = $room_model->apartment->house->address;

        return $house_addr . ', кв ' . $apartment_num . ' комн ' . $room_model->number;
    }

    /**
     * Проверяет на существование комнату. Если не существует - возвращает null, в противном случае число.
     * @param string $number Номер комнаты
     * @param int $apartment_id ID помещения
     * @return int|null
     */
    public static function isExist($number, $apartment_id)
    {
        $result = self::find()
                ->andWhere(['number' => $number])
                ->andWhere(['apartment_id' => $apartment_id])
                ->one()->id ?? null;

        return $result;
    }
}
