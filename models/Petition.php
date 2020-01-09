<?php

namespace app\models;

use app\models\query\PetitionQuery;
use app\modules\drive\models\Google;
use app\modules\drive\models\Yandex;
use PhpImap\Exceptions\InvalidParameterException;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "petition".
 *
 * @property int $id
 * @property string $header Наименование заявки
 * @property string $text Содержание заявки
 * @property int $status_id ID статуса заявки
 * @property int $specialist_id
 * @property int $manager_id
 * @property int $resident_id Заявитель
 * @property string $where_type Откуда пришла заявка (Эл. почта, заведена вручную, из виджета)
 * @property int $relation_petition_id ID связанной заявки
 * @property string $execution_date Дата исполнения заявки
 * @property int $created_by ID создавшего заявку
 * @property string $created_at Дата создания
 * @property string $answer Ответ заявителю
 * @property int $closed_user_id Пользователь, закрывший заявку
 * @property int $petition_type Тип обращения
 * @property string $address
 * @property int $trouble_id
 * @property int $trouble_description Описание неисправности
 * @property int $email_id Email заявителя, для переписки по обращению
 * @property int $company_id Компания, к которой пренадлежит обращение
 *
 * @property Message[] $messages
 * @property Users $closedUser
 * @property Users $createdBy
 * @property Users $manager
 * @property Petition $relationPetition
 * @property Petition[] $petitions
 * @property Resident $resident
 * @property Users $specialist
 * @property Status $status
 * @property TroubleshootingPeriod $troubleShooting
 * @property Company $company
 * @property ResidentEmail $residentEmail
 */
class Petition extends ActiveRecord
{

    const PETITION_TYPE_PETITION = 0; //Обращение. В базе по умолчанию
    const PETITION_TYPE_COMPLAINT = 1; //Жалоба.
    const WHERE_TYPE_MANUAL_INPUT = 'manual'; //Ручной ввод заявки
    const WHERE_TYPE_EMAIL = 'email'; //Электронная почта
    const WHERE_TYPE_WIDGET = 'widget'; //Прислано с использованием виджета

