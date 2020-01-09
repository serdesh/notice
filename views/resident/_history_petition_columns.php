<?php

use app\models\Status;
use app\models\Users;
use yii\helpers\Html;

/* @var $data app\models\Petition */

return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '20px',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'created_at',
        'format' => 'dateTime',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'name',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'description',
    ],
];