<?php

use app\models\Petition;
use johnitvn\ajaxcrud\CrudAsset;
use kartik\grid\GridView;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Petition */
/* @var $msg_DataProvider \yii\data\ActiveDataProvider */

CrudAsset::register($this);

?>
<div class="petition-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'header',
                'text:raw',
                [
                    'attribute' => 'trouble_id',
                    'label' => 'Неисправность',
                    'value' => $model->troubleShooting->trouble ?? null
                ],
                [
                    'attribute' => 'trouble_description',
                    'label' => 'Описание неисправности',
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'status_id',
                    'label' => 'Статус',
                    'value' => $model->status->name ?? null,
                ],
                [
                    'attribute' => 'specialist_id',
                    'label' => 'Специалист',
                    'value' => $model->specialist->fio ?? null,
                ],
                [
                    'attribute' => 'manager_id',
                    'label' => 'Менеджер',
                    'value' => $model->manager->fio ?? null,
                ],
                [
                    'attribute' => 'resident_id',
                    'value' => function(Petition $model){
                        if (isset($model->resident_id)){
                            return  $model->resident->getFullName() ?? null;
                        }
                        return null;
                    }
                ],
                [
                    'attribute' => 'where_type',
                    'label' => 'Метод занесения обращения',
                    'value' => $model->getWhereType() ?? null,
                ],
                'relation_petition_id',
                'execution_date:dateTime',
                [
                    'attribute' => 'created_by',
                    'label' => 'Составитель обращения',
                    'value' => $model->createdBy->fio ?? null,
                ],
                'created_at:dateTime',
                'answer:ntext',
                'closed_user_id',
                [
                    'attribute' => 'petition_type',
                    'value' => $model->getPetitionType() ?? null,
                ],
                [
                    'attribute' => 'address',
                    'value' => function(Petition $model){
                        if (isset($model->resident_id)) {
                            return $model->resident->getAddress() ?? null;
                        }
                        return null;
                    },
                ],
                [
                    'attribute' => 'additional_info',
                    'label' => 'Дополнительная информация',
                    'value' => Petition::getAdditionalInfo($model->resident_id) ?? null,
                ],
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    }
    ?>

</div>

<div class="petition-msg">
    <?php
    try {
        echo GridView::widget([
            'id' => 'crud-datatable',
            'dataProvider' => $msg_DataProvider,
//            'filterModel' => $searchModel,
            'pjax' => true,
            'columns' => require(__DIR__ . '/_msg_columns.php'),
            'toolbar' => [
                [
                    'content' => '{export}'
                ],
            ],
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'panel' => [
                'type' => 'primary',
                'heading' => '<i class="glyphicon glyphicon-list"></i> Список сообщений',
//                    'before' => $additional_filters,
                'after' => '<div class="clearfix"></div>',
            ]
        ]);
    } catch (Exception $e) {
        Yii::error($e->getTraceAsString(), '_error');
        Yii::$app->session->setFlash('error', $e->getMessage());
    }
    ?>
</div>
