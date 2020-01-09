<?php

/* @var $this yii\web\View */
/* @var $model app\models\Apartment */
/* @var $room_models app\models\Room */

?>
<div class="apartment-create">
    <?= $this->render('_form', [
        'model' => $model,
        'room_models' => $room_models,
    ]) ?>
</div>
