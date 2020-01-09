<?php

namespace app\models;

use app\models\query\ContactQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property string $address
 * @property string $email
 * @property string $phone
 *
 * @property Company[] $companies
 * @property Phone[] $phones
 * @property Resident[] $residents
 * @property Users[] $users
 */
class Contact extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['address'], 'string'],
            [['email', 'phone'], 'string', 'max' => 255],
            [['email',], 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'address' => 'Адрес',
            'email' => 'Email',
            'phone' => 'Телефон',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->phone){
            $this->phone = self::preparePhone($this->phone);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
        return $this->hasMany(Phone::className(), ['contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResidents()
    {
        return $this->hasMany(Resident::className(), ['contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['contact_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ContactQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContactQuery(get_called_class());
    }

    /**
     * Оставляет последние 10 цифр в номере
     * @param string $phone_number Номер телефона
     * @return bool|string
     */
    public static function preparePhone($phone_number)
    {
        $phone_number = trim($phone_number);
        return substr($phone_number, -10);

    }

    public static function getPhonesWithContact($id)
    {
        return ArrayHelper::map(Phone::findAll(['contact_id' => $id]), 'id', 'number');
    }
}
