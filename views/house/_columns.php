<?php

use app\models\Company;
use app\models\House;
use app\models\Users;
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
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
         'attribute' => 'company_id',
         'label' => 'Компания',
         'filter' => Company::getList(),
         'value' => function(House $model){
                return $model->company->name ?? null;
         },
         'visible' => Users::isSuperAdmin(),
     ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'address',
        'label' => 'Адрес',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'fias_number',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'cadastral_number',
    ],
//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'residential_number',
//    ],
//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'non_residential_number',
//    ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'additional_info',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'document_id',
    // ],

    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title' => 'Просмотр','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title' => 'Редактировать', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title' => 'Удалить',
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Вы уверены?',
                          'data-confirm-message'=>'Вы уверены, что хотите удалить эту запись?'],
    ],

];   