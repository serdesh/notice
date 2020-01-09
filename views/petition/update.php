<?php

/* @var $this yii\web\View */
/* @var $model app\models\Petition */
/* @var $msg_DataProvider \yii\data\ActiveDataProvider */
?>
<div class="petition-update">

    <?= $this->render('_form', [
        'model' => $model,
        'msg_DataProvider' => $msg_DataProvider,
        'errors' => isset($errors) ? $errors : null,
    ]) ?>

</div>
