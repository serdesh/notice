<?php

/* @var $this yii\web\View */
/* @var $model app\models\House */
/* @var $doc_model app\models\Document */
?>
<div class="house-update">

    <?= $this->render('_form', [
        'model' => $model,
        'doc_model' => $doc_model,
    ]) ?>

</div>
