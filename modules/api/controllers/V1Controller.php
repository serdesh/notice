<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.06.2019
 * Time: 14:50
 */

namespace app\modules\api\controllers;


use app\models\Company;
use app\models\Contact;
use app\models\Message;
use app\models\Petition;
use app\models\Resident;
use app\models\Settings;
use app\models\UploadForm;
use app\models\Users;
use app\modules\api\models\V1;
use app\modules\drive\models\Google;
use app\modules\drive\models\Yandex;
use app\models\Call;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;

class V1Controller extends Controller
{

//    public function behaviors()
//    {
//        return [
//            'access' => [
//                'class' => AccessControl::class,
//                'only' => ['get-status', 'check-form'],
//                'rules' => [
//                    [
//                        'actions' => ['get-status', 'check-form'],
//                        'allow' => true,
//                        'roles' => ['?'],
//                    ],
//                ],
//            ],
//        ];
//    }

    public function actionTestPush()
    {
        Yii::$app->controller->enableCsrfValidation = false;
        $postData = json_encode($_POST);

        Yii::info('next API');
        Yii::info($postData);
    }

    public function actionGetStatus()
    {
//        \Yii::$app->response->format = Response::FORMAT_JSON;

        $post = \Yii::$app->request->get();

        $petition_id = $post['number'];

        if (!$petition_id) {
            return 'Отсутвует номер зааявки';
        }

        $petition = Petition::findOne((int)$petition_id) ?? null;

        if (!$petition) {
            return 'Заявка № ' . $petition_id . ' не найдена';
        }

        $status = $petition->status->name ?? 'Не определен';
        $responsible = Users::getShortName($petition->specialist_id) ?? 'Не определен';
        $answer = $petition->answer ?? 'Не определен';

        return $this->renderPartial('check_result', [
            'petition_id' => $petition_id,
            'status' => $status,
            'responsible' => $responsible,
            'answer' => $answer,
        ]);
    }

    public function actionAddIncomingCall()
    {
        // $this->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = json_decode(Yii::$app->request->rawBody, true);

        $phone_number = $data['number'];
        $token = $data['token'];

        $phone = Contact::preparePhone($phone_number);

        $setting = Settings::find()->where(['value' => $token])->andWhere(['key' => 'atc_code'])->one();

        if($setting == null){
            return [];
        }

        //Ищем жильца по номеру телефона
        $resident = Resident::find()
                ->joinWith(['contact'])
                ->andWhere(['like', 'contact.phone', $phone])
                ->one() ?? null;

        $call = new Call([
            'phone_number' => $phone_number,
            'company_id' => $setting->company_id,
        ]);

        if ($resident){
            $call->resident_id = $resident->id;
        }

        if (!$call->save()){
            Yii::error($call->errors, '_error');
        }

        if ($call->resident_id){
            $url = Url::to(['/resident/view', 'id' => $resident->id]);
            return ['result' => 'success', 'is_resident' => 'true', 'url' => $url, 'phone' => $phone_number];
        }

        $url = Url::to(['/resident/create', 'phone' => $phone]);
        return ['result' => 'success', 'Звонок успешно добавелен', 'is_resident' => 'false', 'url' => $url, 'phone' => $phone_number];
    }

