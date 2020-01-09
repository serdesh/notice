<?php

/* @var $this yii\web\View */
/* @var $model app\models\Company */
/* @var $contact_model app\models\Contact */
?>
<div class="company-update">

    <?= $this->render('_form', [
        'model' => $model,
        'contact_model' => $contact_model,
    ]) ?>

</div>
