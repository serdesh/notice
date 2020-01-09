<?php

namespace app\models;

use app\models\query\EmailSettingsQuery;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use PhpImap\Mailbox;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "email_settings".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type 0-IMAP, 1-POP
 * @property string $incoming_server Сервер входящей почты
 * @property int $incoming_port Порт для входящих сообщений
 * @property string $smtp_server Сервер исходящей почты
 * @property string $smtp_port Порт сервера исходящей почты
 * @property int $protection Защита соединения/Протокол шифрования
 * @property string $login Логин от почтового ящика
 * @property string $password Пароль от почтового ящика
 * @property int $checked Флаг проверки ящика на работоспособность
 * @property int $email
 * @property int $as_default Использовать по умолчанию. Если 0 - использовать ящик УК
 *
 * @property Users $user
 */
class EmailSettings extends ActiveRecord
{

    public $email;
    public $connect_errors;
    const EMAIL_TYPE_IMAP = 0;
    const EMAIL_TYPE_POP = 1;
    const EMAIL_PROTECTION_SSL = 10;
    const EMAIL_PROTECTION_TLS = 11;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'incoming_port', 'protection', 'checked', 'as_default'], 'integer'],
            [['incoming_server', 'smtp_server', 'smtp_port'], 'string'],
            [['login', 'password'], 'string', 'max' => 255],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
            [['email'], 'email'],
            [
                [
                    'type',
                    'incoming_port',
                    'protection',
                    'incoming_server',
                    'smtp_server',
                    'smtp_port',
                    'login',
                    'password',
                    'email'
                ],
                'required'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Тип сервера входящей почты',
            'incoming_server' => 'Сервер входящей почты',
            'incoming_port' => 'Порт для входящих сообщений',
            'smtp_server' => 'Сервер исходящей почты',
            'smtp_port' => 'Порт сервера для исходящих сообщений',
            'protection' => 'Защита соединения/Протокол шифрования',
            'login' => 'Логин от почтового ящика',
            'password' => 'Пароль от почтового ящика',
            'checked' => 'Флаг проверки ящика на работоспособность',
            'email' => 'Email',
            'as_default' => 'Использовать по умолчанию',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        Yii::info($this->toArray(), 'info');

        if ($insert) {
            $user_model = Users::findOne($this->user_id) ?? null;
            if ($user_model) {
                if (!$user_model->email) {
                    $user_model->email = $this->email;
                }
                if (!$user_model->save(false)) {
                    Yii::error($user_model->errors, 'error');
                }

            }

        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return EmailSettingsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EmailSettingsQuery(get_called_class());
    }

    /**
     * Проверяет подключение к электронному ящику
     * @return null
     * @throws InvalidParameterException
     */
    public function checkSettings()
    {
        Yii::info('Проверка подключения', 'test');
        $type = (new EmailSettings)->getType($this->type);
        $protection = (new EmailSettings)->getProtection($this->protection);
        $username = $this->login;
        $password = $this->password;
        $mail_server = $this->incoming_server;
        $mail_dir = Url::to('@app/mail/users');
        $mail_port = $this->incoming_port;
        $mail_imap_path = '{' . $mail_server . ':' . $mail_port . '/' . $type . '/' . $protection . '}INBOX';
        Yii::info('Строка подключения: ' . $mail_imap_path, 'test');

        try {
            $mailbox = new Mailbox($mail_imap_path, $username, $password, $mail_dir);
            $mailbox->getImapStream();
        } catch (ConnectionException $e) {
            Yii::error($e->getMessage(), 'error');
            return ['mail_parameter_error' => $e->getMessage()];
        }

        return null;
    }

    /**
     * Получает наименование типа протокола email
     * @param $type
     * @return null|string
     */
    public function getType($type)
    {
        if ($type == self::EMAIL_TYPE_IMAP) {
            return 'imap';
        }
        if ($type == self::EMAIL_TYPE_POP) {
            return 'pop3';
        }
        return null;
    }

    /**
     * Получает наименование защиты/шифрования email сервера
     * @param $protection
     * @return null|string
     */
    public function getProtection($protection = null)
    {
        if (!$protection) {
            $protection = $this->protection;
        }
        if ($protection == self::EMAIL_PROTECTION_SSL) {
            return 'SSL';
        }
        if ($protection == self::EMAIL_PROTECTION_TLS) {
            return 'TLS';
        }
        return null;
    }

    /**
     * Получает адрес отправителя, учитываю настройку почты "Использовать по умолчанию"
     * @param int $id ID обращения
     * @param int $manager_id
     * @return EmailSettings
     */
    public static function getEmailSettingsForPetition($id, $manager_id = null)
    {
        $petition_model = Petition::findOne($id) ?? null;
        $user_id = $manager_id;

        if (!$petition_model) {
            return null;
        }

        $company_id = $petition_model->company->id;

        if (!$user_id) {
            $user_id = $petition_model->manager->id ?? null;
        }
        if (!$user_id) {
            //Если нет менеджера, берем настройки почты компании
            $user_id = Users::find()
                    ->andWhere(['company_id' => $company_id])
                    ->andWhere(['permission' => Users::USER_ROLE_ADMIN])
                    ->one()->id ?? null;
        } else {
            Yii::info('Менеджер: ' . $user_id, 'test');
        };
        //Получаем настрйоки почты менеджера
        $email_settings_model = EmailSettings::find()->andWhere((['user_id' => $user_id]))->one() ?? null;
        if (!$email_settings_model) {
           //Если настройки не найдены возвращаем настройки почты компании
            /** @var Users $admin_company */
            $admin_company = Users::find()
                ->andWhere(['company_id' => $company_id])
                ->andWhere(['permission' => Users::USER_ROLE_ADMIN])
                ->one();
            return EmailSettings::findOne(['user_id' => $admin_company->id]);
        }
        Yii::info('Настрокий почты: ' . $email_settings_model->id, 'test');

        $is_default = $email_settings_model->as_default ?? null;
        Yii::info('Почта по умолчанию: ' . $is_default, 'test');

        if ($is_default) {
            //Если почта менеджера отмечена для использования по умолчанию
            Yii::info('Выбрана почта менеджера', 'test');
            return $email_settings_model;
        } else {
            Yii::info('Выбрана почта компании', 'test');
            //Возвращаем настройки почты админа компании
            /** @var Users $company_id */
            $user = Users::findOne($user_id) ?? null;

            if (!$user) {
                return null;
            }
            Yii::info('Пользователь: ' . $user->id, 'test');

            /** @var Users $admin_company */
            $admin_company = Users::find()
                    ->andWhere(['company_id' => $user->company_id])
                    ->andWhere(['permission' => Users::USER_ROLE_ADMIN])
                    ->one() ?? null;

            if (!$admin_company) {
                return null;
            }
            Yii::info('Админ компании: ' . $admin_company->id, 'test');

            return EmailSettings::find()->andWhere(['user_id' => $admin_company->id])->one() ?? null;
        }

    }

    /**
     * Получает email пользователя
     * @return null|string
     */
    public function getEmailAddress()
    {
        return Users::findOne($this->user_id)->email ?? null;
    }

    /**
     * Получает сервер из почтового адерса
     * @param string $email Адрес электронной почты
     * @return string
     */
    public static function getServer($email)
    {
        $part_mail = explode('@', $email);
        return $part_mail[1];
    }
}
