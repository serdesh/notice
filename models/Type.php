<?php

namespace app\models;

use app\models\query\TypeQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "type".
 *
 * @property int $id
 * @property string $full_name
 * @property string $short_name
 *
 * @property Street[] $streets
 */
class Type extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_name', 'short_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'short_name' => 'Short Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreets()
    {
        return $this->hasMany(Street::className(), ['type_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return TypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TypeQuery(get_called_class());
    }

    public static function getTypeList()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'full_name') ?? null;
    }

    /**
     * @param string $name Наименование типа
     * @return ActiveRecord
     */
    public static function getTypeByName($name)
    {
        return self::find()->where(['full_name' => $name])->one();
    }
}
