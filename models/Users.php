<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $fio ФИО
 * @property string $permission Должность
 * @property string $login Логин
 * @property string $password Пароль
 * @property string $inn ИНН
 * @property string $snils СНИЛС
 * @property int $company_id ID Компании
 * @property int $contact_id
 * @property string $created_at Дата создания
 * @property int $created_by Создатель
 * @property string $email
 *
 * @property Company $company
 */
class Users extends ActiveRecord
{
    const USER_ROLE_SUPER_ADMIN = 'super_administrator';
    const USER_ROLE_SUPER_MANAGER = 'super_manager';
    const USER_ROLE_ADMIN = 'administrator';
    const USER_ROLE_MANAGER = 'manager';
    const USER_ROLE_SPECIALIST = 'specialist';

    public $new_password;
    public $file;
    public $fake_login; //Для запрета смены логина админом организации

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fio', 'permission', 'login', 'fake_login'], 'string', 'max' => 255],
            ['login', 'match', 'pattern' => '/^[a-z]\w*$/i', 'message' => 'Логин не соответствует требованиям'],
            [['email'], 'unique'],
            [
                ['password', 'new_password'],
                'string',
                'min' => 6,
                'message' => 'Пароль должен содержать минимум 6 символов',
                'skipOnEmpty' => true,
            ],