    public $house;
    public $apartment;
    public $filter_state;
    public $additional_info; //Доп. информация о жильце.
    public $company_id;
    public $call_id; //Звонок
    public $recipient; //Получатель перенаправляемого обращения


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'petition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['text', 'answer', 'address', 'trouble_description'], 'string'],
            [
                [
                    'status_id',
                    'specialist_id',
                    'manager_id',
                    'resident_id',
                    'relation_petition_id',
                    'created_by',
                    'closed_user_id',
                    'petition_type',
                    'house',
                    'apartment',
                    'trouble_id',
                    'call_id',
                    'email_id',
                ],
                'integer'
            ],
            [['execution_date', 'created_at', 'additional_info'], 'safe'],
            [['header', 'where_type'], 'string', 'max' => 255],
            [
                ['resident_id'],
                'required',
                'skipOnEmpty' => true,
                'message' => 'Необходимо выбрать жильца'
            ],
            [['header'], 'required'],
            [
                ['closed_user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::className(),
                'targetAttribute' => ['closed_user_id' => 'id']
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
            [
                ['manager_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::className(),
                'targetAttribute' => ['manager_id' => 'id']
            ],
            [
                ['relation_petition_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Petition::className(),
                'targetAttribute' => ['relation_petition_id' => 'id']
            ],
            [
                ['resident_id'],
                'exist',
//                'skipOnError' => true,
                'skipOnEmpty' => true,
                'targetClass' => Resident::className(),
                'targetAttribute' => ['resident_id' => 'id']
            ],
            [
                ['specialist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::className(),
                'targetAttribute' => ['specialist_id' => 'id']
            ],
            [
                ['status_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Status::className(),
                'targetAttribute' => ['status_id' => 'id']
            ],
            ['recipient', 'email', 'message' => 'Некорректный email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'header' => 'Тема обращения',
            'text' => 'Содержание обращения',
            'status_id' => 'ID статуса обращения',
            'specialist_id' => 'ID Специалиста',
            'manager_id' => 'ID Менеджера',
            'resident_id' => 'Заявитель',
            'where_type' => 'Откуда пришло обращение (Эл. почта, заведена вручную, из виджета)',
            'relation_petition_id' => 'ID связанноого обращения',
            'execution_date' => 'Дата исполнения обращения',
            'created_by' => 'ID создавшего обращение',
            'created_at' => 'Дата создания',
            'answer' => 'Ответ заявителю',
            'closed_user_id' => 'Пользователь, закрывший обращение',
            'petition_type' => 'Тип обращения',
            'address' => 'Адрес',
            'house' => 'Дом',
            'apartment' => 'Квартира',
            'trouble_id' => 'ID Неисправности',
            'trouble_description' => 'Описание Неисправности',
            'call_id' => 'ID звонка',
            'email_id' => 'Email для ответа',
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \Exception
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if (!$this->created_by) {
                $this->created_by = Yii::$app->user->id ?? null;
            }
            $this->status_id = 1; //Новое
            Yii::info('Created by: ' . $this->created_by, 'test');
            Yii::info('Petition status: ' . $this->petition_type, 'test');

            //Если неисправность выбрана из списка - проставляем дату исполнения обращения
            if ($this->trouble_id) {
                $this->execution_date = TroubleshootingPeriod::getExecutionDate($this->trouble_id);
            }
        }

        //Если указана доп инфомация - сохраняем её жильцу
        if ($this->additional_info) {
            $resident_model = Resident::findOne($this->resident_id);
            $resident_model->additional_info = $this->additional_info;
            $resident_model->save();
        }

        //Преобразуем дату и время для записи в базу
        if ($this->execution_date) {
            $this->execution_date = Functions::getDateTimeForBase($this->execution_date);
        }

        if ($this->getOldAttribute('status_id') != $this->status_id) {
            Functions::addToHistory($this->id, 'Изменение статуса',
                'Обращению присвоен статус: ' . $this->status->name);
        }
        if ($this->getOldAttribute('manager_id') != $this->manager_id) {
            Functions::addToHistory($this->id, 'Назначение сотрудника',
                'Обращению назначен менеджер: ' . $this->manager->fio);
        }
        if ($this->getOldAttribute('specialist_id') != $this->specialist_id) {
            Functions::addToHistory($this->id, 'Назначение сотрудника',
                'Обращению назначен специалист: ' . $this->specialist->fio);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $drive = Settings::getValueByKeyFromCompany('drive_type', Users::getCompanyIdForUser());
        $inn = $this->company->inn ?? '000000';
        $dir_type = '001';
        if ($this->petition_type == self::PETITION_TYPE_COMPLAINT) {
            $dir_type = '002';
        }

        $path = $inn . '/' . $dir_type . '/' . $this->id;

        if ($drive) {
            //Вложения хранятся в облаке
            if ($drive == 'yandex') {
                $result = Yandex::deleteDirectory($path);
                Yii::info($result, 'test');
            } elseif ($drive == 'google') {
                $messages = Message::find()->andWhere(['petition_id' => $this->id])->all();
                foreach ($messages as $message) {
                    try {
                        Google::deleteFile($message->attachments);
                    } catch (\Google_Exception $e) {
                        Yii::error($e->getMessage(), '_error');
                    }
                }
            }
        } else {
            //Вложения хранятся локально
            $path_dir = Url::to('@webroot/' . Yii::$app->name . '/' . $path);
            Yii::info($path_dir, 'test');
            if (!Functions::deleteDirectory($path_dir)) {
                Yii::warning('Ошибка удаления директории ' . $path_dir, 'warning');
            }
        }

        return parent::beforeDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

    }

    /**
     * @return ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['petition_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClosedUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'closed_user_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Users::className(), ['id' => 'created_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(Users::className(), ['id' => 'manager_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRelationPetition()
    {
        return $this->hasOne(Petition::className(), ['id' => 'relation_petition_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPetitions()
    {
        return $this->hasMany(Petition::className(), ['relation_petition_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getResident()
    {
        return $this->hasOne(Resident::className(), ['id' => 'resident_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Users::className(), ['id' => 'specialist_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'status_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTroubleShooting()
    {
        return $this->hasOne(TroubleshootingPeriod::className(), ['id' => 'trouble_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getResidentEmail()
    {
        return $this->hasOne(ResidentEmail::class, ['id' => 'email_id']);
    }

    public function getCall()
    {
        return $this->hasOne(Call::class, ['petition_id' => 'id']);

    }

    /**
     * {@inheritdoc}
     * @return PetitionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PetitionQuery(get_called_class());
    }

    /**
     * Получает название типа обращения
     * @return null|string
     */
    public function getPetitionType()
    {
        if ($this->petition_type == self::PETITION_TYPE_PETITION) {
            return 'Обращение';
        }
        if ($this->petition_type == self::PETITION_TYPE_COMPLAINT) {
            return 'Жалоба';
        }
        return null;
    }

    /**
     * Получает список типов обращений
     * @return array
     */
    public function getTypeList()
    {
        return ArrayHelper::map([
            ['id' => self::PETITION_TYPE_PETITION, 'name' => 'Обращение',],
            ['id' => self::PETITION_TYPE_COMPLAINT, 'name' => 'Жалоба',],
        ], 'id', 'name');
    }

    /**
     * Получает название типа "ввода" обращения
     * @return string
     */
    public function getWhereType()
    {
        Yii::info('Where type: ' . $this->where_type, 'test');
        if ($this->where_type == self::WHERE_TYPE_MANUAL_INPUT) {
            return 'Ручной ввод';
        }
        if ($this->where_type == self::WHERE_TYPE_EMAIL) {
            return 'Электронная почта';
        }
        if ($this->where_type == self::WHERE_TYPE_WIDGET) {
            return 'Виджет';
        }
        return 'Не найдено';
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id'])
            ->viaTable('users', ['id' => 'created_by']);
    }

    /**
     * Получает все обращения без ссылки на другое обращение
     */
    public static function getRootPetitionsList()
    {
        $root_petitions = self::find()
                ->joinWith(['company'])
                ->andWhere(['IS', 'relation_petition_id', null])
                ->andWhere(['company.id' => Users::getCompanyIdForUser()])
                ->andWhere(['IN', 'status_id', [1, 2]])
                ->all() ?? null;

        return ArrayHelper::map($root_petitions, 'id', 'header');
    }

    /**
     * Получает доп информацию о жильце
     * @param int $id ID Жильца
     * @return string
     */
    public static function getAdditionalInfo($id)
    {
        return Resident::findOne($id)->additional_info ?? null;
    }

    /**
     * Получает из эелектроннго ящика обращения для компании
     * @param int $id ID компании
     * @return string
     * @throws InvalidParameterException
     * @throws \Exception
     */
    public function getMailFromCompany($id)
    {
        $result = '';
        //Получаем настройки для каждого ящика, принадлежащего компании
        foreach (EmailSettings::find()
                     ->joinWith(['user u'])
                     ->andWhere(['u.company_id' => $id])
                     ->andWhere(['as_default' => 1])//Ящики пользователй, использующих свои ящики
            ->each() as $email) {

            Yii::info($email->toArray(), 'test');

            $email_address = Users::findOne($email->user_id)->email ?? null;

            if (!$email_address) {
                continue;
            }

            //Получаем письма
            $result .= $this->getEmails($email) . PHP_EOL;

//            if ($result) {
//                return $result;
//            }
//
//            return null;
        }
        return $result;
    }

    /**
     * Получает письма для ящика
     * @param EmailSettings $email_setting
     * @return string
     * @throws InvalidParameterException
     * @throws \Exception
     */
    public function getEmails(EmailSettings $email_setting)
    {
        Yii::info('Получение письма', 'test');
        $type = (new EmailSettings)->getType($email_setting->type);
        $protection = (new EmailSettings)->getProtection($email_setting->protection);
        $username = $email_setting->login;
        $password = $email_setting->password;
        $mail_server = $email_setting->incoming_server;
        $mail_dir = Url::to('@app/mail/users');
        $mail_port = $email_setting->incoming_port;
        $mail_imap_path = '{' . $mail_server . ':' . $mail_port . '/' . $type . '/' . $protection . '}INBOX';

        $company_id = Users::findOne($email_setting->user_id)->company_id ?? null;
        if (!$company_id) {
            return 'Не найдена компания для настроек почты';
        }
        $company_model = Company::findOne($company_id) ?? null;

        try {
            $mailbox = new Mailbox($mail_imap_path, $username, $password, $mail_dir);
        } catch (InvalidParameterException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new InvalidParameterException($e->getMessage());
        }
        //Проверяем подключение
        if (!$this->checkStream($mailbox)) {
            return 'Ошибка подключения';
        }
        Yii::info('Подключено', 'test');
        //Проверяем есть ли не прочитанные
        $mail_check = (array)$mailbox->imap('mailboxmsginfo');
        if ((int)$mail_check['Unread'] <= 0) {
            //Если нет - выходим
            Yii::info('Непрочитанных: ' . (int)$mail_check['Unread'], 'test');
            return 'Все сообщения прочитаны';
        }

        //Получаем ID непрочитанных сообщений
        $mail_ids = $mailbox->searchMailbox('UNSEEN');

        Yii::info('ID непрочитанных писем', 'test');
        Yii::info($mail_ids, 'test');

        foreach ($mail_ids as $mail_id) {
            Yii::info('ID письма: ' . $mail_id, 'test');

            //Получаем экземпляр объекта класса IncomingMail который содержит информацию о сообщении.
            /** @var IncomingMail $mail */
            $mail = $mailbox->getMail($mail_id);

            $head = $mailbox->getMailHeader($mail_id);
            $subject = $head->subject;
            Yii::info('Тема письма: ' . $subject, 'test');
            $message_model = new Message();
            $message_model->header = $subject;
//            $message_model->text = strip_tags($mail->textPlain);
            $html = $mail->textHtml;
            $plain = $mail->textPlain;
            if ($html) {

                $message_model->text = $html;
            } else {
                $message_model->text = $plain;
            }
            Yii::info('Plain text: ' . $mail->textPlain, 'test');
            Yii::info('HTML text: ' . $mail->textHtml, 'test');
            $message_model->outer_id = (string)$mail_id;
            $message_model->email_date = $mail->date;
            $message_model->from = $mail->fromAddress;

            //Проверяем есть ли номер обращения в теме сообщения
            $is_reply = strpos($subject, 'ращение[0');

            Yii::info('Переписка по обращению?: ' . $is_reply, 'test');

            if (!$is_reply) {
                //Если письмо не относится к переписке по обращению, создаем обращение
                //Тема сообщения в ответ на обращение или ответ на ответ на обращение Обращение[00000000123]
                Yii::info('Создание обращения', 'test');

                $petition_model = new Petition();

                if (strpos($head->subject, 'Обращение №') === false) {
                    //Письмо - не относится к существующему обращению, создаем обращение
                    //Ищем жильца по email`у отправителя письма
                    $resident_email_model = ResidentEmail::find()
                            ->andWhere(['email' => $mail->fromAddress])
                            ->one() ?? null;

                    if ($resident_email_model) {
                        Yii::info($resident_email_model->toArray(), 'test');
                        $petition_model->resident_id = $resident_email_model->resident_id;
                        $resident = Resident::findOne($petition_model->resident_id);
                        $petition_model->address = $resident->apartment->getFullAddress();
                        $petition_model->email_id = $resident_email_model->id;
                    }
                    $petition_model->company_id = $email_setting->user->company_id;
                    $petition_model->header = $message_model->header ?? 'Без темы';
                    $petition_model->text = $message_model->text ?? 'Текст отсутствует';
                    $petition_model->where_type = self::WHERE_TYPE_EMAIL;
                    $petition_model->status_id = 1; //Новое
                    $petition_model->created_by = $email_setting->user_id; //ID владельца ящика

                    Yii::info($petition_model->toArray(), 'test');
                } else {
                    //Письмо относится обращению
                    //Получаем номер обращения
                    $start = strpos($subject, '[');
                    $end = strrpos($subject, ']');
                    $petition_number = (int)substr($subject, $start, $end - $start);
                    Yii::info('номер обращения: ' . $petition_number, 'test');

                    //Ищем обращение
                    $petition_model = Petition::findOne($petition_number) ?? null;
                    if (!$petition_model) {
                        Yii::error($petition_model->errors, 'error');
                        return 'Обращение с номером ' .
                            str_pad($petition_number, 11, '0', STR_PAD_LEFT) .
                            ' не найдено.';
                    }
                }

                if (!$petition_model->save()) {
                    $mailbox->markMailAsUnread($mail_id); //Помечаем как непрочитанное
                    Yii::error($petition_model->errors, 'error');
                    return 'Ошибка сохранения обращения';
                }
                $message_model->petition_id = $petition_model->id;
            } else {
                //Получаем ID обращения из заголовка сообщения
                preg_match('/\[(.+)\]/', $message_model->header, $str);
                Yii::info($str, 'test');
                $message_model->petition_id = (int)$str[1];
            }

            if (!isset($petition_model)) {
                $petition_model = Petition::findOne($message_model->petition_id);
            }
            $dir_type = '001';
            if (isset($petition_model->petition_type) && $petition_model->petition_type = self::PETITION_TYPE_COMPLAINT) {
                $dir_type = '002';
            }

            if (!$message_model->save()) {
                $mailbox->markMailAsUnread($mail_id); //Помечаем как прочитанное
                Yii::error($message_model->errors, 'error');
                return 'Ошибка сохранения сообщения';
            }
            $mailbox->markMailAsRead($mail_id); //Помечаем как прочитанное

            //Путь к папке в облаке
            $path_attachments_cloud_directory = $company_model->inn
                . '/' . $dir_type
                . '/' . $message_model->petition_id
                . '/' . $message_model->id;

//            $path_attachments_local_directory = Url::to('@webroot/' . Yii::$app->name . '/' . $path_attachments_cloud_directory);
            $path_attachments_local_directory = '/' . Yii::$app->name . '/' . $path_attachments_cloud_directory;

            //Получаем файлы вложенные к данному сообщению, если они есть.
            if ($mail->hasAttachments()) {
                $attachments = $mail->getAttachments();
                Yii::info('Путь сохранения вложений: ' . $path_attachments_local_directory, 'test');

                foreach ($attachments as $key => $attachment) {
                    Yii::info('Путь к сохраненному файлу: ' . $attachment->filePath, 'test');
                    //Перемещаем вложение
                    $file_name = basename($attachment->filePath);
                    $dir = Url::to('@webroot' . $path_attachments_local_directory);
                    Yii::info($dir, 'test');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $move_result = rename($attachment->filePath, $dir . '/' . $file_name);
                    Yii::info('Результат перемещения: ' . $move_result, 'test');
                }
                $message_model->attachments = $path_attachments_local_directory;
                if ($html && strpos($html, '<img src="cid:') > 0) {
                    //Ищем ссылки на картинки в теле письма и заменяем их на локальные сслыки к файлам картинок

                    $html = $this->getHtmlWithImage($message_model);
                    $message_model->text = $html;
                    $message_model->save();
                }
            }

            //Проверяем настройки хранения файлов в облаке
            $drive = Settings::getDrive();

            $path_attachments_directory = $message_model->attachments; //Путь к папке с сохраненными файлами-вложениями

            Yii::info($message_model->getAttributes(), 'test');

            if ($drive == 'yandex' || $drive == 'google') {

                $files = scandir($path_attachments_directory);

                Yii::info($files, 'test');

                //Получаем список файлов
                $files = array_diff($files, array('..', '.'));
                Yii::info($files, 'test');

                foreach ($files as $key => $file_name) {
                    $destination_path_file = $path_attachments_cloud_directory . '/' . $file_name;
                    $source_path_file = $path_attachments_directory . '/' . $file_name;
                    if ($drive == 'yandex') {
                        Yandex::sendFile($source_path_file, $destination_path_file);
                        $message_model->attachments = $path_attachments_cloud_directory;
                        if ($message_model->attachments) {
                            if (is_file($source_path_file)) {
                                unlink($source_path_file);
                            }
                        }
                    } elseif ($drive == 'google') {
                        $message_model->attachments = Google::sendFile($source_path_file, $destination_path_file, true);
                        if ($message_model->attachments) {
                            if (is_file($source_path_file)) {
                                unlink($source_path_file);
                            }
                        }
                    }
                }
            }
            if (!$message_model->save()) {
                Yii::error($message_model->errors, '_error');
            }
        }
        return null;
    }

    /**
     * Проверяет подключение к ящику
     * @param Mailbox $mailbox
     * @return bool
     */
    public function checkStream($mailbox)
    {
        //Проверяем есть ли подключение
        try {
            $mailbox->getImapStream();
        } catch (\Exception $e) {
            Yii::error($e->getTraceAsString(), 'error');
        }
        return true;
    }

    /**
     * Получает все обращения компании
     * @param int $company_id ID УК
     * @return array
     */
    public static function getList($company_id)
    {
        $petitions = Petition::find()
                ->joinWith(['company'])
                ->andWhere(['company.id' => $company_id])
                ->all() ?? null;
        if ($petitions) {
            return ArrayHelper::map($petitions, 'id', 'header');
        }

        return [0 => 'Обращения не найдены'];
    }

    /**
     * @param array $params
     * @return null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function sendMail($params = [])
    {
//        $to = $this->resident->contact->email ?? null; //Отправка жильцу

        //Ответ на последнее письмо
        $last_message_id = Message::find()
                ->andWhere(['petition_id' => $this->id])
                ->andWhere(['is_incoming' => 1])
                ->max('id') ?? null;

        //Если в обращении указан email берем его, если нет - отвечаем на последнее сообщение
        $to = $this->residentEmail->email ?? Message::findOne($last_message_id)->from ?? null;
//            $to = Message::findOne($last_message_id)->from ?? null;

        if (!$to) {
            return 'Не найден получатель';
        }
        Yii::info('Кому: ' . $to, 'test');

        $email_settings = EmailSettings::getEmailSettingsForPetition($this->id, $this->manager_id);
        if (!$email_settings) {
            Yii::info('Не найдены настройки почты для отправки сообщения', 'test');
            return 'Не найдены настройки почты для отправки сообщения';
        } else {
            Yii::info('Настройки почты', 'test');
            Yii::info($email_settings->toArray(), 'test');
        }

        $from = $email_settings->getEmailAddress() ?? null;
        if (!$from) {
            return 'Не найден отправитель';
        }
        Yii::info('От кого: ' . $from, 'test');

        $subject = $params['subject'];
        $text = $params['text'];

        Yii::info('Тема: ' . $subject, 'test');
        Yii::info('Текст: ' . $text, 'test');

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
            $result = $mailer->compose()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->setHtmlBody($text)
                ->send();
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), '_error');
            $result = false;
        }
        if ($result) {
            Yii::info('Письмо на ' . $to . ' успешно отправлено: ' . (string)$result, 'test');
            //Пишем в messages
            $message_model = new Message();
            $message_model->header = $subject;
            $message_model->text = $text;
            $message_model->petition_id = $this->id;
            $message_model->from = $from;
            $message_model->is_incoming = 0;
            if (!$message_model->save()) {
                Yii::error($message_model->errors, '_error');
                return 'Ошибка сохранения сообщения';
            }
        }

        return null;
    }

    /**
     * @return ActiveQuery
     */
    public function getCalls()
    {
        return $this->hasMany(Call::class, ['petition_id' => 'id']);
    }

    public function getHtmlWithImage(Message $message)
    {
        $html = $message->text;
        //Получаем список файлов
        $dir = Url::to('@webroot' . $message->attachments);
        $files = array_diff(scandir($dir), array('..', '.')) ?? '';
        Yii::info($files, 'test');
        //Ищем все входжения <img src="cid:какойтотекст"
        preg_match_all('/<img src="cid:.*?[ ]/', $html, $out);
        Yii::warning($out, 'test');
        foreach ($out[0] as $link) {
            Yii::info($link);
            $parts_link = explode(':', $link);
            $image_name = substr($parts_link[1], 0, strpos($parts_link[1], '"'));
            Yii::info('Имя картинки: ' . $image_name, 'test');
            //Перебираем все вложения
            foreach ($files as $file) {
                //Имя файла в формате 239_0d612c12d2ac33625bf3e0351b6f5e4f73829fa8_auto
                //Определяем тип файла, т.к. почтовые сервера отдают картинки, встроенные в текст письма, без расширения
                $ext = '';
                $parts_file = explode('_', $file);
                if ($parts_file[2] == $image_name) {
                    switch (mime_content_type($dir . '/' . $file)) {
                        case 'image/gif';
                            $ext = '.gif';
                            break;
                        case 'image/png';
                            $ext = '.png';
                            break;
                        case 'image/jpeg';
                            $ext = '.jpg';
                            break;
                    }
                    rename($dir . '/' . $file, $dir . '/' . $file . $ext);
                    $html_path_dir = Url::to('@web' . $message->attachments, true);
                    //Подставлям расширения файлам
                    $html = str_replace(
                        '<img src="cid:' . $image_name,
                        '<img src="' . $html_path_dir . '/' . $file . $ext,
                        $html);
                }
            }
        }
        return $html;
    }

    /**
     * Проверяет есть email адрес, с которого пришло обращение у жильца (заявителя)
     * @param int $petition_id ID Обращения
     * @param int $resident_id ID жильца
     * @return array|string
     */
    public static function checkEmailForPetition($petition_id, $resident_id)
    {
        Yii::info('CheckEmailForPetition', 'test');
        $petition_model = self::findOne($petition_id);
        $resident_model = Resident::findOne($resident_id);

        //Получаем минимальный ID сообщения (самое первое сообщение для обращения)
        /** @var Message $message */
        $message_id = Message::find()
                ->andWhere(['petition_id' => $petition_model->id])
                ->min('id') ?? null;

        if (!$message_id) {
            return 'Сообщения не найдены';
        }

        $message = Message::findOne($message_id);

        //Берем адрес первого сообщения и ищем в таблице с email`ами жильцов
        /** @var ResidentEmail $re_model */
        $re_model = ResidentEmail::find()
                ->andWhere(['email' => $message->from])
                ->one() ?? null;

        if (!$re_model) {
            //Если email не найден - добавляем жильцу этот email
            $model = new ResidentEmail([
                'resident_id' => $resident_model->id,
                'email' => $message->from
            ]);
            if (!$model->save()) {
                Yii::error($model->errors, '_error');
                return $model->errors;
            }
            //Добавляем email в обращение
            $petition_model->email_id = $model->id;
            if (!$petition_model->save()) {
                Yii::error($petition_model->errors, '_error');
                return $petition_model->errors;
            }
        } else {
            //Проверяем наличие email_id в обращении
            if (!$petition_model->email_id) {
                $petition_model->email_id = $re_model->id;
                if (!$petition_model->save()) {
                    Yii::error($petition_model->errors, '_error');
                    return $petition_model->errors;
                }
            }
        }
        return $petition_model->email_id;
    }

    /**
     * Проверяет есть ли новые обращения
     * @return bool
     */
    public static function availableNewPetition()
    {
        return self::find()
            ->joinWith(['createdBy cb'])
            ->statusNew()
            ->andWhere(['cb.company_id' => Yii::$app->user->identity->company_id])
            ->exists();
    }

}
