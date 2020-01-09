<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/** @var $history_DataProvider ActiveDataProvider */
/** @var $msg_DataProvider ActiveDataProvider */
?>

<div class="petition-history">
    <?php
    try {
        echo GridView::widget([
            'id' => 'crud-datatable-history',
            'dataProvider' => $history_DataProvider,
//            'filterModel' => $searchModel,
            'pjax' => true,
            'columns' => require(__DIR__ . '/_history_petition_columns.php'),
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
                'heading' => '<i class="glyphicon glyphicon-list"></i> Список событий',
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
<div class="petition-msg">
    <?php
    try {
        echo GridView::widget([
            'id' => 'crud-datatable-messages',
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
