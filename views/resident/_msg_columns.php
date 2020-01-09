<?php

use app\models\Functions;
use app\models\Message;
use yii\helpers\Url;

/* @var $data app\models\Petition */
/* @var string $type Тип страницы */

return [
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'id',
        'label' => '№',
        'width' => '50px',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'created_at',
        'format' => 'dateTime',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'is_incoming',
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
        'label' => 'Тема',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'text',
        'format' => 'raw',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'from',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'attachments',
        'value' => function(Message $model){
            return Functions::getMailAttachments($model);
        },
        'format' => 'raw'
    ],

    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'template' => '{view}',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to(['message/' . $action, 'id' => $key]);
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