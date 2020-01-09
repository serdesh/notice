<?php

use app\models\Petition;
use app\models\Status;
use app\models\Users;
use yii\helpers\Html;

/* @var $data app\models\Petition */

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'created_at',
        'format' => 'dateTime',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'header',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'status_id',
        'filter' => Status::getList(),
        'label' => 'Статус',
        'value' => function($data){
            return Status::findOne($data->status_id)->name ?? null;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'manager_id',
        'filter' => Users::getListByPosition(Users::USER_ROLE_MANAGER),
        'label' => 'Менеджер',
        'value' => function ($data) {
            return $data->specialist->fio ?? null;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'specialist_id',
        'filter' => Users::getListByPosition(Users::USER_ROLE_SPECIALIST),
        'label' => 'Специалист',
        'value' => function ($data) {
            return $data->specialist->fio ?? null;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'label' => '...',
        'value' => function(Petition $data){
            return Html::a('Дополнительно', ['history-petition', 'id' => $data->id],[
                'id' => 'addition_btn',
                'role' => 'modal-remote',
                'class' => 'btn btn-info btn-block',
            ]);
        },
        'format' => 'raw',
    ],
];