<?php

/** @var int $petition_id ID обращения*/
/** @var int $company_id ID компании*/

use yii\helpers\Html;
use yii\helpers\Url;

?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Результат отправки сообщения</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12 text-center">
            <p class="head-form-uk"> Спасибо! Ваша заявка принята. Ваш номер - <b>[<?=  str_pad($petition_id, 11, '0',
                        STR_PAD_LEFT) ?>]</b></p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6 text-center">
            <?= Html::a('Подать новое обращение', Url::to(['@web/api/v1/widget', 'company' => $company_id]), [
                    'class' => 'btn btn-info'
            ]); ?>
        </div>
        <div class="col-xs-6 text-center">
            <?= Html::a('Проверить статус обращения', Url::to(['@web/api/v1/check-form']), [
                'class' => 'btn btn-info'
            ]); ?>
        </div>
    </div>
</div>
</body>
</html>