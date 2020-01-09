<?php

namespace app\models;

use app\models\query\HistoryQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "history".
 *
 * @property int $id
 * @property string $created_at
 * @property int $petition_id ID обращения, к которому относится событие
 * @property string $name Наименование события
 * @property string $description Описание события
 */
class History extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['petition_id'], 'integer'],
            [['description'], 'string'],
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
            'created_at' => 'Дата события',
            'petition_id' => 'ID обращения, к которому относится событие',
            'name' => 'Наименование события',
            'description' => 'Описание события',
        ];
    }

    /**
     * {@inheritdoc}
     * @return HistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HistoryQuery(get_called_class());
    }
}
