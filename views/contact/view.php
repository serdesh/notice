<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */
?>
<div class="contact-view">
 
    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'address:ntext',
                'email:email',
                'phone',
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
