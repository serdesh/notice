<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Document */
?>
<div class="document-view">

    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                [
                    'attibute' => 'outer_id',
                    'label' => 'В облаке',
                    'value' => function ($data) {
                        $outer_id = $data->outer_id ?? null;
                        if ($outer_id) {
                            return Html::button('Да', ['class' => 'btn btn-success btn-sm']);
                        }
                        return Html::button('Нет', ['class' => 'btn btn-warning btn-sm']);
                    },
                    'format' => 'raw',
                ],
                'local_path',
                [
                    'class' => '\kartik\grid\DataColumn',
                    'attribute' => 'created_by',
                    'label' => 'Создатель',
                    'value' => function ($data) {
                        return $data->user->fio ?? null;
                    },
                    'vAlign' => 'middle',
                ],
                'created_at:dateTime',
                'updated_at:date',
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
