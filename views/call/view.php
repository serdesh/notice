<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Call */
?>
<div class="call-view">
 
    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'created_at',
                'phone_number',
                'petition_id',
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getMessage(), '_error');
    } ?>

</div>
