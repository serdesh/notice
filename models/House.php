<?php

namespace app\models;

use app\models\query\HouseQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "house".
 *
 * @property int $id
 * @property int $street_id
 * @property string $fias_number Номер из ФИАС
 * @property string $cadastral_number Кадастровый номер
 * @property int $residential_number Кол-во жилых помещений
 * @property int $non_residential_number Кол-во не жилых помещений
 * @property string $additional_info Доп. информация
 * @property int $document_id
 * @property int $company_id ID компании, управляющей данным домом
 * @property string $number Номер
 * @property string $address Адрес
 * @property string $import_address Адрес в формате файла импорта
 *
 * @property Apartment[] $apartments
 * @property Document $document
 * @property Street $street
 * @property Company $company
 */
class House extends ActiveRecord
{
    public $street_name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'house';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['street_id', 'residential_number', 'non_residential_number', 'document_id', 'company_id'], 'integer'],
            [['additional_info', 'number', 'address'], 'string'],
            [['fias_number', 'cadastral_number'], 'string', 'max' => 255],
            [
                ['document_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Document::className(),
                'targetAttribute' => ['document_id' => 'id']
            ],
            [
                ['street_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Street::className(),
                'targetAttribute' => ['street_id' => 'id']
            ],
            [['import_address'], 'string'],
            [['address'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'street_id' => 'Street ID',
            'fias_number' => 'Номер по ФИАС',
            'cadastral_number' => 'Кадастровый номер',
            'residential_number' => 'Кол-во жилых помещений',
            'non_residential_number' => 'Кол-во не жилых помещений',
            'additional_info' => 'Доп. информация',
            'document_id' => 'Document ID',
            'company_id' => 'ID компании, управляющей данным домом',
            'number' => 'Номер',
            'street_name' => 'Улица',
            'address' => 'Адрес',
            'import_address' => 'Адрес из файла импорта',
        ];
    }

    public function beforeSave($insert)
    {
        if (!Users::isSuperAdmin() && !Users::isSuperManager()) {
            //Подставляем компанию текущего пользователя
            $this->company_id = (new Users)->getCompanyIdForUser();
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return ActiveQuery
     */
    public function getApartments()
    {
        return $this->hasMany(Apartment::className(), ['house_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::className(), ['id' => 'document_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStreet()
    {
        return $this->hasOne(Street::className(), ['id' => 'street_id']);
    }

    /**
     * {@inheritdoc}
     * @return HouseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HouseQuery(get_called_class());
    }

//    /**
//     * @param int $id ID Дома
//     * @return string
//     */
//    public function getFullAddress($id = null)
//    {
//        if (!$id) {
//            $model = $this;
//        } else {
//            $model = self::findOne($id) ?? null;
//        }
//
//        if ($model) {
//            return $model->street->getShortName() . ', д.' . $model->number;
//        }
//
//        return 'Не найдено';
//
//    }

    /**
     * Получает полный адрес, включая номер дома
     * @param int $id ID Дома
     * @return null|string
     */
    public static function getFullAddressWithHouse($id)
    {
        return House::findOne($id)->address ?? null;
    }

    /**
     * Если передан ID улицы - получает список домов улицы,
     * в противном случае список всех домов
     * @param int $street_id ID Улицы
     * @return array
     */
    public function getList($street_id = null)
    {
        Yii::info('Street ID: ' . $street_id, 'test');
        if ($street_id) {
            $result = self::find()->andWhere(['street_id' => $street_id])->all();
        } else {
            $result = self::find()->all();
        }

        return ArrayHelper::map($result, 'id', 'number');
    }

    /**
     * Получает неприкрепленные к УК дома
     * @return array
     */
    public static function getNotDefinedHousesList()
    {
        $houses = self::find()->andWhere(['IS', 'company_id', null])->all();

        return ArrayHelper::map($houses, 'id', 'number');
    }

    /**
     * Получает дома УК
     * Обязательно возвращаем массив только ID домов, т.к. его принимает Select2 с множественным выбором
     * @param int $id ID Компании
     * @return array
     */
    public static function getHousesByCompany($id)
    {
        Yii::info('Company ID: ' . $id, 'test');

        $houses = [];
        $result = self::find()->byCompany($id)->all() ?? null;

        foreach ($result as $house) {
            array_push($houses, $house->id);
        }

        return $houses;
    }

    /**
     * Получает все дома компании + непривязанные дома
     * @param int $id Компании
     * @return array
     */
    public static function getHousesByCompanyAndFreeHouses($id)
    {
//        $result = self::find()->byCompany($id)->orWhere(['IS', 'company_id', null])->all() ?? null;
//        return ArrayHelper::map($result, 'id', 'number');

        $addresses = House::find()
            ->andWhere(['company_id' => $id])
            ->orWhere(['IS', 'company_id', null])
            ->all();

        return ArrayHelper::map($addresses, 'id', 'address');

//        foreach ($streets as $street) {
//
//            Yii::info($street, 'test');
//
//            foreach ($street['houses'] as $house) {
//                if ($house['company_id'] == $id || !$house['company_id']) {
//                    $result[$street['type']['short_name'] . ' ' . $street['name']][$house['id']] = $house['number'];
//                }
//
//            }
//        }
//        return $result;
    }

    public static function getFreeHouses()
    {
        $addresses = House::find()
            ->andWhere(['IS', 'company_id', null])
            ->all();

        return ArrayHelper::map($addresses, 'id', 'address');
    }

    /**
     * Для теста
     * @param int $id ID компании
     * @return array
     */
    public static function getTest($id)
    {

        Yii::info('Company ID: ' . $id, 'test');

        $streets = Street::find()
            ->joinWith(['houses h'])
            ->andWhere(['h.company_id' => $id])
            ->asArray()
            ->all();

        $result = [];
        foreach ($streets as $street) {
            Yii::info($street, 'test');
            foreach ($street['houses'] as $house) {
                $result[$street['name']][$house['id']] = $house['number'];
            }
        }

        return $result;

    }

    /**
     * Получает адреса всех домов, относящихся к компании
     * @return array
     */
    public static function getAddresses()
    {
        if (User::isSuperAdmin() || Users::isSuperManager()) {
            return ArrayHelper::map(self::find()
                ->all(),
                'id', 'address');
        }
        return ArrayHelper::map(self::find()
            ->andWhere(['company_id' => Users::getCompanyIdForUser()])
            ->all(),
            'id', 'address');
    }

    /**
     * Проверяет на существование параметры дома
     * Проверяет по трем параметрам Номер в ФИАС, Кадастровый номер и адрес
     * @param \app\models\House $model еще не сохраненная модель дома (нет $model->id)
     * @return bool
     */
    public static function isAvailable($model)
    {

        Yii::info('Checked model House: ', 'test');
        Yii::info( $model->toArray(), 'test');

        $result = self::find()
            ->andWhere(['fias_number' => $model->fias_number])
            ->andWhere(['cadastral_number' => $model->cadastral_number])
            ->andWhere(['address' => $model->address])
            ->one()
            ->id ?? null;



        if ($result) {
            Yii::info('House is Available: true' , 'test');
            return true;
        }

        Yii::info('House is Available: false', 'test');
        return false;
    }

}
