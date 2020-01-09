<?php

namespace app\models;

use app\models\query\MessageQuery;
use app\modules\drive\models\Google;
use app\modules\drive\models\Yandex;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;


/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property string $header Заголовок
 * @property string $text Содержание
 * @property int $petition_id ID Заявки
 * @property string $created_at
 * @property string $outer_id ID Email сообщения на почтовом сервере
 * @property string $email_date Дата сообщения на почтовом сервере
 * @property string $attachments Путь к папке с вложениями
 * @property string $from От кого пришло сообщение
 * @property int $is_incoming Входящее или нет
 *
 * @property Petition $petition
 */
class Message extends ActiveRecord
{

    public $type;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['text', 'outer_id'], 'string'],
            [['petition_id', 'is_incoming'], 'integer'],
            [['created_at', 'email_date'], 'safe'],
            [['header'], 'string', 'max' => 255],
            [
                ['petition_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Petition::className(),
                'targetAttribute' => ['petition_id' => 'id']
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
            'header' => 'Заголовок',
            'text' => 'Содержание',
            'petition_id' => 'ID обращения',
            'created_at' => 'Дата',
            'from' => 'От кого',
            'outer_id' => 'ID сообщения на почтовом сервере',
            'email_date' => 'Дата сообщения на почтовом сервере',
            'is_incoming' => 'Вх./Исх.',
            'attachments' => 'Вложения',
        ];
    }

    public function beforeDelete()
    {
        //Удаляем вложения с сервера или из облака
        $drive = Settings::getValueByKeyFromCompany('drive_type', Users::getCompanyIdForUser());
        $attachments = $this->attachments;
        Yii::info('Attachments: ' . $attachments, 'test');

        if ($drive) {
            //Вложения хранятся в облаке
            if ($drive == 'yandex') {
                $result = Yandex::deleteDirectory($this->attachments);
                Yii::info($result, 'test');
            } elseif ($drive == 'google') {
                try {
                    Google::deleteFile($this->attachments);
                } catch (\Google_Exception $e) {
                    Yii::error($e->getMessage(), '_error');
                }
            }
        } else {
            //Вложения хранятся локально
            $path_dir = $this->attachments;
            Yii::info($path_dir, 'test');
            if (!Functions::deleteDirectory($path_dir)) {
                Yii::warning('Ошибка удаления директории ' . $path_dir, 'warning');
            }
        }

        return parent::beforeDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetition()
    {
        return $this->hasOne(Petition::className(), ['id' => 'petition_id']);
    }

    /**
     * {@inheritdoc}
     * @return MessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }

    /**
     * @param string $path Путь к папке с файлами
     * @return string
     */
    public static function getFiles($path)
    {
        $dir_path = Url::to('@webroot'. $path);
        Yii::info($dir_path, 'test');
        Yii::info(is_dir($dir_path), 'test');
        if (is_dir($dir_path)) {
            $files = array_diff(scandir($dir_path), array('..', '.')) ?? null;
        } else {
            return 'Нет вложений';
        }

        if (!$files) {
            return 'Нет вложений';
        }
        $content = '';


        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $name_parts = explode('_', $file);
            $cutted_file_name = array_pop($name_parts);

            $content .= Html::a($cutted_file_name,
                    Url::to(['/message/download-file', 'path_file' => $path . '/' . $file]), [
                        'data-pjax' => 0,
                    ]) . '<br>';
        }

        return $content;
    }

    /**
     * @param int $id ID сообщения
     * @param string $recipient получатель сообщения
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     */
    public function forwardMessage($id, $recipient)
    {
        $message_model = self::findOne($id);
        $petition_id = $message_model->petition_id ?? null;

        Yii::info('Сообщение: ' . $message_model->header, 'test');
        Yii::info('Обращение: ' . $petition_id, 'test');

        $email_settings = EmailSettings::getEmailSettingsForPetition($petition_id);

        if (!$email_settings) {
            Yii::error('Не найдены настройки почты для отправки сообщения', '_error');
            return false;
        } else {
            Yii::info('Настройки почты', 'test');
            Yii::info($email_settings->toArray(), 'test');
        }

        $from = $email_settings->getEmailAddress() ?? null;
        if (!$from) {
            Yii::error('Не найден отправитель', '_error');
            return false;
        }
        Yii::info('От кого: ' . $from, 'test');

        $subject = $message_model->header;
        $text = $message_model->text;

        Yii::info('Тема: ' . $subject, 'test');
        Yii::info('Текст: ' . $text, 'test');

        /** @var yii\swiftmailer\Mailer $mailer */
        $mailer = Yii::createObject([
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => $email_settings->smtp_server,
                'username' => $email_settings->login,
                'password' => $email_settings->password,
                'port' => $email_settings->smtp_port,
                'encryption' => $email_settings->getProtection(),
            ],
        ]);
        Yii::info($mailer, 'test');

        try {
            $prep_mailer = $mailer->compose()
                ->setFrom($from)
                ->setTo($recipient)
                ->setSubject($subject);

            $drive = Settings::getDrive();
            if (!$drive) {
                //Если файлы хранятся локально - прикрепляем к сообщению
                $files = array_diff(scandir($message_model->attachments), array('..', '.'));
                Yii::info($files, 'test');
                foreach ($files as $file) {
                    $prep_mailer->attach($message_model->attachments . '/' . $file);
                }
            } else {
                //Если вложения в облаке, отправляем ссылку
                if ($drive == 'yandex'){
                   $text .= '<br> Ссылка для скачивания вложений: ' .  Yandex::getFilesInDirectory($message_model->attachments);
                } elseif ($drive == 'google'){
                    $text .= '<br>' . Html::a('Скачать вложенные файлы', 'https://drive.google.com/open?id=' . $message_model->attachments, [
                        'class' => 'btn btn-primary',
                        'target' => '_blank',
                    ]);
                }
            }
            $prep_mailer->setHtmlBody($text);
            $result = $prep_mailer->send();
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), '_error');
            $result = false;
        }
        if ($result) {
            Yii::info('Письмо на ' . $recipient . ' успешно отправлено: ' . (string)$result, 'test');
            return true;
        }
        return false;

    }
}
