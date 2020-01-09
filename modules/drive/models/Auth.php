<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.04.2019
 * Time: 14:13
 */

namespace app\modules\drive\models;


use app\models\Settings;
use app\models\Users;
use Google_Client;
use Google_Service_Drive;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

class Auth
{
    const GOOGLE_CREDENTIALS_PATH = '@app/tokens/google/credentials.json'; //Файл можно скачать со страницы проекта google https://console.developers.google.com
    const GOOGLE_TOKEN_DIR = '@app/tokens/google/';

    public $company_id;



    /**
     * @return Google_Client
     * @throws \Google_Exception
     */
    public static function clientInit($company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }

        $redirect_uri  = Url::to('/drive', true);

        $client = new Google_Client();
        $client->setAuthConfig(Url::to(self::GOOGLE_CREDENTIALS_PATH));
        $client->setRedirectUri($redirect_uri);
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        //В scopes можно добавить Google_Service_Drive::DRIVE работать будет после того,
        // как в консоли Google в учетных данных будет добавлена область действия "../auth/drive"
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);   // incremental auth
        if (self::checkAccessToken($company_id)){
            self::setToken($client, $company_id);
            if ($client->isAccessTokenExpired()){
                self::refreshToken($client, $company_id);
            }
        }


        return $client;
    }

    /**
     * @param int $company_id
     * @return bool
     */
    public static function checkAccessToken($company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        if (file_exists(Url::to(self::GOOGLE_TOKEN_DIR . 'token_'. $company_id . '.json'))){
            return true;
        }
        return false;
    }

    /**
     * @param object $client Google client
     * @param int $company_id
     * @return bool
     */
    private static function setToken($client, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }
        $access_token = Json::decode(file_get_contents(Url::to(self::GOOGLE_TOKEN_DIR . 'token_'. $company_id . '.json')));
        $client->setAccessToken($access_token);
        return true;
    }

    /**
     * Обновляет устаревший access токен
     *
     * Отмена регистрации в приложении (для получения refresh_token) https://myaccount.google.com/u/0/permissions.
     * @param object $client Google_client
     * @param int $company_id
     * @return object
     */
    private static function refreshToken($client, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }

        // При обновлении токена refresh токен отсутствует
        // Поэтому сохраняем refresh токен в отдельную переменную
        $refresh_token = $client->getRefreshToken();
        if (!$refresh_token) {
            //Если refresh токена нет в файле token.json берем из настроек
            $refresh_token = Google::getRefreshToken();
        }

        // Обновляем токен
        $client->fetchAccessTokenWithRefreshToken($refresh_token);

        // Создаём новую переменную, в которую помещаем новый обновлённый токен
        $new_access_token = $client->getAccessToken();

        if (isset($new_access_token['refresh_token'])){
            // Если в новом access токене нет refresh токена - добавляем в новый access токен старый refresh токен
            $new_access_token['refresh_token'] = $refresh_token;
        }

        // Устанавливаем новый токен
        $client->setAccessToken($new_access_token);

        //Сохраняем в файл
        file_put_contents(Url::to(self::GOOGLE_TOKEN_DIR . 'token_'. $company_id . '.json'), json_encode($client->getAccessToken()));

        return $client;
    }

    /**
     * @param string $code Код для получения токена
     * @param int $company_id
     * @return Google_Client|null
     * @throws \Google_Exception
     */
    public function getTokenWithCode($code, $company_id = null)
    {
        if (!$company_id){
            $company_id = Users::getCompanyIdForUser();
        }

        $token_path = Url::to(self::GOOGLE_TOKEN_DIR . 'token_'. $company_id . '.json');
        $client = $this->clientInit();
        $access_token = $client->fetchAccessTokenWithAuthCode($code);

        Yii::info('Redirect URI: ' . $client->getRedirectUri(), 'test');

        if (array_key_exists('error', $access_token)){
            Yii::error($access_token, __METHOD__);
            Yii::$app->session->setFlash('error', 'Ошибка получения токена. ' . $access_token['error']);

            return null;
        }

        Yii::info($access_token, 'test');
        Yii::info('Code: ' . $code, 'test');

        file_put_contents($token_path, json_encode($client->getAccessToken()));

        $model = Settings::find()->where(['key' => 'google_refresh_token'])->one() ?? null;

        if (!$model) {
            $model = new Settings([
                'key' => 'google_refresh_token',
                'name' => 'Токен для обновления токена доступа',
                'company_id' => $company_id,
            ]);
        }

        if (isset($access_token['refresh_token'])){

            $model->value = $access_token['refresh_token'];

            Yii::info('Refresh Token: ' . $model->value, 'test');

            if (!$model->save()){
                Yii::error($model->errors, __METHOD__);
                return null;
            }
        }

        return $client;
    }
}