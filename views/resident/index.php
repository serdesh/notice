<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ResidentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if (Yii::$app->controller->action->id == 'unrelated') {
    $this->params['breadcrumbs'][] = 'Жильцы "Без жилья"';
} else {
    $this->params['breadcrumbs'][] = 'Жильцы';
}
//$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="resident-index">
    <div id="ajaxCrudDatatable">
        <?php
        try {
            echo GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => require(__DIR__ . '/_columns.php'),
                'toolbar' => [
                    [
                        'content' =>
                            Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                                [
                                    'role' => 'modal-remote',
                                    'title' => 'Создать нового жильца',
                                    'class' => 'btn btn-default'
                                ]) .
                            Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                                ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Сбросить таблицу']) .
                            '{toggleData}' .
                            '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список жильцов',
                    'before' => Html::a('Показать жильцов, не привязанных к помещению', ['unrelated'], [
                        'class' => 'btn btn-info',
                        'style' => Yii::$app->controller->action->id == 'unrelated' ? 'display:none' : '',
                    ]),
                    'after' =>
//                        BulkWidget::widget([
//                            'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить все',
//                                ["bulkdelete"],
//                                [
//                                    "class" => "btn btn-danger btn-xs",
//                                    'role' => 'modal-remote-bulk',
//                                    'data-confirm' => false, 'data-method' => false,// for overide yii data api
//                                    'data-request-method' => 'post',
//                                    'data-confirm-title' => 'Вы уверены?',
//                                    'data-confirm-message' => 'Вы уверены, что хотите удалить эту запись?'
//                                ]),
//                        ]) .
                        '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            Yii::error($e->getTraceAsString(), '_error');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        ?>
    </div>
</div>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
