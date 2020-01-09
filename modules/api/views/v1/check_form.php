<?php

use yii\helpers\Url;

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Проверка статуса заявки</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <p class="head-form-uk">Проверьте статус заявки</p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <form action="<?= Url::to('@web/api/v1/get-status', true); ?>" method="GET" class="form-inline" role="form">
                <div class="form-group">
                    <label class="sr-only" for="InputNumber">Email address</label>
                    <input type="text" name="number" class="form-control" id="InputNumber"
                           placeholder="Введите номер заявки">
                </div>
                <button type="submit" class="btn btn-default">Проверить</button>
            </form>
        </div>
    </div>

</div>
</body>
</html>
