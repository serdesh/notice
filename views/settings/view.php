<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Settings */
?>
<div class="settings-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                'key',
                'value',
                [
                    'attribute' => 'user_id',
                    'label' => 'Настройка пользователя',
                    'value' => \app\models\Users::getShortName($model->user_id),
                ],
                [
                    'attribute' => 'company_id',
                    'label' => 'Настройка компании',
                    'value' => \app\models\Company::findOne($model->company_id)->name ?? null,
                ],
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
