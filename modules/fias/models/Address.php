<?php

namespace app\modules\fias\models;

use GuzzleHttp\Client;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Address
{

    public static function getRegionsList()
    {
        $content = self::request('addrobj');
        return ArrayHelper::map($content, 'aoguid', 'offname');
    }

    /**
     * Получает районы региона/области
     * @param string $region_aoguid ID региона
     * @return array
     */
    public static function getAreasList($region_aoguid)
    {
        $content = self::request('addrobj/' . $region_aoguid);
        return ArrayHelper::map($content, 'aoguid', 'offname');
    }

    /**
     * Получает получает насленные пункты района
     * @param string $area_aoguid ID региона
     * @return array
     */
    public static function getCitiesList($area_aoguid)
    {
        $content = self::request('addrobj/' . $area_aoguid);
        return ArrayHelper::map($content, 'aoguid', 'offname');
    }

    /**
     * Получает улицы населенного пункта
     * @param string $city_aoguid ID города
     * @return array
     */
    public static function getStreetsList($city_aoguid)
    {
        $content = self::request('addrobj/' . $city_aoguid);
        return ArrayHelper::map($content, 'aoguid', 'offname');
    }

   /**
     * Получает список домов улицы
     * @param string $street_aoguid ID улицы
     * @return array
     */
    public static function getHousesList($street_aoguid)
    {
        $content = self::request('house/' . $street_aoguid);
        return ArrayHelper::map($content, 'houseguid', 'housenum');
    }

    /**
     * Возвразает декодированный реззультат запроса
     * @param string $url Добавка адреса к основному адресу. Основной адрес http://basicdata.ru/api/json/fias/
     * @return mixed
     */
    private static function request($url)
    {
        $client = new Client(['base_uri' => 'http://basicdata.ru/api/json/fias/']);
        $response = $client->get($url);

        $content = Json::decode($response->getBody()->getContents());
        if ($content['data']){
            return $content['data'];
        }

        return $content;

    }

    public static function getTest()
    {
        //КО - 15784a67-8cea-425b-834a-6afe0e3ed61c
        //ШМР - a031240c-d73d-4c40-b839-fd880d6a777c
        //Шарья - c78b9b9e-ace7-46d0-9f6f-c259d7f65e4f
        //Ленина - f7341860-a390-4fce-914d-db9be19ec416
        //дом 100 - 3f700ee5-52f4-4088-b48a-9eba5e32dd9f

        //Связь по полю "aoguid"
//        $content = self::request('addrobj'); //Регионы
//        $content = self::request('addrobj/15784a67-8cea-425b-834a-6afe0e3ed61c'); //Районы региона
        $content = self::request('addrobj/a031240c-d73d-4c40-b839-fd880d6a777c'); //Населенные пункты района
//        $content = self::request('addrobj/c78b9b9e-ace7-46d0-9f6f-c259d7f65e4f'); //Улицы населенного пункта
//        $content = self::request('house/05d5b361-6f80-495c-8cac-8b8e6f102fcf'); //информация о номерах отдельных домов, владений, домовладений, корпусов, строений и земельных участках по aoguid улицы
//        $content = self::request('houseint/f7341860-a390-4fce-914d-db9be19ec416'); //информация об интервалах номеров домов на улице
//        $content = self::request('hststat'); //Состояния дома (Корпус отселяется (Отс), Объединение ранее учтенных (Объ), без особого состояния)
//        $content = self::request('eststat'); //Признак владения (Не определено, Владение, Дом, Домовладение)
//        $content = self::request('intvstat'); // (Не определено, Обычный, Четный, Нечетный)
//        $content = self::request('landmark/0097c218-c9de-4bf0-915f-8b84a2bec231'); //дополнительное неформализованное текстовое описание места расположения объекта адресации относительно ориентиров на местности
//        $content = self::request('normdoc/000004aa-0379-4b18-b6b3-14cef3c6bf20'); //Документ
//        $content = self::request('operstat'); //Причина появления записи в БД (Присоединение адресного объекта (слияние), Переподчинение и пр.)
//        $content = self::request('socrbase'); //Наименования типов (округ, область, район, улица, бульвар и т.д. и т.п.)
//        $content = self::request('strstat'); //Статус (Строени, сооружение, литер, не определено)
        return $content;


    }
}