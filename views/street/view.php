<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Street */
?>
<div class="street-view">
 
    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'type_id',
                'name',
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
