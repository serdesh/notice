<?php

namespace app\models;

use app\models\query\CompanyQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $name
 * @property string $director
 * @property string $inn ИНН
 * @property int $contact_id
 * @property string $password
 * @property int $enabled
 * @property string $notes Примечания
 *
 * @property Contact $contact
 * @property Users[] $users
 */
class Company extends ActiveRecord
{
    public $new_password;
    public $houses;
    public $test;
    public $employee;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact_id', 'enabled'], 'integer'],
            [['name', 'director', 'password'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 50],
            [
                ['contact_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Contact::className(),
                'targetAttribute' => ['contact_id' => 'id']
            ],
            [['houses', 'notes'], 'safe'],
            [
                ['password', 'new_password'],
                'match',
                'pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{6,}$/',
                'message' => 'Пароль не соответствует требованиям!',
            ],
            [['name', 'inn', 'password'], 'required'],
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
            'director' => 'Директор',
            'inn' => 'ИНН',
            'contact_id' => 'ID контактов',
            'password' => 'Пароль',
            'new_password' => 'Новый пароль',
            'enabled' => 'Включен',
            'notes' => 'Примечание',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            //Создаем учетку админа для компании до хеширования пароля
            $user = new Users();
            $user->fio = 'Администратор. ' . $this->name;
            $user->permission = Users::USER_ROLE_ADMIN;
            $user->inn = $this->inn;
            $user->password = $this->password;
            $user->created_by = Yii::$app->user->id;
            $user->login = 'admin';
            if (!$user->save(false)) {
                Yii::error($user->errors, 'error');
            }

            $this->password = md5($this->password);
        }

        if ($this->new_password != null) {
            $this->password = md5($this->new_password);
            //Также меняем пароль у админов компании
            $admin_models = Users::find()
                ->andWhere(['permission' => Users::USER_ROLE_ADMIN])
                ->andWhere(['company_id' => $this->id])
                ->all();
            /** @var Users $admin_model */
            foreach ($admin_models as $admin_model){
                $admin_model->password = $this->password;
                if (!$admin_model->save(false)){
                    Yii::error($admin_model->errors, '_error');
                }
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            //Добавляем только что добавленому админу ID компании
            /** @var Users $user */
            $user = Users::find()->andWhere(['inn' => $this->inn])->one() ?? null;

            if ($user->id) {
                $user->company_id = $this->id;
                if (!$user->save(false)) {
                    Yii::error($user->errors, 'error');
                }
            } else {
                Yii::error('Пользователь с ИНН ' . $this->inn . ' не найден', '_error');
            }

        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['company_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return CompanyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CompanyQuery(get_called_class());
    }

    /**
     * Получает список компаний
     * @return array
     */
    public static function getList()
    {
        if (Users::isSuperAdmin()) {
            return ArrayHelper::map(self::find()->all(), 'id', 'name');
        }

        $company_id = Users::findOne(['id' => Yii::$app->user->id])->company_id ?? null;

        if ($company_id) {
            return ArrayHelper::map(self::find()
                ->andWhere(['id' => $company_id])
                ->all(), 'id', 'name');
        }

        return [];
    }
}
