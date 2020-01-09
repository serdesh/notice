<?php

use app\modules\fias\models\Address;
use kartik\select2\Select2;
use gietos\yii\Dadata\Client;

?>

<div class="test-fias">
    <div class="row">
        <div class="col-md-2">
            <?php
            echo Select2::widget([
                'name' => 'regions',
                'data' => Address::getRegionsList(),
                'options' => [
                    'placeholder' => 'Выберите регион',
                    'onchange' => '
                var region_id = $(this).val();
                $.post(
                    "/address/address/get-areas",
                    {
                        id: region_id,
                    },
                    function(res){
                        $("#area-drop").removeAttr("disabled");
                        $("#area-drop").html(res);
                    }
                )
            ',
                ]
            ])
            ?>
        </div>
        <div class="col-md-2">
            <?php
            echo Select2::widget([
                'name' => 'areas',
                'id' => 'area-drop',
//        'data' => Address::getAreasList('15784a67-8cea-425b-834a-6afe0e3ed61c'),
                'options' => [
                    'placeholder' => 'Выберите район',
                    'disabled' => true,
                    'onchange' => '
                var area_id = $(this).val();
                $.post(
                    "/address/address/get-cities",
                    {
                        id: area_id,
                    },
                    function(res){
                        $("#city-drop").removeAttr("disabled");
                        $("#city-drop").html(res);
                    }
                )
            ',
                ]
            ])
            ?>
        </div>
        <div class="col-md-2">
            <?php
            echo Select2::widget([
                'name' => 'cities',
                'id' => 'city-drop',
//        'data' => Address::getCitiesList('a031240c-d73d-4c40-b839-fd880d6a777c'),
                'options' => [
                    'placeholder' => 'Выберите город',
                    'disabled' => true,
                    'onchange' => '
                var city_id = $(this).val();
                $.post(
                    "/address/address/get-streets",
                    {
                        id: city_id,
                    },
                    function(res){
                        $("#street-drop").removeAttr("disabled");
                        $("#street-drop").html(res);
                    }
                )
            ',
                ],
            ])
            ?>
        </div>
        <div class="col-md-2">
            <?php
            echo Select2::widget([
                'name' => 'streets',
                'id' => 'street-drop',
//        'data' => Address::getStreetsList('c78b9b9e-ace7-46d0-9f6f-c259d7f65e4f'),
                'options' => [
                    'placeholder' => 'Выберите улицу',
                    'disabled' => true,
                    'onchange' => '
                        var city_id = $(this).val();
                        $.post(
                            "/address/address/get-houses",
                            {
                                id: city_id,
                            },
                            function(res){
                                $("#house-drop").removeAttr("disabled");
                                $("#house-drop").html(res);
                            }
                        )
                    ',
                ],
            ])
            ?>

        </div>
        <div class="col-md-2">
            <?php
            echo Select2::widget([
                'name' => 'houses',
                'id' => 'house-drop',
//        'data' => Address::getHousesList('f7341860-a390-4fce-914d-db9be19ec416'),
                'options' => [
                    'placeholder' => 'Выберите дом',
                    'disabled' => true,
                ],
            ])
            ?>
        </div>
    </div>
<?php // \yii\helpers\VarDumper::dump(Address::getTest(), 25, true); ?>
    <?php
    $httpClient = new GuzzleHttp\Client([
        'base_uri' => 'https://dadata.ru/api',
    ]);
    $client_dadata = new Client($httpClient, [
        'token' => 'c2ae0bae1eaeb49d2994e4bc31be54543b871251',
        'secret' => '4a7e8448a6dc065bb54163b43c9b6e6786963b67',

    ]);

    $address = $client_dadata->cleanAddress('Кострома мира 33');
    echo 'Result: ' . $address->result . PHP_EOL;
    ?>
</div>
