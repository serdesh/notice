<?php

use app\models\Users;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\EmailSettings */
?>
<div class="email-settings-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'attribute' => 'user_id',
                    'value' => Users::getShortName($model->user_id),
                ],
                [
                    'attribute' => 'type',
                    'value' => $model->getType($model->type),
                ],
                'incoming_server:ntext',
                'smtp_server:ntext',
                'login',
                'password',
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getMessage(), 'error');
    } ?>

</div>
