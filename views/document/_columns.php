<?php

use app\models\Company;
use app\models\Document;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
//    [
//        'class' => 'kartik\grid\SerialColumn',
//        'width' => '30px',
//        'vAlign' => 'middle',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'label' => '#',
        'attribute' => 'id',
        'width' => '40px',
        'vAlign' => 'middle',
        'hAlign' => 'center'
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'company_id',
        'label' => 'Компания',
        'filter' => Company::getList(),
        'value' => function (Document $model) {
            $user = Users::findOne($model->created_by);
            return $user->company->name ?? null;
        },
        'visible' => Users::isSuperAdmin(),
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'name',
        'vAlign' => 'middle',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'outer_id',
        'label' => 'В облаке',
        'value' => function (Document $data) {
            $outer_id = $data->outer_id ?? null;
            if ($outer_id) {
                return Html::button('Да', [
                    'class' => 'btn btn-success btn-sm btn-block',
                    'disabled' => true,
                ]);
            }
            return Html::button('Нет', [
                'class' => 'btn btn-warning btn-sm btn-block',
                'id' => 'not-cloud-btn',
                'data-id' => $data->id,
            ]);
        },
        'format' => 'raw',
        'width' => '20px',
        'vAlign' => 'middle',
    ],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'local_path',
//        'vAlign' => 'middle',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'house_id',
        'label' => 'Адрес дома',
        'value' => function (Document $model) {
            return $model->house->address ?? null;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'created_by',
        'label' => 'Создатель',
        'value' => function ($data) {
            return $data->user->fio ?? null;
        },
        'vAlign' => 'middle',
    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'created_at',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'updated_at',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'template' => '{download} {view} {update} {delete}',
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'buttons' => [
            'download' => function ($key, Document $model, $index) {
                return Html::a(
                    '<span class="glyphicon glyphicon-download">',
                    Url::to([
                        '/document/download',
                        'id' => $model->id,
                    ]),
                    [
                        'data-pjax' => 0,
                        'title' => 'Скачать',
                    ]
                );
            }
        ],
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


