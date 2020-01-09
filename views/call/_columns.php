<?php

use app\models\Call;
use app\models\Status;
use app\models\Users;
use yii\helpers\Html;
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
        'format' => 'datetime',
        'attribute' => 'created_at',
        'vAlign' => 'middle',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'phone_number',
        'vAlign' => 'middle',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'petition_status',
        'filter' => Status::getList(true),
        'value' => function (Call $model) {
            if (!$model->petition_id) {
                return 'Новое';
            }
            return $model->petition->status->name ?? null;
        },
            'vAlign' => 'middle',
],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'petition_id',
//        'value' => function (Call $model) {
//                $root_petition_list = Petition::getRootPetitionsList();
//
//                $relation_petition_id = $model->petition_id ?? null;
//
//                return Html::dropDownList('branch_petition_dropdown', $relation_petition_id,  $root_petition_list, [
//                    'call-id' => $model->id,
//                    'class' => 'form-control branch-petition',
//                    'prompt' => 'Выберите ветку',
//                ]);
////            return $model->petition->header ?? null;
//        },
//        'format' => 'raw',
//        'vAlign' => 'middle',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'specialist_id',
        'filter' => Users::getListByPosition('specialist', Users::getCompanyIdForUser()),
        'value' => function (Call $model) {
//            $spec_id = $model->petition->specialist_id ?? null;
//            Yii::info('Специалист: ' . $spec_id, 'test');
//
//            return Html::dropDownList('spec_dropdown', $spec_id,  Users::getListByPosition('specialist', Users::getCompanyIdForUser()), [
//                'call-id' => $model->id,
//                'class' => 'form-control executor-petition',
//                'prompt' => 'Выберите специалиста',
//            ]);
            return '<p id="exec-' . $model->id . '">' . Users::getShortName($model->petition->specialist_id ?? null) . '</p>';
        },
        'format' => 'raw',
        'vAlign' => 'middle',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'value' => function (Call $model) {
            if (!$model->petition_id) {
                return Html::a(
                    'Создать обращение',
                    Url::to([
                        '/petition/create',
                        'call_id' => $model->id,
                    ]),
                    [
                        'id' => 'add-btn-' . $model->id,
                        'data-pjax' => 0,
                        'title' => 'Создать обращение',
                        'class' => 'btn btn-primary'
                    ]
                );
            }
            return 'Обращение создано';
        },
        'format' => 'raw',
        'vAlign' => 'middle',
    ],
//    [
//        'class' => 'kartik\grid\ActionColumn',
//        'dropdown' => false,
//        'vAlign'=>'middle',
//        'template' => '{add_petition}',
//        'buttons' => [
//            'add_petition' => function ($key, Call $model, $index) {
//                return Html::a(
//                    'Создать обращение',
//                    Url::to([
//                        '/petition/create',
//                        'call' => $model->id,
//                    ]),
//                    [
//                        'data-pjax' => 0,
//                        'title' => 'Создать обращение',
    //                        'class' => 'btn btn-primary'
    //                    ]
//                );
//            }
//        ],
//        'urlCreator' => function($action, $model, $key, $index) {
//                return Url::to([$action,'id'=>$key]);
//        },
//        'viewOptions'=>['role'=>'modal-remote','title' => 'Просмотр','data-toggle'=>'tooltip'],
//        'updateOptions'=>['role'=>'modal-remote','title' => 'Редактировать', 'data-toggle'=>'tooltip'],
//        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete',
//                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
//                          'data-request-method'=>'post',
//                          'data-toggle'=>'tooltip',
//                          'data-confirm-title'=>'Are you sure?',
//                          'data-confirm-message'=>'Are you sure want to delete this item'],
//    ],

];   