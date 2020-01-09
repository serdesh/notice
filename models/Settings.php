<?php

namespace app\models;

use app\models\query\SettingsQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $name Наименование настройки
 * @property int $user_id ID пользователя к котрому относится настройка
 * @property int $company_id ID компании к котрой относится настройка
 * @property int $note Примечание
 */
class Settings extends ActiveRecord
{
    const DRIVE_YANDEX = 'yandex';
    const DRIVE_GOOGLE = 'google';
    const DRIVE_LOCAL = null;
    const KEY_ATC_CODE = 'atc_code';
    const KEY_ATC_KEY = 'atc_key';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'company_id'], 'integer'],
            [['key', 'value', 'name', 'note'], 'string', 'max' => 255],
//            ['key', 'unique', 'targetAttribute' => ['key', 'company_id']],
//            ['key', 'unique', 'targetAttribute' => ['key', 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Ключ',
            'value' => 'Значение',
            'name' => 'Наименование настройки',
            'user_id' => 'ID пользователя к котрому относится настройка',
            'company_id' => 'ID компании к котрой относится настройка',
            'note' => 'Примечание',
        ];
    }

    /**
     * {@inheritdoc}
     * @return SettingsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SettingsQuery(get_called_class());
    }

    /**
     * Получает значение по ключу
     * @param $key
     * @return null|string
     */
    public static function getValueByKey($key)
    {
        return self::find()
                ->andWhere(['key' => $key])
                ->one()
                ->value ?? null;
    }

    /**
     * Получает значение по ключу для пользователя
     * @param $key
     * @param $user_id
     * @return null|string
     */
    public static function getValueByKeyFromUser($key, $user_id)
    {
        Yii::info('key: ' . $key, 'test');
        Yii::info('key: ' . $user_id, 'test');

        return self::find()
                ->andWhere(['key' => $key])
                ->andWhere(['user_id' => $user_id])
                ->one()
                ->value ?? null;
    }

    public static function getValueByKeyFromCompany($key, $company_id)
    {
        Yii::info('Запрашиваемый ключ настроек: ' . $key, 'test');
        Yii::info('ID компании: ' . $company_id, 'test');
        $result = self::find()
                ->andWhere(['key' => $key])
                ->andWhere(['company_id' => $company_id])
                ->one()
                ->value ?? null;
        Yii::info('Найденное значение: ' . $result, 'test');

        return $result;
    }

    /**
     * Получает тип хранилища файлов yandex||google||null (Яндекс диск, GoogleDrive, сервер соответственно)
     * @param null $company_id
     * @return null|string
     */
    public static function getDrive($company_id = null)
    {
        if (!$company_id) {
            $company_id = Users::getCompanyIdForUser();
        }
        return self::getValueByKeyFromCompany('drive_type', $company_id);
    }

    /**
     * @param string $key Ключ настройки
     * @return bool
     */
    public static function keyExist($key)
    {
        return self::find()
            ->andWhere(['key' => $key])
            ->andWhere(['OR', ['company_id' => Users::getCompanyIdForUser()], ['user_id' => Yii::$app->user->id]])
            ->exists();
    }
}
