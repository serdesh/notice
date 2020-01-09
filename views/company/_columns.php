<?php

use app\models\Company;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

$template = '{view} {update} {delete}';

if (Users::isSuperManager()) $template = '{delete}';

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
        'attribute' => 'name',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'director',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'inn',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'filter' => [1 => 'Да', 0 => 'Нет'],
        'attribute' => 'enabled',
        'value' => function (Company $data) {
            if ($data->id == 1){
                //Если супер компания - кнопку не показываем
                return '';
            }
            if ($data->enabled) {
                return Html::a('Да', '#', [
                    'id' => 'enabled-btn',
                    'data-id' => $data->id,
                    'class' => 'btn btn-success btn-block'
                ]);
            } else {
                return Html::a('Нет', '#', [
                    'id' => 'enabled-btn',
                    'data-id' => $data->id,
                    'class' => 'btn btn-danger btn-block'
                ]);
            }
        },
        'format' => 'raw',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'notes',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'value' => function (\app\models\Company $model){
            //Если юзер не суперадмин
            if (!Users::isSuperAdmin()) return null;
            //Есди юзер является пользователем компании
            if ($model->id == Yii::$app->user->identity->company_id) return '';
            return Html::a('Вход под клиентом', ['/site/login', 'company' => $model->id], [
                'class' => 'btn btn-info',
            ]);
        },
        'format' => 'raw',
        'vAlign' => 'middle',
        'hAlign' => 'center'
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'template' => $template,
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