    public function beforeAction($action)
    {
        if($action->id == 'add-incoming-call'){
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Метод отдает форму для проверки статуса обращения
     * @return string
     */
    public function actionCheckForm()
    {
        return $this->renderPartial('check_form');
    }

    /**
     * Метод отдает виджет для отправки обращения в УК
     * @param int $company ID Компании для которой отправлено обращение
     * @return string
     */
    public function actionWidget($company)
    {
        $model = new V1();
        $model->company_id = $company;

        $petition_model = new Petition();
        $resident_model = new Resident();

        return $this->renderPartial('widget', [
            'model' => $model,
            'petition_model' => $petition_model,
            'resident_model' => $resident_model,
        ]);
    }

    /**
     * Получает обращение с сайта через виджет
     * @return string
     */
    public function actionPetition()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return 'Неизвестный запрос';
        }

        $resident = new Resident();
        $v1 = new V1();
        $petition = new Petition();
        $upload = new UploadForm();

        $v1->load($request->post());
        $resident->load($request->post());
        $petition->load($request->post());
        $company = Company::findOne($v1->company_id);

        if ($v1->agreement == '0') {
            $v1->addError('agreement', 'Необходимо дать согласие на обработку ПД');
        }

        if ($resident->last_name == '') {
            $resident->addError('last_name', 'Необходимо указать фамилию');
        }

        if ($resident->first_name == '') {
            $resident->addError('first_name', 'Необходимо указать имя');
        }

        if ($petition->header == '') {
            $petition->addError('header', 'Необходимо указать тему обращения');
        }

        if ($petition->text == '') {
            $petition->addError('text', 'Необходимо написать обращение');
        }

        if ($v1->hasErrors() || $petition->hasErrors() || $resident->hasErrors()) {
            return $this->renderPartial('widget', [
                'model' => $v1,
                'petition_model' => $petition,
                'resident_model' => $resident,
            ]);
        }

        $resident_exist_model = Resident::find()
                ->andWhere(['last_name' => $resident->last_name])
                ->andWhere(['first_name' => $resident->first_name])
                ->andWhere(['patronymic' => $resident->patronymic])
                ->one() ?? null;


        if ($resident_exist_model) {
            //Если жилец найден в базе
            $resident = $resident_exist_model;
        }
        if (!$resident->save()) {
            Yii::error($resident->errors, '_error');
        }

        if ($v1->phone) {
            //Добавляем телефон в базу
            $resident->addPhone($v1->phone);
        }


        $resident_contact = $resident->contact ?? null;
        if (!$resident_contact) {
            $resident_contact = new Contact();
        }

        if (!$resident_contact->save()) {
            Yii::error($resident_contact->errors, '_error');
        } else {
            $resident->contact_id = $resident_contact->id;
            $resident->save();
        }

        $petition->where_type = Petition::WHERE_TYPE_WIDGET;
        $petition->resident_id = $resident->id;
        $petition->created_by = Users::find()
                ->andWhere(['permission' => Users::USER_ROLE_ADMIN])
                ->andWhere(['company_id' => $company->id])
                ->one()
                ->id ?? null;
        $petition->address = $v1->address;

        if (!$petition->save()) {
            Yii::error($petition->errors, '_error');
        }

        switch ($petition->petition_type) {
            case 1:
                $dir_type = '002';
                break;
            default:
                $dir_type = '001';
        }

        $message = new Message();
        $message->header = $petition->header;
        $message->text = $petition->text;
        $message->petition_id = $petition->id;
        if (!$message->save()) {
            Yii::error($message->errors, '_error');
        }

        //Загружаем файлы
        if ($request->isPost) {
            $upload->files = UploadedFile::getInstances($upload, 'files');
            /** @var string $path_dir Путь к папке с загруженными файлами */
            if ($path_dir = $upload->uploads()) {
                //Загружено

                $uploaded_files = array_diff(scandir($path_dir), ['..', '.']);
                Yii::info($uploaded_files, 'test');

                //Определяемся с хранением файла (сервер, яндекс или гугло диск)
                $drive = Settings::getDrive($company->id);
                //Путь к папке в облаке
                $path_attachments_cloud_directory = $company->inn
                    . '/' . $dir_type
                    . '/' . $petition->id
                    . '/' . $message->id;

                /** @var UploadedFile $file */
//                foreach ($upload->files as $file) {
//                    Yii::info($file, 'test');
//                    $file_name = $file->name;
                if ($drive == 'yandex' || $drive == 'google') {
//                        $destination_path_file = $path_attachments_cloud_directory . '/' . $file_name;
//                        $source_path_file = $file->tempName;
                    if ($drive == 'yandex') {
                        try {
                            foreach ($uploaded_files as $file) {
                                $destination_path_file = $path_attachments_cloud_directory . '/' . $file;
                                Yandex::sendFile($path_dir . '/' . $file, $destination_path_file, $company->id);
                            }
//                                Yandex::sendFile($source_path_file, $destination_path_file, $company->id);
                        } catch (\Exception $e) {
                            return 'Внутренняя ошибка сервера: ' . $e->getMessage();
                        }
                        $message->attachments = $path_attachments_cloud_directory;
                    } elseif ($drive == 'google') {
                        try {
                            foreach ($upload->files as $file) {
                                $destination_path_file = $path_attachments_cloud_directory . '/' . $file->name;
                                $message->attachments = Google::sendFile($path_dir . '/' . $file, $destination_path_file,
                                    true,
                                    $company->id);
                            }
                        } catch (\Exception $e) {
                            return 'Внутренняя ошибка сервера: ' . $e->getMessage();
                        }
                    }
                    if (!$message->save()) {
                        Yii::error($message->errors, '_error');
                    };
                } else {
                    //Хранение файлов на сервере
                    $uploads_path_dir = Yii::$app->name . '/' . $company->inn . '/' . $dir_type . '/' . $petition->id . '/' . $message->id;
                    Yii::info('Путь к папке: ' . $uploads_path_dir, 'test');

                    if (!is_dir($uploads_path_dir)) {
                        $path = Url::to('@webroot/' . $uploads_path_dir);
                        if (!is_dir($path)) {
                            $create_result = mkdir($path, 0777, true);
                            Yii::info($create_result, 'test');
                        }
                    }
                    foreach ($uploaded_files as $file){
                        $uploads_path_file = Url::to('@webroot/' . $uploads_path_dir . '/' . $file);
                        $source_file = Url::to('@webroot/' . $path_dir . '/' . $file);
//                        $uploads_path_file = '/' . $uploads_path_dir . '/' . $file;
                        Yii::info('Путь к файлу: ' . $uploads_path_file, 'test');
                        Yii::info('Файл-ресурс: ' . $source_file, 'test');
                        Yii::info('Файл-ресурс существует: ' . is_file($source_file), 'test');
                        $move_result = false;
                        try{
                            $move_result = rename($source_file, $uploads_path_file);

                        } catch (\Exception $e){
                            Yii::error($e->getMessage(), '_error');
                        }

                        if (!$move_result) {
                            Yii::error('Ошибка перемещения загруженного файла', '_error');
                            Yii::error($move_result, '_error');
                        } else {
                            Yii::info('Файл перемещен: ' . (string)$move_result, 'test');
                        }
                    }
                    //Удаляем временные файлы
                    foreach ($uploaded_files as $file){
                        $path_removed_file = Url::to('@webroot/' . $path_dir . '/' . $file);
                        if (is_file($path_removed_file)){
                            unlink($path_removed_file);
                        }
                    }
                    rmdir(Url::to('@webroot/' . $path_dir));

                    $message->attachments = $uploads_path_dir;
                    if ($resident_contact->email){
                        $message->from = $resident_contact->email;
                    }
                    $message->save();
                }
//                }
            } else {
                Yii::error($upload->errors, '_error');
                return $this->renderPartial('widget', [
                    'model' => $v1,
                    'petition_model' => $petition,
                    'resident_model' => $resident,
                    'upload' => $upload,
                ]);
            }
        }

//        if ($upload->file['tmp_name'] && $upload->file['name']) {
//            //Определяемся с хранением файла (сервер, яндекс или гугло диск)
//            $drive = Settings::getDrive($company->id);
//            //Путь к папке в облаке
//            $path_attachments_cloud_directory = $company->inn
//                . '/' . $dir_type
//                . '/' . $petition->id
//                . '/' . $message->id;
//            $file_name = $upload->file['name'];
//
//            if ($drive == 'yandex' || $drive == 'google') {
//                $destination_path_file = $path_attachments_cloud_directory . '/' . $file_name;
//                $source_path_file = $upload->file['tmp_name'];
//                if ($drive == 'yandex') {
//                    try {
//                        Yandex::sendFile($source_path_file, $destination_path_file, $company->id);
//                    } catch (\Exception $e) {
//                        return 'Внутренняя ошибка сервера: ' . $e->getMessage();
//                    }
//                    $message->attachments = $path_attachments_cloud_directory;
//                } elseif ($drive == 'google') {
//                    try {
//                        $message->attachments = Google::sendFile($source_path_file, $destination_path_file, true,
//                            $company->id);
//                    } catch (\Exception $e) {
//                        return 'Внутренняя ошибка сервера: ' . $e->getMessage();
//                    }
//                }
//                if (!$message->save()) {
//                    Yii::error($message->errors, '_error');
//                };
//            } else {
//                $uploads_path_dir = '/' . Yii::$app->name . '/' . $company->inn . '/' . $dir_type . '/' . $petition->id . '/' . $message->id;
//                Yii::info('Путь к папке: ' . $uploads_path_dir, 'test');
//
//                if (!is_dir($uploads_path_dir)) {
//                    $create_result = mkdir(Url::to('@webroot/' . $uploads_path_dir), 0777, true);
//                    Yii::info($create_result, 'test');
//                }
//                $uploads_path_file = Url::to('@webroot/' . $uploads_path_dir . '/' . $upload->file['name']);
//                Yii::info('Путь к файлу: ' . $uploads_path_file, 'test');
//
//                $move_result = move_uploaded_file($upload->file['tmp_name'], $uploads_path_file);
//
//                if (!$move_result) {
//                    Yii::error('Ошибка перемещения загруженного файла', '_error');
//                    Yii::error($move_result, '_error');
//                } else {
//                    Yii::info('Файл перемещен: ' . (string)$move_result, 'test');
//                }
//
//                $message->attachments = $uploads_path_dir;
//                $message->save();
//            }
//        }
//
//        return 'Спасибо! Ваша заявка принята. Ваш номер - [' . str_pad($petition->id, 11, '0',
//                STR_PAD_LEFT) . ']';
        return $this->renderPartial('widget_result_page', [
            'petition_id' => $petition->id,
            'company_id' => $company->id,
        ]);

    }
}