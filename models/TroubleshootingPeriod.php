<?php

namespace app\models;

use app\models\query\TroubleshootingPeriodQuery;
use DateTime;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "troubleshooting_period".
 *
 * @property int $id
 * @property string $trouble Неисправность
 * @property double $period Предельный срок выполнения ремонта. В часах
 * @property string $description
 * @property string $group
 */
class TroubleshootingPeriod extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'troubleshooting_period';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['trouble', 'description'], 'string'],
            [['period'], 'number'],
            [['group'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trouble' => 'Неисправность',
            'period' => 'Предельный срок выполнения ремонта. В часах',
            'description' => 'Примечание',
            'group' => 'Группа',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TroubleshootingPeriodQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TroubleshootingPeriodQuery(get_called_class());
    }

    /**
     * Получает список всех неисправностей
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->orderBy(['group' => SORT_ASC])->all(), 'id', 'trouble', 'group');
    }

//    public function beforeSave($insert)
//    {
//        if ($this->group){
//            $this->group = TroubleshootingPeriod::getNameTroubleGroup($this->group);
//        }
//        return parent::beforeSave($insert);
//    }

    /**
     * Возвращает дату исполнения с учетом временных показателей в таблице troubleshooting_period
     * @param int $id ID неисправности
     * @param string $date Дата, от которой начинается отсчет
     * @return string
     * @throws \Exception
     */
    public static function getExecutionDate($id, $date = null)
    {

        if (!$date) $date = date('Y-m-d H:i', time());

        Yii::info($date, 'test');

        //Получаем сколько часов выделено на задачу
        $num_hours = self::findOne($id)->period ?? null;

        $num_minutes = ceil($num_hours * 60);



        Yii::info($num_hours, 'test');

        if ($num_hours){
            $launchDate = new DateTime($date);
            $launchDate->modify('+' . $num_minutes . ' min');
            $exec_date = $launchDate->format('Y-m-d H:i');
            return $exec_date;
        }

        return date('Y-m-d H:i', time());
    }

    /**
     * Список групп
     * @return array
     */
    public static function getGroupsList()
    {
        return [
             1 => 'Кровля',
             2 => 'Стены',
             3 => 'Оконные и дверные заполнения',
             4 => 'Внутренняя и наружная отделка',
             5 => 'Полы',
             6 => 'Печи',
             7 => 'Санитарно-техническое оборудование',
             8 => 'Электрооборудование',
             9 => 'Лифт',
             10 => 'Разное',
        ];
    }

    /**
     * Получает имя группы по ID
     * @param int $id ID Группы
     * @return null|string
     */
    public static function getNameTroubleGroup($id)
    {
        if ($id == 1) return  'Кровля';
        if ($id == 2) return  'Стены';
        if ($id == 3) return  'Оконные и дверные заполнения';
        if ($id == 4) return  'Внутренняя и наружная отделка';
        if ($id == 5) return  'Полы';
        if ($id == 6) return  'Печи';
        if ($id == 7) return  'Санитарно-техническое оборудование';
        if ($id == 8) return  'Электрооборудование';
        if ($id == 9) return  'Лифт';
        if ($id == 10) return  'Разное';

        return 0;
    }

    /**
     * Получает ID группы по наименованию
     * @param string $name Наименование группы
     * @return int
     */
    public static function getIdTroubleGroup($name)
    {
        if ($name === 'Кровля') return 1;
        if ($name === 'Стены') return 2;
        if ($name === 'Оконные и дверные заполнения') return 3;
        if ($name === 'Внутренняя и наружная отделка') return 4;
        if ($name === 'Полы') return 5;
        if ($name === 'Печи') return 6;
        if ($name === 'Санитарно-техническое оборудование') return 7;
        if ($name === 'Электрооборудование') return 8;
        if ($name === 'Лифт') return 9;
        if ($name === 'Разное') return 10;

        return 0;
    }
}
