<?php

//use johnitvn\ajaxcrud\CrudAsset;
use app\models\Settings;
use yii\helpers\Url;
use yii\httpclient\Client;

$data = [
    'response_type' => 'token',
    'client_id' => Settings::getValueByKey('yandex_disk_client_id'), //ID приложения яндекс диска
    'redirect_uri' => Url::to('@web/drive/yandex/yandex-disk', true),
];

Yii::info(http_build_query($data), 'test');

$client = new Client(['baseUrl' => 'https://oauth.yandex.ru']);
$response = $client->createRequest()
    ->setUrl('/authorize?' . http_build_query($data))
    ->addHeaders(['content-type' => 'application/json'])
//    ->setContent()
    ->send();

echo $response->content;

