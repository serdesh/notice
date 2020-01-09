<?php

/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $contact_model app\models\Contact */

?>
<div class="users-create">
    <?= $this->render('_form', [
        'model' => $model,
        'contact_model' => $contact_model,
    ]) ?>
</div>
