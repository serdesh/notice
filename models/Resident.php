<?php

namespace app\models;

use app\models\query\ResidentQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "resident".
 *
 * @property int $id
 * @property int $owner Собственник помещения
 * @property string $last_name Фамилия
 * @property string $first_name Имя
 * @property string $patronymic Отчество
 * @property string $birth_date Дата рождения
 * @property int $contact_id ID контакта
 * @property string $related_degree Степень родства с собственником
 * @property string $additional_info Дополнительная информация
 * @property int $apartment_id ID квартиры
 * @property int $snils СНИЛС
 * @property int $room_id ID комнаты
 * @property int $num_record Номер записи лицевого счета. Используется только при импорте
 * @property int $created_by_company Компания, добавившая в базу жильца
 * @property string $resident_emails Email`ы жильца
 *
 * @property ApartmentToOwner[] $apartmentToOwners
 * @property Petition[] $petitions
 * @property Contact $contact
// * @property Apartments[] $apartments
 * @property Apartment $apartment
 * @property Room $room
 * @property ResidentEmail[] $emails
 */
class Resident extends ActiveRecord
{
    public $address;
    public $house;
    public $fio;
    public $resident_emails;
    public $phone;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resident';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['house', 'owner', 'contact_id', 'apartment_id', 'room_id', 'created_by_company'], 'integer'],
            [['birth_date', 'request_page', 'request_id'], 'safe'],
            [['additional_info', 'snils', 'num_record'], 'string'],
            [['last_name', 'first_name', 'patronymic', 'related_degree', 'resident_emails'], 'string', 'max' => 255],
            [
                ['contact_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Contact::className(),
                'targetAttribute' => ['contact_id' => 'id']
            ],
            ['phone', 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner' => 'Собственник помещения',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'patronymic' => 'Отчество',
            'birth_date' => 'Дата рождения',
            'contact_id' => 'ID контакта',
            'related_degree' => 'Степень родства с собственником',
            'additional_info' => 'Дополнительная информация',
            'apartment_id' => 'ID квартиры',
            'house' => 'ID дома',
            'address' => 'Адрес',
            'snils' => 'СНИЛС',
            'room_id' => 'ID комнаты',
            'created_by_company' => 'Компания-создатель',
            'resident_emails' => 'Email',
            'phone' => 'Телефон',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->room_id == 0) {
            $this->room_id = null;
        }
        $this->birth_date = Functions::getDateForBase($this->birth_date);

        if (!$this->created_by_company) {
            $this->created_by_company = Users::getCompanyIdForUser();
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return ActiveQuery
     */
    public function getApartmentToOwners()
    {
        return $this->hasMany(ApartmentToOwner::className(), ['owner_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getApartments()
    {
        return $this->hasMany(Apartment::className(), ['id' => 'apartment_id'])
            ->viaTable('apartment_to_owner', ['owner_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getApartment()
    {
        return $this->hasOne(Apartment::className(), ['id' => 'apartment_id']);
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getHouse()
    {
        return $this->hasOne(House::className(), ['id' => 'house_id'])
            ->viaTable('apartment', ['id' => 'apartment_id']);
    }

    /**
     * @return Street
     */
    public function getStreet()
    {
        return $this->apartment->house->street;
    }

    /**
     * @return ActiveQuery
     */
    public function getPetitions()
    {
        return $this->hasMany(Petition::className(), ['resident_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getEmails()
    {
        return $this->hasMany(ResidentEmail::class, ['resident_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ResidentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ResidentQuery(get_called_class());
    }

    /**
     * Получает ФИО жильца
     * @param int $id ID Жильца
     * @return string
     */
    public function getFullName($id = null)
    {
        if ($id) {
            $model = self::findOne($id) ?? null;
            if (!$model) {
                return 'Не найдено';
            }
            return $model->last_name . ' ' . $model->first_name . ' ' . $model->patronymic;
        }

        if ($this->last_name || $this->first_name || $this->patronymic) {
            return $this->last_name . ' ' . $this->first_name . ' ' . $this->patronymic;
        }
        return '';
    }

    /**
     * Получает список всех жильцов
     * @return array
     */
    public static function getList()
    {
//        $sql = 'SELECT *, CONCAT(last_name, first_name, patronymic) as fullName, resident.id as id FROM resident LEFT JOIN apartment ON resident.apartment_id = apartment.id LEFT JOIN house ON apartment.house_id = house.id WHERE house.company_id = ' . Users::getCompanyIdForUser();
        $sql = 'SELECT *, CONCAT(last_name, first_name, patronymic) AS fullName, resident.id AS id FROM resident WHERE created_by_company = ' . Users::getCompanyIdForUser();

        return ArrayHelper::map(self::findBySql($sql)->all(), 'id', 'fullName');

    }

    /**
     * Получает список всех жильцов
     * @return array
     */
    public static function getNameList()
    {
        $sql = 'SELECT *, CONCAT(last_name, first_name, patronymic) AS fullName FROM resident';

        $result = [];

        foreach (self::findBySql($sql)->each() as $resident) {
            array_push($result, $resident->fullName);
        }

//        \Yii::info($result, 'test');

        return $result;

    }

    /**
     * @return string
     */
    public function getAddress()
    {
        if ($this->room_id) {
            return $this->room->getFullAddress($this->room_id);
        } else {
            if ($this->apartment_id) {
                return $this->apartment->getFullAddress();
            }
            return null;
        }
    }

    /**
     * Проверяет на существование жильца
     * Проверяемые параметры: ФИО, ID квартиры
     * @param \app\models\Resident $model Еще не сохранненная модель жильца (нет $model->id)
     * @return bool
     */
    public static function isAvailable($model)
    {

        $result = self::find()
                ->andWhere(['last_name' => $model->last_name])
                ->andWhere(['first_name' => $model->first_name])
                ->andWhere(['patronymic' => $model->patronymic])
                ->andWhere(['apartment_id' => $model->apartment_id])
                ->one()->id ?? null;

        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * Добавляет телефон жильцу
     * @param $phone
     * @return null
     */
    public function addPhone($phone)
    {
        if (!$phone) {
            return null;
        }

        $phone = Functions::formatPhone($phone);

        /** @var Contact $contact */
        $contact = $this->contact ?? null;

        if (!$contact) {
            $contact = new Contact();
        }

        $contact->phone = str_replace(' ', '', $contact->phone); //Убираем все пробелы

        if ($contact->phone) {
            if (strpos($contact->phone, ',') > 0) {
                //Если уже есть больше одного номера телефона (телефоны разделены запятой)
                $phones = explode(',', $contact->phone);
                if (!in_array($phone, $phones)) {
                    //Добавляем телефон
                    array_push($phones, $phone);
                }
                $contact->phone = implode(',', $phones);
            } else {
                //Просто дописываем номер телефона
                $contact->phone .= ',' . $phone;
            }
        } else {
            $contact->phone = $phone;
        }

        if (!$contact->save()) {
            \Yii::error($contact->errors, '_error');
            return false;
        } else {
            if (!$this->contact_id) {
                $this->contact_id = $contact->id;
            }
            $this->save();
        }
        return true;
    }

    public function getAllEmails($id = null)
    {
        if (!$id) {
            $models = ResidentEmail::find()->andWhere(['resident_id' => $this->id])->all();
        } else {
            $models = ResidentEmail::find()->andWhere(['resident_id' => $id])->all();
        }

        $result_arr = [];

        foreach ($models as $email_model) {
            array_push($result_arr, $email_model->email);
        }

        return implode(', ', $result_arr);
    }

    public static function getListEmails($id)
    {
        return ArrayHelper::map(
            ResidentEmail::find()
                ->andWhere(['resident_id' => $id])
                ->all(),
            'id', 'email');
    }

}
