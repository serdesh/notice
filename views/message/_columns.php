<?php

use app\models\Message;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

$template = '{view} {delete}';
if (!User::isAdmin()){
    $template = '{view}';
}

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
        'attribute' => 'created_at',
        'format' => 'datetime',
        'label' => 'Дата'
    ],
    [
        'attribute' => 'is_incoming',
        'filter' => [0 => 'Исходящее', 1 => 'Входящее'],
        'value' => function (Message $model) {
            if ($model->is_incoming === 1) {
                return 'Входящее';
            } elseif($model->is_incoming === 0) {
                return 'Исходящее';
            }
            return null;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'header',
        'value' => function(Message $model){
            return Html::a($model->header, ['/message/view', 'id' => $model->id], [
                'data-pjax' => 0,
                'role' => 'modal-remote'
            ]);
        },
        'format' => 'raw',
    ],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'text',
//        'format' => 'raw',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'petition_id',
    ],

    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'template' => $template,
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