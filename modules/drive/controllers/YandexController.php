<?php

namespace app\modules\drive\controllers;

use app\models\Company;
use app\models\Settings;
use app\models\Users;
use app\modules\drive\models\Yandex;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Default controller for the `disk` module
 */
class YandexController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTest()
    {
        $inn = Company::findOne(Users::getCompanyIdForUser())->inn ?? 'no_inn';
        $path_dir = $inn . '/ID_petition';

        $result = Yandex::sendFile(Url::to('@webroot/uploads/123.xlsx'),  $path_dir . '/123.xlsx');

        if ($result) return Json::encode($result);

        return 'Ошибка';
    }

    public function actionYandexDisk($access_token = null, $oauth = null)
    {
        $request = Yii::$app->request;

        if ($access_token){
            return $this->render('yandex_disk',[
                'access_token' => $access_token,
            ]);
        }

        if ($request->isAjax){

            Yii::info('is Ajax', 'test');

            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => "Доступ к Я Диску",
                'size' => 'large',
                'content' => $this->renderAjax('_oauth_form'),
            ];

        }

        if ($oauth){
            return $this->render('_oauth_form');

        }
        return $this->render('yandex_disk');
    }

    public function actionSaveToken()
    {
        $request = Yii::$app->request;

        $token = $request->post('token');

        if (!$token) return 'fail';
        Yii::info('Token: ' . $token, 'test');

        $setting = Settings::find()->andWhere(['key' => 'yandex_disk_token', 'company_id' => Users::getCompanyIdForUser()])->one() ?? null;

        if (!$setting){
            $setting = new Settings([
                'key' => 'yandex_disk_token',
                'name' => 'Токен доступа к Яндекс диску',
                'company_id' => Users::getCompanyIdForUser(),
            ]);
        }
        $setting->value = $token;

        if (!$setting->save()){
            Yii::error($setting->errors, '_error');
            return 'fail';
        }
        return 'success';
    }

    /**
     * Отправка файла в облако
     * @param string $source_path Путь к отправляемому файлу. На локальном сервере работает если путь C:/OSPanel/domains/notice/web/uploads/123.xlsx
     * @param string $destination_path Полный путь к файлу, без корневой папки. Корневая папка - Наименование приложения
     * Если передать просто имя файла, то он сохранится в папке Notice, котороя лежит в корне яндекс диска
     * @return string
     */
    public function actionSendFile($source_path, $destination_path)
    {
       $result = Yandex::sendFile($source_path, $destination_path);

       if ($result) return Json::encode($result);

       return 'Ошибка';
    }

    public function actionDownload($path){

        $path_file = Yandex::downloadFile($path);
        $name = basename($path_file);

        Yii::info('Путь к файлу для отдачи пользователю' . $path_file, 'test');

        if (file_exists($path_file)) {
            return Yii::$app->response->sendFile($path_file, $name);
        }

        return $this->render('/site/error', [
            'name' => 'Ошибка.',
            'message' => 'Ошибка при попытке загрузки файла.'
        ]);
    }

    /**
     * Устанавливает ID приложения Яндекс диска
     * @param $id
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionSetId($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        //Проверяем доступ пользователя к запрашиваемым данным
        if (!Users::isSuperAdmin()) {
                throw new ForbiddenHttpException('Доступ запрещен');
        }

        $settings = Settings::find()->andWhere(['key' => 'yandex_disk_client_id'])->one() ?? null;

        if (!$settings){
            $settings = new Settings([
                'key' => 'yandex_disk_client_id',
                'name' => 'ID приложения яндекс-диска',
                'company_id' => 1,
            ]);
        }

        $settings->value = $id;
        if (!$settings->save()){
            Yii::error($settings->errors, '_error');
            return ['fail', 'Ошибка сохранения ID приложения'];
        }

        return ['success'];
    }

}
