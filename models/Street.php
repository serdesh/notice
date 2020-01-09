<?php

namespace app\models;

use app\models\query\StreetQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "street".
 *
 * @property int $id
 * @property int $type_id ID типа улицы
 * @property string $name
 *
 * @property House[] $houses
 * @property Type $type
 */
class Street extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'street';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => Type::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'ID типа улицы',
            'name' => 'Наименование',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHouses()
    {
        return $this->hasMany(House::className(), ['street_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    /**
     * {@inheritdoc}
     * @return StreetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StreetQuery(get_called_class());
    }

    /**
     * Получает наименование улицы с сокращенным типом. Напр: ул. Новокузнецкая
     * @param int $id ID улицы
     * @return string
     */
    public function getShortName($id = null)
    {
        if (!$id){
            $model = $this;
        } else {
            $model= self::findOne($id);
        }

        return $model->type->short_name . ' ' . $model->name;

    }

    /**
     * Получает наименование улицы с полным типом. Напр: улица Новокузнецкая
     * @return string
     */
    public function getFullName()
    {
        return $this->type->full_name . ' ' . $this->name;
    }

    /**
     * Если не указан тип получает список типов улиц
     * в противном случае получает один тип улицы
     * @param int $type_id ID типа улицы
     * @return array
     */
    public static function getList($type_id = null)
    {
        if (!$type_id) return ArrayHelper::map(self::find()->all(), 'id', 'name') ?? [];

        return ArrayHelper::map(self::find()->where(['type_id' => $type_id])->all(), 'id', 'name') ?? [];

    }
}
