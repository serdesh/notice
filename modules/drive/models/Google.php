<?php

namespace app\modules\drive\models;


use app\models\Settings;
use app\models\Users;
use expectedClass;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;
use Google_Service_Exception;
use Yii;
use yii\helpers\VarDumper;

class Google
{

    /**
     *
     * @param object $client Google_Client
     * @param int $company_id
     * @return Google_Service_Drive
     * @throws \Google_Exception
     */
    public static function getGoogleDriveService($client = null, $company_id = null)
    {
        if (!$client) {
            $client = Auth::clientInit($company_id);
        }

        $driveService = new Google_Service_Drive($client);

        return $driveService;
    }

    /**
     * Очистка гугло-корзины
     * @throws \Google_Exception
     */
    public static function emptyTrash()
    {
        $driveService = self::getGoogleDriveService();

        $driveService->files->emptyTrash();
        return true;
    }

    public static function getRefreshToken()
    {
        return Settings::find()->where(['key' => 'google_refresh_token'])->one()->value ?? null;
    }

    /**
     * Отправляет файл в Googlle облако
     * @param string $source_path Путь к файлу, который необходимо отправить
     * @param string $destination_path Путь к файлу на гугло диске
     * @param null $company_id
     * @param bool $return_directory_id Если true метод возвращает id папки, в противном случае ID файла
     * @return array|string
     * @throws \Google_Exception
     */
    public static function sendFile($source_path, $destination_path, $return_directory_id = false, $company_id = null)
    {
        $destination_name = basename($destination_path);
        $destination_path_dir = str_replace($destination_name, '', $destination_path);
        $mime_type = mime_content_type($source_path);

        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }

        \Yii::info('Имя файла: ' . $destination_name, 'test');
        \Yii::info('Путь к директории: ' . $destination_path_dir, 'test');

        $driveService = Google::getGoogleDriveService(null ,$company_id);
        /*Очистка корзины не работает.
        Для возможности очистки корзины в консоли Google в учетных данных нужно добавить область действия "../auth/drive"
        Что влечет за собой проверку сервиса самим Google
        */
//        $driveService->files->emptyTrash();

        //Ищем на диске папку backups
        $root_directory_id = Settings::getValueByKeyFromCompany('google_root_folder_id', $company_id);
        //Если записи о папке не найдено - создаем папку на гугло диске
        if (!$root_directory_id) {
            //Создаем папку
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => Yii::$app->name,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);
            $directory = $driveService->files->create($fileMetadata, [
                'fields' => 'id'
            ]);
            //Пишем ID папки в настройки
            $settings_model = Settings::find()->where(['key' => 'google_root_folder_id'])->one() ?? null;
            //Если настройки вообще нет в базе, создаем
            if (!$settings_model) {
                $settings_model = new Settings([
                    'key' => 'google_root_folder_id',
                    'name' => 'ID папки приложения в Google облаке',
                    'company_id' => $company_id,
                ]);
            }
            $settings_model->value = $directory->id;

            Yii::info('ID созданной директории: ' . $settings_model->value, 'test');

            if (!$settings_model->save()) {
                Yii::error($settings_model->errors, 'error');
            }
            $result_shared = Google::shareFile($directory->id, $company_id);
            Yii::info('Shared: ' . (string)$result_shared, 'test');

