<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Message */
?>
<div class="message-view">
 
    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'header',
                'text:raw',
                'petition_id',
                'created_at:datetime',
                [
                  'attribute' => 'attachments',
                  'value' => function(\app\models\Message $model){
                        return \app\models\Functions::getMailAttachments($model);
                  },
                    'format' => 'raw',
                ],
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    } ?>

</div>
