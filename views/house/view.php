<?php

use app\models\Functions;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\House */
/* @var $doc_model app\models\Document */
?>
<div class="house-view">

    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'attribute' => 'address',
                ],
                'fias_number',
                'cadastral_number',
                'additional_info:ntext',
                'residential_number',
                'non_residential_number',
                [
                    'attribute' => 'company_id',
                    'label' => 'Компания',
                    'value' => $model->company->name,
                ],
            ],
        ]);
        echo DetailView::widget([
            'model' => $doc_model,
            'attributes' => [
                [
                    'attribute' => 'name',
                    'label' => 'Документ',
                    'value' => function (\app\models\Document $model) {
                        return Html::a($model->name,
                            Url::to([
                                'download',
                                'file' => $model->local_path,
                                'name' => $model->name . Functions::getExtension($model->local_path),
                            ])
                        );
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
