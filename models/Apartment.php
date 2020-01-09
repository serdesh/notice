<?php

namespace app\models;

use app\models\query\ApartmentQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "apartment".
 *
 * @property int $id
 * @property int $house_id ID дома
 * @property int $is_residential Жилое или не жилое Жилое = 1
 * @property string $number Номер помещения
 * @property string $address Адрес
 * @property string $cadastral_number Кадастровый номаер
 *
 * @property House $house
 * @property Room[] $rooms
 * @property ApartmentToOwner[] $apartmentToOwners
 */
class Apartment extends ActiveRecord
{
    public $rooms;
    public $room_list;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apartment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['house_id'], 'integer'],
            [['number'], 'string', 'max' => 255],
            [
                ['house_id', 'number'],
                'unique',
                'targetAttribute' => ['house_id', 'number'],
                'message' => 'Помещение уже существует',
            ],
            [
                ['house_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => House::className(),
                'targetAttribute' => ['house_id' => 'id']
            ],
            [['is_residential'], 'integer'],
            [['address', 'room_list', 'cadastral_number'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'house_id' => 'ID дома',
            'number' => 'Номер помещения',
            'is_residential' => 'Тип помещения',
            'rooms' => 'Комнаты',
            'address' => 'Адрес',
            'room_number' => 'Комнаты',
            'cadastral_number' => 'Кадастровый номер',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getHouse()
    {
        return $this->hasOne(House::className(), ['id' => 'house_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRooms()
    {
        return $this->hasMany(Room::className(), ['apartment_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getApartmentToOwners()
    {
        return $this->hasMany(ApartmentToOwner::className(), ['apartment_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ApartmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApartmentQuery(get_called_class());
    }

    /**
     * Получает адрес помещения (с номером помещения)
     * @param int $id ID Помещения
     * @return string
     */
    public function getFullAddress($id = null)
    {
        $id ? $model = self::findOne($id) : $model = $this;

        if ($model->id) {
            $rooms = $model->rooms;

            \Yii::info($rooms, 'test');

            $rooms_list = '';
            if ($rooms) {
                foreach ($rooms as $room) {
                    $rooms_list .= ' комн ' . $room->number . ',';
                }
            }
            return $model->house->address . ', кв ' . $model->number . $rooms_list;
        }

        return 'Не найдено';
    }

    /**
     * @param int $id ID Жильца
     * @return string
     */
    public function getFullAddressByResident($id)
    {
        $resient_model = Resident::findOne($id);

        $house_address = $resient_model->apartment->house->address;
        $apartment_number = $resient_model->apartment->number;
        $room_number = $resient_model->room->number;

        return $house_address . $apartment_number . $room_number;

    }

    /**
     * Возвращает список квартир в доме
     * @param int $house_id ID дома
     * @return array
     */
    public function getList($house_id)
    {
        return ArrayHelper::map(self::find()->where(['house_id' => $house_id])->all(), 'id', 'number');
    }


    /**
     * Проверяет существование помещения.
     * Если найдено совпадение house_id, number - значит помещение уже есть
     * @param \app\models\Apartment $model Еще не сохраненная модель квартиры (нет $model->id)
     * @return bool
     */
    public static function isAvailable($model)
    {

        $result = self::find()
                ->andWhere(['house_id' => $model->house_id])
                ->andWhere(['number' => $model->number])
                ->one()
                ->id ?? null;

        if ($result) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public static function getAddresses()
    {
        if (User::isSuperAdmin()) {
            $sql = 'SELECT *, CONCAT(h.address, "кв", a.number) as fullAddress 
                    FROM house AS h 
                    LEFT JOIN  apartment AS a 
                    ON a.house_id = h.id';
        } else {
            $sql = 'SELECT *, CONCAT(h.address, "кв", a.number) as fullAddress 
                    FROM house AS h 
                    LEFT JOIN  apartment AS a 
                    ON a.house_id = h.id 
                    WHERE h.company_id = ' . User::getCompanyIdForUser();
        }

        $sql .= ' AND a.is_residential = 1';

        return ArrayHelper::map(self::findBySql($sql)->all(), 'id', 'fullAddress');

    }

    /**
     * Получает ID Помещения и комнаты (если имеется)
     * @param string $address Адрес дома
     * @param string $apartment_number Номер помещения
     * @param string $room_number Номер комнаты
     * @return array Возвращает [apartment_id, room_id]
     */
    public static function getApartmentAndRoomId($address, $apartment_number, $room_number = null)
    {
        $apartment_id = null;
        $room_id = null;

        $house_id = House::find()
                ->andWhere(['import_address' => $address])
                ->one()->id ?? null;

        if ($house_id) {
            $apartment_id = self::find()
                    ->andWhere(['house_id' => $house_id])
                    ->andWhere(['number' => $apartment_number])
                    ->one()->id ?? null;
            if ($apartment_id && $room_number) {
                $room_id = Room::find()
                        ->andWhere(['number' => $room_number])
                        ->andWhere(['apartment_id' => $apartment_id])
                        ->one()->id ?? null;
            }
        }

        $arr = [$apartment_id, $room_id];

        return $arr;
    }
}
