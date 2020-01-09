<?php

/* @var $this yii\web\View */
/* @var $model app\models\Resident */
/* @var $contact_model app\models\Contact */

?>
<div class="resident-create">
    <?= $this->render('_form', [
        'model' => $model,
        'contact_model' => $contact_model,
    ]) ?>
</div>
