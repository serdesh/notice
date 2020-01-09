<?php

namespace app\models;

use \app\components\BulkWidget;
use app\modules\drive\models\Yandex;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "functions".
 */
class Functions extends ActiveRecord
{
    /**
     * Получает роль запращиваемого пользователя
     * @param int $id ID Пользователя
     * @return null|string
     */
    public static function getUserRole($id)
    {
        $user = Users::find()->where(['id' => $id])->one();
        if ($user) {
            if ($user->permission == 'super_administrator') {
                return 'Супер Администратор';
            }
            if ($user->permission == 'super_manager') {
                return 'Супер Менеджер';
            }
            if ($user->permission == 'administrator') {
                return 'Администратор';
            }
            if ($user->permission == 'manager') {
                return 'Менеджер';
            }
            if ($user->permission == 'specialist') {
                return 'Специалист';
            }
        }
        return null;
    }

    /**
     * В зависимости от роли возвращает или нет кнопку таблицы "Удалить все"
     * @return string
     * @throws \Exception
     */
    public static function getBulkButtonWidget()
    {
        $bulk_button_widget = BulkWidget::widget([
            'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить Все',
                ["bulkdelete"],
                [
                    "class" => "btn btn-danger btn-xs",
                    'role' => 'modal-remote-bulk',
                    'data-confirm' => false,
                    'data-method' => false,// for overide yii data api
                    'data-request-method' => 'post',
                    'data-confirm-title' => 'Вы уверены?',
                    'data-confirm-message' => 'Вы уверены что хотите удалить все эти элементы?'
                ]),
        ]);

        if (!Users::isAdmin()) {
            $bulk_button_widget = '';
        }

        return $bulk_button_widget;
    }

    /**
     * Получает адреса всех квартир собственника
     * @param int $id ID собственника
     * @var $apartment \app\models\Apartment
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getAddressesOwnerApartments($id = null)
    {
//        if (!$id) return 'Не найдено';
        $resident = Resident::findOne($id);

        //TODO: Реализовать вывод всех адресов для собственника нескольких квартир

        return $resident->apartment->getFullAddress();
    }

    /**
     * Конвертирует дату в Y-m-d 00:00:00
     * @param string $date Дата в формате d.m.Y
     * @return string
     */
    public static function getDateForBase($date)
    {
        if (strstr($date, '.')) {
            $parts_date = explode('.', $date);

            return $parts_date[2] . '-' . $parts_date[1] . '-' . $parts_date[0] . ' 00:00:00';
        }
        return $date;

    }

    /**
     * Конвертирует дату в Y-m-d H:i
     * @param string $date Дата в формате d.m.Y
     * @return string
     */
    public static function getDateTimeForBase($date)
    {
        if (strstr($date, '.') && strstr($date, ':')) {

            $parts_date_time = explode(' ', $date);

            $parts_date = explode('.', $parts_date_time[0]);

            return $parts_date[2] . '-' . $parts_date[1] . '-' . $parts_date[0] . ' ' . $parts_date_time[1];
        }
        return $date;

    }

