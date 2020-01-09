<?php

namespace app\models;

use app\models\query\DocumentQuery;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "document".
 *
 * @property int $id
 * @property string $name
 * @property string $outer_id ID Документа в облаке
 * @property string $local_path Путь к документу на сервере
 * @property int $created_by ID добавившего документ
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Users $createdBy
 * @property House $house
 */
class Document extends ActiveRecord
{

    public $house_id;
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'outer_id', 'local_path'], 'string', 'max' => 255],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
            [['file', 'house_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'outer_id' => 'ID Документа в облаке',
            'local_path' => 'Путь к документу на сервере',
            'created_by' => 'ID добавившего документ',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'house_address' => 'Адрес дома',
        ];
    }

    public function beforeSave($insert)
    {
        if (!$this->isNewRecord) {
            $this->updated_at = date('Y-m-d H:i:s', time());
        } else {
            $this->created_at = date('Y-m-d H:i:s', time());
            $this->created_by = \Yii::$app->user->id;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $house_id = \Yii::$app->request->post('Document')['house'] ?? null;
        if ($house_id) {
            $house = House::findOne($house_id) ?? null;
            if ($house) {
                $house->document_id = $this->id;
                $house->save();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHouse()
    {
        return $this->hasOne(House::className(), ['document_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return DocumentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DocumentQuery(get_called_class());
    }
}