            [['inn', 'snils'], 'string'],
            [['created_at'], 'safe'],
            [['fio', 'password', 'login'], 'required'],
            [['contact_id', 'company_id', 'created_by'], 'integer'],
            [['email'], 'email'],
            [['company_id'], 'required', 'message' => 'Не выбрана компания'],
            [['permission'], 'required', 'message' => 'Не выбрана должность'],
            [
                ['password', 'new_password'],
                'match',
                'pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{6,}$/',
                'message' => 'Пароль не соответствует требованиям!',
//                'message' => 'Пароль не соответствует требованиям! Требования к паролю:
//                            1. Не короче 6 символов.
//                            2. Пароль может содержать только латинские строчные и заглавные буквы, цифры и спецсимволы.
//                            3. Пароль должен содержать как минимум одну строчную букву, как минимум одну заглавную букву и как минимум одну цифру.
//                '
            ],
            ['login', 'unique', 'targetAttribute' => ['login', 'company_id'], 'message' => 'Логин уже используется'] //Пара логин и ID компании должны быть уникальны
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fio' => 'ФИО',
            'permission' => 'Должность',
            'login' => 'Логин',
            'password' => 'Пароль',
            'new_password' => 'Новый пароль',
            'inn' => 'ИНН',
            'snils' => 'СНИЛС',
            'created_at' => 'Дата создания',
            'contact_id' => 'ID Контакта',
            'company_id' => 'ID Компании',
            'created_by' => 'Создатель',
            'email' => 'Email',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->password = md5($this->password);
        }

        if ($this->new_password != null) {
            Yii::info($this->password, 'test');
            $this->password = md5($this->new_password);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Получает список ролей пользователей
     * @return array
     */
    public function getRoleList()
    {
        if (self::isSuperAdmin()) {
            return ArrayHelper::map([
                ['id' => self::USER_ROLE_SUPER_ADMIN, 'name' => 'Супер Администратор',],
                ['id' => self::USER_ROLE_SUPER_MANAGER, 'name' => 'Супер менеджер',],
                ['id' => self::USER_ROLE_ADMIN, 'name' => 'Администратор',],
                ['id' => self::USER_ROLE_MANAGER, 'name' => 'Менеджер',],
                ['id' => self::USER_ROLE_SPECIALIST, 'name' => 'Специалист',],
            ], 'id', 'name');
        } elseif (self::isAdmin()) {
            return ArrayHelper::map([
                ['id' => self::USER_ROLE_MANAGER, 'name' => 'Менеджер',],
                ['id' => self::USER_ROLE_SPECIALIST, 'name' => 'Специалист',],
            ], 'id', 'name');
        }
        return [];
    }

    /**
     * Получает описание роли
     * @param string $permission имя роли
     * @return string
     */
    public function getRoleDescription($permission = null)
    {
        if ($permission) {
            $this->permission = $permission;
        }
        if (self::USER_ROLE_SUPER_ADMIN == $this->permission) {
            return 'Супер Администратор';
        }
        if (self::USER_ROLE_SUPER_MANAGER == $this->permission) {
            return 'Супер Менеджер';
        }
        if (self::USER_ROLE_ADMIN == $this->permission) {
            return 'Администратор';
        }
        if (self::USER_ROLE_MANAGER == $this->permission) {
            return 'Менеджер';
        }
        if (self::USER_ROLE_SPECIALIST == $this->permission) {
            return 'Специалист';
        }

        return 'Не найдено';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * Получает описание роли по наименованию роли
     * @param string $role_name Имя роли
     * @return string
     */
    public static function getRoleDescriptionByRoleName($role_name)
    {
        switch ($role_name) {
            case self::USER_ROLE_SUPER_ADMIN:
                $description = 'Супер Администратор';
                break;
            case self::USER_ROLE_ADMIN:
                $description = 'Администратор';
                break;
            case self::USER_ROLE_SUPER_MANAGER:
                $description = 'Супер Менеджер';
                break;
            case self::USER_ROLE_MANAGER:
                $description = 'Менеджер';
                break;
            default:
                $description = 'Специалист';
        }
        return $description;
    }

    /**
     * Проверят, является ли пользователь супер админом
     * @return bool
     */
    public static function isSuperAdmin()
    {
        $permission = self::getPermission();
        if ($permission == self::USER_ROLE_SUPER_ADMIN) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверят, является ли пользователь админом
     * @return bool
     */
    public static function isAdmin()
    {
        $permission = self::getPermission();
        if ($permission == self::USER_ROLE_ADMIN) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверят, является ли пользователь супер менеджером
     * @return bool
     */
    public static function isSuperManager()
    {
        $permission = self::getPermission();
        if ($permission == self::USER_ROLE_SUPER_MANAGER) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверят, является ли пользователь менеджером
     * @return bool
     */
    public static function isManager()
    {
        $permission = self::getPermission();
        if ($permission == self::USER_ROLE_MANAGER) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверят, является ли пользователь специалистом
     * @return bool
     */
    public static function isSpecialist()
    {
        $permission = self::getPermission();
        if ($permission == self::USER_ROLE_SPECIALIST) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получает текущие права (permission)
     * @return mixed
     */
    public static function getPermission()
    {
        return self::findOne(Yii::$app->user->id)->permission ?? false;

    }

    /**
     * Получает права (permission) пользователя
     * @param int $id Пользователя
     * @return string|null
     */
    public static function getPermissionForUser($id)
    {
        return self::findOne($id)->permission ?? null;

    }

    /**
     * Получает всех служащих по их должности, если передаетс ID компании, то получет всех специлистов компании
     * @param string $position Роль/должность пользователя
     * @param int $company_id ID Сомпании
     * @return array
     */
    public static function getListByPosition($position, $company_id = null)
    {
        $query = self::find()->andWhere(['permission' => $position]);

        if ($company_id) {
            $query->andWhere(['company_id' => $company_id]);
        }

        $models = $query->all();
        $specialists = [];
        foreach ($models as $model) {
            $specialists[$model->id] = self::getShortName($model->id);
        }
        Yii::info($specialists, 'test');
        return $specialists;
    }

    /**
     * Возвращает фимилию и инициалы
     *
     * @param int $id ID пользователя
     * @return string
     */
    public static function getShortName($id)
    {
        $model = Users::findOne($id) ?? null;
        if (!$model) {
            return null;
        }
        if (isset($model->fio)) {
            try {
                $part_fio = explode(' ', $model->fio);
                $n = mb_strtoupper(mb_substr($part_fio[1], 0, 1)); //Получаем первую букву имени
                $mn = mb_strtoupper(mb_substr($part_fio[2], 0, 1)); //Первую букву отчества
                return $part_fio[0] . ' ' . $n . '.' . $mn . '.';
            } catch (\Exception $e) {
//                Yii::warning('Не удалось преобразовать ФИО в Фамилию и инициалы. ' . $e->getMessage(), 'warning');
                return $model->fio;
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public static function getCompanyIdForUser()
    {
        $model = self::findOne(Yii::$app->user->id) ?? null;

        return $model->company->id ?? null;
    }

    /**
     * Получает пользователей, не привязанных к компании
     * @return array
     */
    public static function getFreeUsers()
    {
        $free_users = self::find()->andWhere(['IS', 'company_id', null])->all();
        return ArrayHelper::map($free_users, 'id', 'fio');
    }

    /**
     * Получает пользователей компании + пользователей без компании
     * @param int $id ID Компании
     * @return array
     */
    public static function getUsersByCompanyAndFreeUsers($id)
    {
        $users = self::find()
            ->orWhere(['IS', 'company_id', null])
            ->orWhere(['company_id' => $id])
            ->all();

        return ArrayHelper::map($users, 'id', 'fio');
    }

    /**
     * Получает список сотрудников компании
     * Обязательно возвращаем массив только ID пользователей, т.к. его принимает Select2 с множественным выбором
     * @param int $id ID Компании
     * @return array
     */
    public static function getUsersByCompany($id)
    {
        $company_users = [];
        $result = self::find()->andWhere(['company_id' => $id])->all() ?? null;

        foreach ($result as $user) {
            array_push($company_users, $user->id);
        }
        return $company_users;
    }
}