            Yii::info('Корневая папка для компании создана успешно', 'test');
        }

        //Создаем дерево папок
        $destination_directory_id = self::createDir($destination_path_dir, $driveService, $company_id);

        if (!$destination_directory_id) {
            return null;
        }

        //Закачиваем на GoogleDrive файл
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $destination_name,
            'parents' => [$destination_directory_id]
        ]);
        $content = file_get_contents($source_path);
        $file = $driveService->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mime_type,
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        Yii::info($file->id, 'test');

        if (isset($file->id)) {
            //Ели закачка успешна удаляем файл с сервера
            if (!unlink($source_path)) {
                Yii::warning('Ошибка удаления отправленного файла', 'warning');
                return ['success' => 'false', 'error' => 'Ошибка удаления файла с сервер'];
            }
        }

        if ($return_directory_id) {
            return $destination_directory_id;
        }
        return ['success' => 'true', 'file' => $file->id];
    }

    /**
     * Создает папку или дерево папок
     * @param string $destination_path_dir Полный путь к создаваемой папке, исключая папку приложения
     * @param Google_Service_Drive $driveService
     * @param int $company_id
     * @return bool
     * @throws \Google_Exception
     */
    private static function createDir($destination_path_dir, $driveService, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        $root_directory_id = Settings::getValueByKeyFromCompany('google_root_folder_id', $company_id);
        $parts_path = explode('/', $destination_path_dir);

        Yii::info('ID корневой папки: ' . $root_directory_id, 'test');

        if (!$root_directory_id) {
            return null;
        }

        Yii::info('Root Dir ID: ' . $root_directory_id, 'test');
        Yii::info($parts_path, 'test');

        $current_directory_id = $root_directory_id;

        foreach ($parts_path as $directory_name) {
            if (!$directory_name) {
                continue;
            }
            //Ищем папку с таким же именем
            $found_directory_id = self::isDouble($directory_name, $current_directory_id, $company_id);
            Yii::info('Папка найдена: ' . (string)$found_directory_id, 'test');
            if ($found_directory_id) {
                //Если папка найдена переходим к следующей итерации
                $current_directory_id = $found_directory_id;
                continue;
            }

            //Если совпадений не найдено - создаем папку
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $directory_name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$current_directory_id],
            ]);
            $directory = $driveService->files->create($fileMetadata, [
                'fields' => 'id'
            ]);
            $current_directory_id = $directory->id;
        }
        Yii::info('ID папки в которую будет скопирован файл: ' . $current_directory_id, 'test');

        return $current_directory_id;
    }

    /**
     * Получает имя файла или папки
     * @param string $id ID папки или файла
     * @param Google_Service_Drive $driveService
     * @return mixed
     */
    protected function getFileNameById($id, $driveService)
    {
//        VarDumper::dump($service->files->get($id)->name, 10, true);
        return $driveService->files->get($id)->name;

    }

    /**
     * Проверяет наличие папки с таким же именем в указанной папке (если папка не указана - ищет по всему диску)
     *  https://developers.google.com/drive/api/v3/search-files
     * @param string $directory_name Имя искомой папки
     * @param string $parent_directory_id ID папки, где будем искать совпадение имени папки
     * @param int $company_id
     * @return string|boolean Если совпадение найдено - возвращает ID папки, если нет - false
     * @throws \Google_Exception
     */
    public static function isDouble($directory_name, $parent_directory_id = null, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        $driveService = Google::getGoogleDriveService(null, $company_id);

        if ($parent_directory_id) {
            $search_result = $driveService->files->listFiles([
                'q' => "name = '$directory_name' and mimeType = 'application/vnd.google-apps.folder' and '$parent_directory_id' in parents",
                //
                'fields' => 'files(id, name)'
            ]);
        } else {
            $search_result = $driveService->files->listFiles([
                'q' => "name = '$directory_name' and mimeType = 'application/vnd.google-apps.folder'", //
                'fields' => 'files(id, name)'
            ]);
        }
        Yii::info($search_result, 'test');

        if ($search_result) {
            if (isset($search_result[0])) {
                return $search_result[0]->id;
            } else {
                return $search_result->id;
            }
        }

        return false;


//       VarDumper::dump($result, 10, true);

    }

    /**
     * @param $id
     * @param int $company_id
     * @return expectedClass|\Google_Http_Request
     * @throws \Google_Exception
     */
    public static function deleteFile($id, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        $driveService = Google::getGoogleDriveService(null, $company_id);
        return $driveService->files->delete($id);
    }

    /**
     * @param $id
     * @param int $company_id
     * @return expectedClass|\Google_Http_Request
     * @throws \Google_Exception
     */
    public static function deleteDirectory($id, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        return self::deleteFile($id, $company_id);
    }

    /**
     * @param $id
     * @param int $company_id
     * @return \yii\console\Response|\yii\web\Response
     * @throws \Google_Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public static function downloadFile($id, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        Yii::info('ID скачиваемого файла: ' . $id, 'test');
        $driveService = Google::getGoogleDriveService(null, $company_id);
        $file = $driveService->files->get($id, ['fields' => 'name, mimeType']);
        $name = $file->name;
        Yii::info('Получаемый файл: ' . $name, 'test');
        $mime_type = $file->mimeType;
        Yii::info('mime тип файла: ' . $mime_type, 'test');

//        VarDumper::dump($file, 10, true);
        $response = $driveService->files->get($id, [
            'alt' => 'media',
        ]);

//        $response = $driveService->files->export($id, $mime_type, ['alt' => 'media']);

        $content = $response->getBody()->getContents();

        return Yii::$app->response->sendContentAsFile($content, $name);

    }

    /**
     * @param string $id ID папки на google диске
     * @return string
     * @throws \Google_Exception
     */
    public static function getFilesInDirectory($id, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        $driveService = Google::getGoogleDriveService(null, $company_id);
        $directory = $driveService->files->get($id);
        VarDumper::dump($directory, 10, true);
        return '';
    }

    /**
     * @param $id
     * @param int $company_id
     * @return string
     * @throws \Google_Exception
     */
    public static function shareFile($id, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        $fileId = $id;
        $driveService = self::getGoogleDriveService(null, $company_id);

        $driveService->getClient()->setUseBatch(true);
        try {
            $batch = $driveService->createBatch();

            $company_email = Users::find()
                ->andWhere(['company_id' => $company_id])
                ->andWhere(['permission' => Users::USER_ROLE_ADMIN])
                ->one()
                ->email ?? null;

            if (!$company_email) {
                Yii::error('Не найден email компании. Расшаривание невозможно', '_error');
                return false;
            }

            $userPermission = new Google_Service_Drive_Permission([
                'type' => 'user',
                'role' => 'reader',
                'emailAddress' => $company_email,
            ]);
            $request = $driveService->permissions->create(
                $fileId, $userPermission, array('fields' => 'id'));
            $batch->add($request, 'user');
            $domainPermission = new Google_Service_Drive_Permission(array(
                'type' => 'domain',
                'role' => 'reader',
                'domain' => Yii::$app->request->hostInfo,
            ));
            $request = $driveService->permissions->create(
                $fileId, $domainPermission, array('fields' => 'id'));
            $batch->add($request, 'domain');
            $results = $batch->execute();

            foreach ($results as $result) {
                if ($result instanceof Google_Service_Exception) {
                    // Handle error
                   return $result;
                } else {
                    return"Permission ID: " . $result->id;
                }
            }
        } finally {
            $driveService->getClient()->setUseBatch(false);
        }

        return 'Внутренняя ошибка';
    }
}