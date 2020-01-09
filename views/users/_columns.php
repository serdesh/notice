<?php

use app\models\EmailSettings;
use app\models\User;
use app\models\Users;
use yii\helpers\Html;
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
//         [
//         'class'=>'\kartik\grid\DataColumn',
//             'label' => '#',
//         'attribute'=>'id',
//     ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'fio',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'company_id',
        'label' => 'Компания',
        'filter' => \app\models\Company::getList(),
        'value' => function ($data) {
            return $data->company->name ?? null;
        },
        'visible' => User::isSuperAdmin(),
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'permission',
        'value' => function ($data) {
            return (new User)->getRoleDescription($data->permission);
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'login',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'email',
        'value' => function (Users $model) {
            if (!$model->email) {
                return Html::a('Подключить почту', ['/email-settings/create', 'id' => $model->id], [
                    'role' => 'modal-remote',
                    'class' => 'btn btn-block btn-default'
                ]);
            }
            $email_checked = EmailSettings::find()->andWhere(['user_id' => $model->id])->one()->checked ?? null;
            $class = $email_checked ? 'btn btn-primary btn-block' : 'btn btn-warning btn-block';
            return Html::a($model->email, ['/email-settings/update', 'user_id' => $model->id], [
                'class' => $class,
                'role' => 'modal-remote',
            ]);
        },
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update} {delete}',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'Просмотр', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Изменить', 'data-toggle' => 'tooltip'],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => 'Удалить',
            'data-confirm' => false,
            'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Подтвердите действие',
            'data-confirm-message' => 'Вы уверены что хотите удалить этот элемент?',
        ],
    ],

];   