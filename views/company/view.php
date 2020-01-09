<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Company */
/* @var $contact_model app\models\Contact */
?>
<div class="company-view">
 
    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                'director',
                'inn',
            ],
        ]);
        echo DetailView::widget([
            'model' => $contact_model,
            'attributes' => [
                'address',
                'email',
                'phone',
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
