<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use johnitvn\ajaxcrud\BulkButtonWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\EmailSettingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Настройки почты';
$this->params['breadcrumbs'][] = 'Настройки почты';

CrudAsset::register($this);

?>
<div class="email-settings-index">
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
                                    'title' => 'Создать настройки почты',
                                    'class' => 'btn btn-default'
                                ]) .
                            Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                                ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Reset Grid']) .
                            '{toggleData}' .
                            '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Email Settings listing',
                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                    'after' => BulkButtonWidget::widget([
                            'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Delete All',
                                ["bulkdelete"],
                                [
                                    "class" => "btn btn-danger btn-xs",
                                    'role' => 'modal-remote-bulk',
                                    'data-confirm' => false,
                                    'data-method' => false,// for overide yii data api
                                    'data-request-method' => 'post',
                                    'data-confirm-title' => 'Are you sure?',
                                    'data-confirm-message' => 'Are you sure want to delete this item'
                                ]),
                        ]) .
                        '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), '_error');
        } ?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
