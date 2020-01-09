<?php

use app\models\Petition;
use app\models\Status;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $data app\models\Petition */
/* @var string $type Тип страницы */

$template = '{update} {to_archive} {to_spam} {forward}';

if ($type == 'archive'){
    $template = '{view} {delete}';
}

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
//    [
//        'class' => 'kartik\grid\SerialColumn',
//        'width' => '30px',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'id',
        'label' => '№',
        'width' => '70px',
        'vAlign' => 'middle',
        'hAlign' => 'center',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'company_id',
        'label' => 'Компания',
        'filter' => \app\models\Company::getList(),
        'value' => function (Petition $model) {
            return $model->company->name ?? null;
        },
        'visible' => Users::isSuperAdmin(),
        'vAlign' => 'middle',
        'hAlign' => 'center',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'created_at',
        'format' => 'dateTime',
        'vAlign' => 'middle',
        'hAlign' => 'center',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'header',
        'vAlign' => 'middle',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'petition_type',
        'value' => function ($data) {
            return $data->petition_type == Petition::PETITION_TYPE_COMPLAINT ? 'Жалоба' : 'Обращение';
        },
        'vAlign' => 'middle',
        'hAlign' => 'center',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'phone_number',
        'label' => 'Номер телефона',
        'value' => function(Petition $model){
            return \app\models\Call::find()
                ->andWhere(['petition_id' => $model->id])
                ->one()
                ->phone_number ?? null;
        },
        'visible' => Users::isSpecialist(),
        'vAlign' => 'middle',
        'hAlign' => 'center',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'address',
//        'value' => function($data){
//            return Functions::parseAddress($data->address);
//        }
        'vAlign' => 'middle',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'status_id',
        'filter' => Status::getList(),
        'label' => 'Статус',
        'value' => function (Petition $model) {
            if ($model->status_id == 3 && !$model->answer && !Users::isSpecialist()){
                //Если заявка закрыта, но ответ не отправлен
                return "<span class='glyphicon glyphicon-exclamation-sign'> </span> Решено<br> Нет ответа заявителю!";
            }
            return Status::findOne($model->status_id)->name ?? null;
        },
        'visible' => $type == 'archive' ? false : true,
        'format' => 'raw',
        'vAlign' => 'middle',
        'hAlign' => 'center',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'specialist_id',
        'filter' => Users::getListByPosition(Users::USER_ROLE_SPECIALIST),
        'label' => 'Ответственный',
        'value' => function ($data) {
            return $data->specialist->fio ?? null;
        },
        'vAlign' => 'middle',
    ],
    //    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'text',
//    ],

//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'manager_id',
//    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'resident_id',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'where_type',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'relation_petition_id',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'execution_date',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'created_by',
    // ],

    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'answer',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'closed_user_id',
    // ],


    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'template' => $template,
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'buttons' => [
            'to_archive' => function ($url, $model) {
                if (!Users::isSpecialist()) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>',
                        ['send-archive', 'id' => $model->id], [
                            'title' => 'Архивировать',
                        ]);
                }
                return null;
            },
            'to_spam' => function ($url, $model) {
                if (!Users::isSpecialist()) {
                    return Html::a('<span class="glyphicon glyphicon-thumbs-down"></span>',
                        ['to-spam', 'id' => $model->id], [

                            'title' => 'Пометить как спам',
                        ]);
                }
                return null;
            },
            'forward' => function ($url, $model) {
                if (!Users::isSpecialist()) {
                    return Html::a('<span class="glyphicon glyphicon-forward"></span>',
                        ['/message/forward-by-petition', 'id' => $model->id], [
                            'title' => 'Перенаправить на другую почту',
                            'role' => 'modal-remote',
                            'data-toggle' => 'tooltip',
                        ]);
                }
                return null;
            },
        ],
//        'viewOptions' => ['role' => 'modal-remote', 'title' => 'Просмотр', 'data-toggle' => 'tooltip'],
        'viewOptions' => ['title' => 'Просмотр', 'data-toggle' => 'tooltip'],
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