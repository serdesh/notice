<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $contact_model app\models\Contact */
?>
<div class="users-view">

    <div class="col-xs-12">
        <?php
        try {
            echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //                'id',
                    'fio',
                    'login',
                    'email',
                    [
                        'attribute' => 'permission',
                        'value' => $model->getRoleDescription(),
                    ],
                ],
            ]);
            echo DetailView::widget([
                'model' => $contact_model,
                'attributes' => [
                    'address',
                    'phone',
                ],
            ]);
        } catch (Exception $e) {
            Yii::error($e->getTraceAsString(), '_error');
            Yii::$app->session->setFlash('error', $e->getMessage());
        } ?>
    </div>

</div>
