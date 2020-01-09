<?php

namespace app\models;

use app\models\query\CallQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "call".
 *
 * @property int $id
 * @property string $created_at
 * @property string $phone_number
 * @property int $petition_id
 * @property int $resident_id Владелец телефонного номера
 * @property int $specialist_id Ответчтвенный исполнитель (специалист)
 * @property int $company_id ID Компании, получившей звонок
 *
 * @property Petition $petition
 * @property Resident $resident
 */
class Call extends ActiveRecord
{
    public $petition_status;
    public $specialist_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'call';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['petition_id', 'resident_id', 'specialist_id', 'company_id'], 'integer'],
            [['phone_number', 'petition_status'], 'string', 'max' => 255],
            [['petition_id'], 'exist', 'skipOnError' => true, 'targetClass' => Petition::className(), 'targetAttribute' => ['petition_id' => 'id']],
            [['resident_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resident::className(), 'targetAttribute' => ['resident_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата',
            'phone_number' => 'Номер телефона',
            'petition_id' => 'Ветка обращения',
            'resident_id' => 'Владелец номера',
            'petition_status' => 'Статус',
            'specialist_id' => 'Ответственный',
            'company_id' => 'ID компании',
        ];
    }

    public function beforeSave($insert)
    {
        if (!$this->company_id){
            $this->company_id = \Yii::$app->user->identity->company_id;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetition()
    {
        return $this->hasOne(Petition::className(), ['id' => 'petition_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResident()
    {
        return $this->hasOne(Resident::className(), ['id' => 'resident_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }


    /**
     * {@inheritdoc}
     * @return CallQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CallQuery(get_called_class());
    }
}
