<?php

namespace app\models;

use app\models\query\StatusQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "status".
 *
 * @property int $id
 * @property string $name
 *
 * @property Petition[] $petitions
 */
class Status extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetitions()
    {
        return $this->hasMany(Petition::className(), ['status_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return StatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StatusQuery(get_called_class());
    }

    /**
     * Получает список статусов
     * @param boolean $for_filter Флаг, показывающий для фильтра список или нет
     * если для фильтра, то исключаются Арзивировано и Просрочено
     * @return array
     */
    public static function getList($for_filter = false)
    {
        if ($for_filter){
            return ArrayHelper::map(self::find()
                ->andWhere(['<>', 'name', 'Архивировано'])
                ->andWhere(['<>', 'name', 'Просрочено'])
                ->all(), 'id', 'name');
        }
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * Получает Id статуса по наименованию
     * @param string $name Наименование параметра
     * @return int|null
     */
    public static function getStatusByName($name)
    {
        $result = self::find()->andWhere(['name' => $name])->one()->id ?? null;

        Yii::info('Status ID: ' . $result, 'test');

        return $result;
    }
}
