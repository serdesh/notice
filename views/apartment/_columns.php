<?php

use app\models\Apartment;
use app\models\Company;
use app\models\Users;
use kartik\grid\GridView;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],

    [
        'class' => 'kartik\grid\ExpandRowColumn',
        'width' => '50px',
        'value' => function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'detail' => function ($model, $key, $index, $column) {
            return \Yii::$app->controller->renderPartial('_expand-apartment', ['house_id' => $model->house_id]);
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'company_id',
        'label' => 'Компания',
        'filter' => Company::getList(),
        'value' => function(Apartment $model){
            return $model->house->company->name ?? null;
        },
        'visible' => Users::isSuperAdmin(),
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'address',
        'label' => 'Адрес',
        'value' => function(app\models\Apartment $data){
            return $data->house->address;
        }
    ],
        // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'id',
    // ],

//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'number',
//    ],
//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'rooms',
//        'value' => function(app\models\Apartment $model){
//            return Room::getListForApartment($model->id);
//        }
//    ],
//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'is_residential',
//        'filter' => [1 => 'Жилое', 2 => 'Не жилое'],
//        'value' => function($data){
//            if ($data->is_residential == 1) return 'Жилое';
//            return 'Не жилое';
//        }
//    ],
//    [
//        'class' => 'kartik\grid\ActionColumn',
//        'dropdown' => false,
//        'vAlign'=>'middle',
//        'urlCreator' => function($action, $model, $key, $index) {
//                return Url::to([$action,'id'=>$key]);
//        },
//        'viewOptions'=>['role'=>'modal-remote','title' => 'Просмотр','data-toggle'=>'tooltip'],
//        'updateOptions'=>['role'=>'modal-remote','title' => 'Редактировать', 'data-toggle'=>'tooltip'],
//        'deleteOptions'=>['role'=>'modal-remote','title' => 'Удалить',
//                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
//                          'data-request-method'=>'post',
//                          'data-toggle'=>'tooltip',
//                          'data-confirm-title'=>'Вы уверены?',
//                          'data-confirm-message'=>'Вы уверены, что хотите удалить эту запись?'],
//    ],

];   