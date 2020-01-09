<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

//$this->title = $name;
?>
<section class="content error_page">

    <div class="container">
        <div class="row">
            <div class="col-xs-3"></div>
            <div class="col-xs-6">
                <div class="error-content">
                    <h4>
                        Сожалеем, возникла непредвиденная ошибка ;(
                    </h4>
                    <h4 class="text-danger">
                        <?= $name ?> <?= nl2br(Html::encode($message)) ?>
                    </h4>
                    <p>
                        <a href=<?= Yii::$app->homeUrl ?>>Вернуться к главной странице</a>
                    </p>
                </div>
            </div>
            <div class="col-xs-3"></div>
        </div>
    </div>

</section>
