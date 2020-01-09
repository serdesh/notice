<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TroubleshootingPeriod */
?>
<div class="troubleshooting-period-view">
 
    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'trouble:ntext',
                'period',
                'description:ntext',
                'group',
            ],
        ]);
    } catch (Exception $e) {
      Yii::error($e->getMessage(), '_error');
    } ?>

</div>
