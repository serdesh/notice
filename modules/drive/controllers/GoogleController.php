<?php

namespace app\modules\drive\controllers;

use app\modules\drive\models\Auth;
use app\modules\drive\models\Google;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `drive` module
 */
class GoogleController extends Controller
{
    /**
     * Renders the index view for the module
     * @param null $code
     * @return string
     * @throws \Google_Exception
     */
    public function actionIndex($code = null)
    {
        if ($code) {
            (new Auth)->getTokenWithCode($code);
        }
        return $this->render('index');
    }

    /**
     * @return array|Response
     * @throws \Google_Exception
     */
    public function actionFirstAuthenticate()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $client = (new Auth)->clientInit();

            Yii::info('redirect URI: ' . $client->getRedirectUri(), 'test');

            //Запрашиваем код доступа
            $data = [
                'response_type' => 'code',
                'redirect_uri' => $client->getRedirectUri(),
                'client_id' => $client->getClientId(),
                'scope' => Google_Service_Drive::DRIVE_FILE,
                'access_type' => 'offline',
                'approval_prompt' => 'auto',
            ];

            $get_data = http_build_query($data);

            Yii::info('https://accounts.google.com/o/oauth2/auth?' . $get_data, 'test');

            $url = 'https://accounts.google.com/o/oauth2/auth?' . $get_data;

//            $this->redirect('https://accounts.google.com/o/oauth2/auth?' . $get_data);

            return [
                'size' => 'large',
                'title' => 'Выдача разрещений приложению',
                'content' => $this->renderAjax(Url::to('_oauth_form'), [
                    'url' => $url,
                ]),
            ];
        }

        return $this->redirect('index');
    }

    /**
     * Отправляет файл на диск
     * @param $source_path
     * @param $destination_path
     * @return string|null При возниконовении ошибок возвращает null
     * @throws \Google_Exception
     */
    public function actionSendFile($source_path, $destination_path)
    {
        $result = Google::sendFile($source_path, $destination_path);

        if ($result) {
            return Json::encode($result);
        }

        return null;
    }

    /**
     * Копирование файла на диск
     * @return bool|string
     * @throws \Google_Exception
     */
//    public function actionCopyToDisk()
//    {
//        $path_dir = Url::to('@app/backups');
//        $driveService = Google::getGoogleDriveService();
//        /*Очистка корзины не работает.
//        Для возможности очистки корзины в консоли Google в учетных данных нужно добавить область действия "../auth/drive"
//        Что влечет за собой проверку сервиса самим Google
//        */
////        $driveService->files->emptyTrash();
//
//        //Ищем на диске папку backups
//        $backup_folder_id = Settings::getBackupGoogleFolder();
//        if (!$backup_folder_id) {
//            //Создаем папку
//            $fileMetadata = new Google_Service_Drive_DriveFile([
//                'name' => 'backup_lift',
//                'mimeType' => 'application/vnd.google-apps.folder'
//            ]);
//            $folder = $driveService->files->create($fileMetadata, [
//                'fields' => 'id'
//            ]);
//            //Пишем ID папки в настройки
//            $settings_model = Settings::find()->where(['key' => 'backup_folder_in_google_drive'])->one();
//            $settings_model->value = $folder->id;
//
//            if (!$settings_model->save()) {
//                Yii::error($settings_model->errors, __METHOD__);
//                Yii::$app->session->setFlash('error', 'Ошибка сохранения ID папки Google Drive');
//            }
//            $backup_folder_id = $folder->id;
//        }
//
//        //Закачиваем на GoogleDrive файл бэкапа
//        $fileMetadata = new Google_Service_Drive_DriveFile([
//            'name' => $file_name,
//            'parents' => [$backup_folder_id]
//        ]);
//        $content = file_get_contents($new_file);
//        $file = $driveService->files->create($fileMetadata, [
//            'data' => $content,
//            'mimeType' => 'application/x-tar',
//            'uploadType' => 'multipart',
//            'fields' => 'id'
//        ]);
//
//        Yii::info($file, 'test');
//
//        if (isset($file->id)) {
//            //Ели закачка успешна удаляем файл с сервера
//            if (!unlink($new_file)) {
//                Yii::warning('Ошибка удаления скопированного файла бэкапа из папки backups', __METHOD__);
//                return false;
//            }
//        }
//
//        return true;
//
//    }

    /**
     * @throws \Google_Exception
     */
    public function actionTestSendFile()
    {
        $path_dir = 'notice111';
        $result = $this->actionSendFile(Url::to('@webroot/uploads/123.xlsx'), $path_dir . '/123.xlsx');

        Yii::info($result, 'test');
        return Json::encode($result);
    }

    /**
     *  https://developers.google.com/drive/api/v3/search-files
     * @throws \Google_Exception
     */
    public function actionTestIsDouble()
    {
        $directory_name = 'notice111';
        $parent_directory_id = '111o6ARxKcxx37Gng3u_P5Uv7avoYmwvy';
        return Google::isDouble($directory_name, $parent_directory_id);
//       return Google::isDouble($directory_name);

    }

    /**
     * @throws \Google_Exception
     */
    public function actionTestCreateSharedFolder()
    {
        $driveService = Google::getGoogleDriveService();
        $directory_name = 'test_directory';
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $directory_name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);
        $directory = $driveService->files->create($fileMetadata, [
            'fields' => 'id'
        ]);

        return Google::shareFile($directory->id);
    }


}
