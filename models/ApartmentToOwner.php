<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "apartment_to_owner".
 *
 * @property int $id
 * @property int $apartment_id
 * @property int $owner_id ID владельца помещения/квартиры
 *
 * @property Apartment $apartment
 * @property Resident $owner
 */
class ApartmentToOwner extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apartment_to_owner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apartment_id', 'owner_id'], 'integer'],
            [['apartment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Apartment::className(), 'targetAttribute' => ['apartment_id' => 'id']],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resident::className(), 'targetAttribute' => ['owner_id' => 'id']],
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
            'owner_id' => 'ID владельца помещения/квартиры',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApartment()
    {
        return $this->hasOne(Apartment::className(), ['id' => 'apartment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Resident::className(), ['id' => 'owner_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\ApartmentToOwnerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ApartmentToOwnerQuery(get_called_class());
    }
}
