<?php
/**
 * @var string $status Статус обращения
 * @var integer $petition_id ID обращения (он же номер обращения)
 * @var string $responsible Фамилия инициалы ответственного специалиста
 * @var string $answer Текст ответа
 */
?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Проверка статуса заявки</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
</head>
<body>
<div class="container text-center">
    <div class="row">
        <div class="col-xs-12">
            <p class="head-form-uk">Инофрмация по заявке №<?= str_pad($petition_id, 11, '0', STR_PAD_LEFT) ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <h5>Статус обращения: <?= $status ?></h5>
        </div>
        <?php if ($responsible): ?>
            <div class="col-xs-12">
                <h5>Ответственный специалист: <?= $responsible ?></h5>
            </div>
        <?php endif; ?>
        <?php if ($answer): ?>
            <div class="col-xs-12">
                <h5>Ответ по обращению: <?= $answer ?></h5>
            </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