//    /**
//     * Парсит строку адреса из таблицы обращений (petition).
//     * Формат входящей строки адреса: тип [street||house||apartment]]|id [street_id||house_id||apartment_id;
//     * Например "house|23"
//     * т.е. house_id = 23
//     * @param $address
//     * @return bool
//     */
//    public static function parseAddress($address)
//    {
//
//        if (!self::checkAddressFormat($address)) return null;
//
//        $part_address = explode('|',$address);
//
//        switch ($part_address[0]){
//            case 'apartment':
//                //Получаем полный адрес по квартире
//                return Apartment::findOne($part_address[1])->getFullAddress() ?? null;
//                break;
//            case 'house':
//                //Получаем полный адрес по дому
//                return House::findOne($part_address[1])->getFullAddress() ?? null;
//                break;
//            case 'street':
//                //Получаем улицу вместе с типом
//                return Street::findOne($part_address[1])->getShortName() ?? null;
//                break;
//            default:
//                return null;
//        }
//
//    }

    /**
     * Получает части адреса - тип улицы, наименование улицы, номер дома, номер квартиры
     * @param string $address Адрес в формате тип [street||house||apartment]]|id [street_id||house_id||apartment_id
     * Например "house|23"
     * т.е. house_id = 23
     * @return null
     */
    public static function getAddressParts($address)
    {
        if (!self::checkAddressFormat($address)) {
            return null;
        }
        $part_address = explode('|', $address);

        Yii::info($part_address, 'test');

        //break не проставлен специально
        switch ($part_address[0]) {
            case 'apartment':
                $apartment_model = Apartment::findOne($part_address[1]) ?? null;
                Yii::info('Apartment ID: ' . $apartment_model->id, 'test');
            case 'house':
                if (!isset($apartment_model)) {
                    $house_model = House::findOne($part_address[1]) ?? null;
                } else {
                    $house_model = House::findOne($apartment_model->house_id) ?? null;
                }
                Yii::info('House ID: ' . $house_model->id, 'test');
            case 'street':
                if (!isset($house_model)) {
                    $street_model = Street::findOne($part_address[1]) ?? null;
                } else {
                    $street_model = Street::findOne($house_model->street_id) ?? null;
                }
                Yii::info('Street ID: ' . $street_model->id, 'test');
                break;
        };


        $result = [
            'type' => $street_model->type_id ?? null,
            'street' => $street_model->id ?? null,
            'house' => $house_model->id ?? null,
            'apartment' => $apartment_model->id ?? null
        ];

        return $result;
    }

    /**
     * @param string $address строка адреса в формате тип [street||house||apartment]]|id [street_id||house_id||apartment_id
     * @return bool
     */
    private static function checkAddressFormat($address)
    {
        if ($address && strstr($address, '|') == false) {
            Yii::error('Не верный формат строки адреса. Парсинг невозможен!', '_error');
            return false;
        }
        return true;
    }

    /**
     * Пишет в сесию улицу, дом, квартиру (при наличии)
     * @param string $data строка в формате тип [street||house||apartment]]|id [street_id||house_id||apartment_id
     * @return null
     */
    public static function setAddressParts($data)
    {
        if (!self::checkAddressFormat($data)) {
            return null;
        }

        $session = Yii::$app->session;
        $part_address = explode('|', $data);

        switch ($part_address[0]) {
            case 'apartment':
                $apartment_model = Apartment::findOne($part_address[1]) ?? null;
                $session->set('apartment', $apartment_model->id);

                Yii::info('Apartment ID: ' . $apartment_model->id, 'test');
            case 'house':
                if (!isset($apartment_model)) {
                    $house_model = House::findOne($part_address[1]) ?? null;
                } else {
                    $house_model = House::findOne($apartment_model->house_id) ?? null;
                }
                $session->set('house', $house_model->id);

                Yii::info('House ID: ' . $house_model->id, 'test');
            case 'street':
                if (!isset($house_model)) {
                    $street_model = Street::findOne($part_address[1]) ?? null;
                } else {
                    $street_model = Street::findOne($house_model->street_id) ?? null;
                }
                $session->set('street', $street_model->id);
                $session->set('type', $street_model->type_id);

                Yii::info('Street ID: ' . $street_model->id, 'test');
                break;
        };

        return true;
    }

    public static function setFlash($message, $type = 'error', \Exception $e = null)
    {
        if ($e) {
            Yii::error($e->getTraceAsString(), '_error');
            Yii::$app->session->setFlash('error', $e->getMessage());
        } else {
            $result_msg = '';
            if ($type == 'error') {
                Yii::error($message, '_error');
            }
            if (is_array($message)) {
                foreach ($message as $key => $item) {
                    $result_msg .= $item . PHP_EOL;
                }
            } else {
                $result_msg = $message;
            }
            Yii::$app->session->setFlash($type, $result_msg);
        }

    }

    /**
     * Возвращает id помещения
     * @param string $resident_address строка адреса включая номер помещения
     * @return mixed
     */
    public static function getApartmentIdFromResidentAddress($resident_address)
    {
        $part_address = explode(', ', $resident_address);
        $apartment_str = array_pop($part_address);

        Yii::info($apartment_str, 'test');

        $part_apartment = explode(' ', $apartment_str);

        Yii::info($part_apartment, 'test');

        $apartment_num = array_pop($part_apartment); //Номер помещения

        return Apartment::find()->andWhere(['number' => $apartment_num])->one()->id ?? null;
    }

    /**
     * @param string $rus_date Дата в формате d.m.Y
     * @return null
     */
    public static function getDateForDb($rus_date)
    {

        if (!strstr($rus_date, '.')) {
            return null;
        }

        $part_date = explode('.', $rus_date);

        $pendos_date = $part_date[2] . '-' . $part_date[1] . '-' . $part_date[0];

        \Yii::info($pendos_date, 'test');

        return $pendos_date;

    }

    public static function getExtension($path)
    {
//        $dir = dirname($path);
//        $file = str_replace($dir, '',$path);
        $part_name_file = explode('.', $path);
        return '.' . end($part_name_file);
    }

    /**
     * Удаляет директорию со вложеннымии папками и файлами
     * @param string $path Путь к директории
     * @return bool
     */
    public static function deleteDirectory($path)
    {
        try {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$path/$file")) ? self::deleteDirectory("$path/$file") : unlink("$path/$file");
            }
            return rmdir($path);
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), '_error');
        }

        return 'Ошибка удаления папки';
    }

    /**
     * Получает вложения сообщения
     * @param Message $model
     * @return string Ссылка на скачивание файла
     */
    public static function getMailAttachments(Message $model)
    {
        Yii::info($model->attachments, 'test');

        if (!$model->attachments) {
            Yii::info('Нет вложений', 'test');
            return 'Нет вложений';
        }

        Yii::info('Сообщение с вложениями', 'test');

        $drive = Settings::getDrive(); //Тип хранилища файлов

        if ($drive == Settings::DRIVE_YANDEX) {
            Yii::info('Выбрано хранилище Яндекс диск', 'test');
            Yii::info('Путь к папке: ' . $model->attachments, 'test');
            $download_link = Yandex::getFilesInDirectory($model->attachments);
            return Html::a('Скачать вложения', $download_link, ['class' => 'btn btn-primary']);
        } elseif ($drive == Settings::DRIVE_GOOGLE) {
            Yii::info('Выбрано хранилище GoogleDrive', 'test');
            Yii::info('Id папки с файлами: ' . $model->attachments, 'test');
            return Html::a('Скачать файлы', 'https://drive.google.com/open?id=' . $model->attachments, [
                'class' => 'btn btn-primary',
                'target' => '_blank',
            ]);
        } elseif (!$drive) {
            Yii::info('Выбрано локальное хранилище', 'test');
            Yii::info('Путь к папке с файлами: ' . $model->attachments, 'test');
            //Файлы хранятся на сервере
            return Message::getFiles($model->attachments);
        }
        return 'Неизвестный тип диска';
    }

    /**
     * Обрезает 10 последних цифр номера телефона
     * @param string $phone Номер телефона
     * @return bool|string
     */
    public static function formatPhone($phone)
    {
        $phone = trim($phone);
        $phone = strip_tags($phone);
        $phone = substr($phone, -10);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace(' ', '', $phone);
        return $phone;
    }

    public static function addToHistory($petition_id, $name, $description = null)
    {
        $model = new History();
        $model->petition_id = $petition_id;
        $model->name = $name;
        $model->description = $description;
        if (!$model->save()) {
            Yii::error($model->errors, '_error');
            return false;
        }
        return true;
    }

    /**
     * охраняет/удаляет email`ы жильца.
     * @param int $resident_id Жилец/заявитель
     * @param string $emails НЕсколько адресов или один адрес(1@mail.ru, 2@mail.ru, 3@mail.ru)
     * @return array|bool Если ошибка возвращает массив с ошибками модели
     */
    public static function saveResidentEmails($resident_id, $emails)
    {
        if (!$resident_id || !$emails) {
            return 'Отсутствует email или заявитель';
        }
        $emails = strtolower($emails);
        $emails = str_replace(' ', '', $emails);

        $arr_emails = [];
        $arr_cur_emails = [];

        if (strpos($emails, ',')) {
            $arr_emails = explode(',', $emails);
        } else {
            array_push($arr_emails, $emails);
        }

        Yii::info($arr_emails, 'test');

        /** @var Resident $resident_model */
        $resident_model = Resident::findOne($resident_id);
        /** @var ResidentEmail[] $current_emails */
        $current_emails = $resident_model->emails;
        foreach ($current_emails as $e_model) {
            array_push($arr_cur_emails, $e_model->email);
        }

        $for_delete = array_diff($arr_cur_emails, $arr_emails); //Для удаления
        $for_add = array_diff($arr_emails, $arr_cur_emails); //Для добавления

        $del_result = ResidentEmail::DeleteAll(['IN', 'email', $for_delete]);

        Yii::info('Del result: ' . $del_result, 'test');

        foreach ($for_add as $email) {
            $model = new ResidentEmail([
                'resident_id' => $resident_model->id,
                'email' => $email,
            ]);
            if (!$model->save()){
                Yii::error($model->errors, '_error');
                if ($model->errors['email'] ?? null){
                        return $email . ' ' . $model->errors['email'][0];
                }
            }
        }
        return true;
    }

}
