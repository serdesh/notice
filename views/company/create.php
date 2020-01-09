<?php

/* @var $this yii\web\View */
/* @var $model app\models\Company */
/* @var $contact_model app\models\Contact */

?>
<div class="company-create">
    <?= $this->render('_form', [
        'model' => $model,
        'contact_model' => $contact_model,
    ]) ?>
</div>
