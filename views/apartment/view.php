<?php

use app\models\Room;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Apartment */
?>
<div class="apartment-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'attribute' => 'address',
                    'value' => $model->house->address,
                ],
                'number',
                'cadastral_number',
                [
                    'attribute' => 'room_number',
                    'value' => Room::getListForApartment($model->id),
                ],
                ['attribute' => 'is_residential',
                'value' => $model->is_residential ? 'Жилое' : 'Не жилое'
            ],
        ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
