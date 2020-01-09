<?php

use app\models\Apartment;
use app\models\Resident;
use app\models\Room;
use yii\helpers\Url;

return [
//    [
//        'class' => 'kartik\grid\CheckboxColumn',
//        'width' => '20px',
//    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'id',
    // ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'fio',
        'label' => 'ФИО',
        'value' => function ($data) {
            return (new Resident)->getFullName($data->id);
        }
    ],

//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'last_name',
//        'label' => 'ФИО',
//        'value' => function ($data) {
//            return (new Resident)->getFullName($data->id);
//        }
//    ],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'address',
//        'value' => function ($data) {
//            if (isset($data->room_id)) {
//                return (new Room)->getFullAddress($data->room_id);
//            } elseif ($data->apartment_id) {
//                return (new Apartment)->getFullAddress($data->apartment_id);
//            }
//            return null;
//        }
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'address',
        'filter' => \app\models\House::getAddresses(),
        'value' => function ($data) {
            if (isset($data->room_id)) {
                return (new Room)->getFullAddress($data->room_id);
            } elseif ($data->apartment_id) {
                return (new Apartment)->getFullAddress($data->apartment_id);
            }
            return null;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'owner',
        'filter' => [1 => 'Да', 0 => 'Нет'],
        'value' => function ($data) {
            if ($data->owner) {
                return 'Да';
            }
            return 'Нет';
        }
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'Просмотр', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Редактировать', 'data-toggle' => 'tooltip'],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => 'Удалить',
            'data-confirm' => false,
            'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Вы уверены?',
            'data-confirm-message' => 'Вы уверены, что хотите удалить эту запись?'
        ],
    ],

];   