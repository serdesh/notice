<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30.05.2019
 * Time: 13:56
 */

namespace app\modules\drive\models;


use app\models\Settings;
use app\models\Users;
use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\VarDumper;

class Yandex extends Model
{

//    const CREATE_FOLDER = '';

    /**
     * @return Disk
     */
    private static function getDisk()
    {
        return new Disk(Settings::getValueByKeyFromCompany('yandex_disk_token', Users::getCompanyIdForUser()));
    }
    /**
     * Что-то делаем с диском
     */
    public static function requestSend()
    {
        // передать OAuth-токен зарегистрированного приложения.
        $disk = new Disk('AgAAAAAKWXLNAAWzB9G5rLRtnkmPlkBwAPLBzzs');

        /**
         * Получить Объектно Ориентированное представление закрытого ресурса.
         * @var  Closed $resource
         */
        $resource = $disk->getResource('file1.png');

        // проверить сущестует такой файл на диске ?
//        $resource->has(); // вернет, например, false

        // загрузить файл на диск под имененм "файл в локальной папке.txt".
//        $resource->upload(__DIR__ . '/файл в локальной папке.txt');

        $path_file = Url::to('@webroot/images/file1.png');

        Yii::info($path_file, 'test');

        $resource->upload($path_file);

        // файл загружен, вывести информацию.
        VarDumper::dump($resource, 10, true);

        // теперь удалить в корзину.
        $removed = $resource->delete();

        Yii::info('Removed: ' . $removed, 'test');
    }

    /**
     * Создет папку на яндекс диске
     * @param string $dir_path Путь папки без корневой папки (Корневая - наименование приложения)
     * @param null $company_id
     * @return bool
     */
    public static function createDir($dir_path, $company_id = null)
    {
        $token = self::getToken($company_id);
//        Yii::info('Токен:' . $token, 'test');
        Yii::info('Путь к папке:' . $dir_path, 'test');

        $disk = new Disk($token);
        $dir_path = Yii::$app->name . '/' . $dir_path;
        $resource = $disk->getResource($dir_path);

        if (!$resource->has()) {
            $parts_path = explode('/', $dir_path);
            Yii::info($parts_path, 'test');

            $cur_path = '';
            foreach ($parts_path as $name_dir) {
                $cur_path .= $name_dir;
                Yii::info('Путь к папке: ' . $cur_path, 'test');

                $res = $disk->getResource($cur_path);
                if (!$res->has()) {
                    $res->create();
                }
                $cur_path .= '/';
            }
        }
        return true;
    }

    /**
     * Получает токен компании
     * @param null $company_id
     * @return null|string
     */
    public static function getToken($company_id = null)
    {
        Yii::info('Переданное ID компании:' . $company_id, 'test');
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }

        return Settings::getValueByKeyFromCompany('yandex_disk_token', $company_id);
    }

    /**
     * Отправка файла на Чндекс диск
     * @param $source_path
     * @param $destination_path
     * @param null $company_id
     * @return Disk\Operation|bool
     */
    public static function sendFile($source_path, $destination_path, $company_id = null)
    {
        $new_file_name = basename($destination_path);
        $path_new_dir = str_replace($new_file_name, '', $destination_path);

        Yii::info('Source: ' . $source_path, 'test');
        Yii::info('Destination: ' . $destination_path, 'test');
        Yii::info('New file name: ' . $new_file_name, 'test');
        Yii::info('New dir path: ' . $path_new_dir, 'test');
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }

        Yandex::createDir($path_new_dir, $company_id);

        $disk = new Disk(self::getToken($company_id));

        /**
         * Получить Объектно Ориентированное представление закрытого ресурса.
         * @var  Closed $resource
         */
        $resource = $disk->getResource(Yii::$app->name . '/' . $destination_path);

        return $resource->upload($source_path, true);
    }

    public static function deleteFile($path_file)
    {
        $disk = new Disk(Settings::getValueByKeyFromCompany('yandex_disk_token', Users::getCompanyIdForUser()));

        $resource = $disk->getResource(Yii::$app->name . '/' . $path_file);

        Yii::info('Recourse is exist: ' . $resource->has(), 'test');

        if ($resource->has()){
            return $resource->delete();
        } else {
            return 'Ресурс не найден';
        }

    }

    public static function deleteDirectory($path_directory)
    {
       return self::deleteFile($path_directory);
    }

    public static function downloadFile ($path)
    {
        Yii::info('Путь к файлу на яндекс диске: ' . $path, 'test');
        $disk = self::getDisk();

        $resource = $disk->getResource(Yii::$app->name . '/' . $path);
        $name = basename($path);
        Yii::info('Recourse is exist: ' . $resource->has(), 'test');

        if ($resource->has()){
            $tmp_dir_name = (int)time();
            $tmp_dir_path = Url::to('@webroot/uploads/' . $tmp_dir_name);
            Yii::info('Temp folder: ' . $tmp_dir_path, 'test');
            $file_path = $tmp_dir_path . '/' . $name;
            Yii::info('Path file: ' . $file_path, 'test');

            mkdir($tmp_dir_path, 0777);
//            return $resource->download('/uploads/' . $tmp_dir_name . '/' . $name);
             $resource->download($file_path);
            return $file_path;
        } else {
            return 'Ресурс не найден';
        }
    }

    public static function getFilesInDirectory($path)
    {
        $path = Yii::$app->name . '/' . $path;
        Yii::info('Путь к папке на яндекс диске: ' . $path);

        if (!$path) return 'Отсутствует путь к папке';

        $disk = self::getDisk();

        $resourse = $disk->getResource($path);

        if (!$resourse->has()) return 'Папка не существует';

        $link = $resourse->getLink();

        Yii::info($link, 'test');

        return $link;

    }

}