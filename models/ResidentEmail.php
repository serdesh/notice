<?php

namespace app\models;

use app\models\query\ResidentEmailQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "resident_email".
 *
 * @property int $id
 * @property int $resident_id Жилец/Заявитель
 * @property string $email
 * @property string $note
 *
 * @property Resident $resident
 */
class ResidentEmail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resident_email';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['resident_id'], 'integer'],
            [['note'], 'string'],
            [['email'], 'email', 'message' => 'Email не корректен'],
            [['resident_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resident::class, 'targetAttribute' => ['resident_id' => 'id']],
            ['email', 'unique', 'message' => 'Email уже используется'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'resident_id' => 'Жилец/Заявитель',
            'email' => 'Email',
            'note' => 'Note',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResident()
    {
        return $this->hasOne(Resident::className(), ['id' => 'resident_id']);
    }

    /**
     * {@inheritdoc}
     * @return ResidentEmailQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ResidentEmailQuery(get_called_class());
    }


}
