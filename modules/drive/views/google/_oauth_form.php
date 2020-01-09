<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.04.2019
 * Time: 16:03
 */

/* @var string $url URL авторизации */

use GuzzleHttp\Client;

if ($url) {
    $guzzle_client = new Client();
    $result = $guzzle_client->request('GET', $url)->getBody();
    if (strstr($result, 'errorDescription')) {
        echo '<h1>Ошибка!</h1>';
        echo \yii\helpers\Html::a($url, $url);
    } else {
        echo $result;
    }
} else {
    echo 'Нет данных';
}