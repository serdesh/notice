<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use \app\components\BulkWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\StreetSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Улицы';
$this->params['breadcrumbs'][] = 'Улицы';

CrudAsset::register($this);

?>
<div class="street-index">
    <div id="ajaxCrudDatatable">
        <?php try {
            echo GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => require(__DIR__ . '/_columns.php'),
                'toolbar' => [
                    ['content' =>
                        Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                            ['role' => 'modal-remote', 'title' => 'Создание улицы', 'class' => 'btn btn-default']) .
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
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список улиц',
                    //                'before'=>'<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                    'after' => BulkWidget::widget([
                            'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить все',
                                ["bulkdelete"],
                                [
                                    "class" => "btn btn-danger btn-xs",
                                    'role' => 'modal-remote-bulk',
                                    'data-confirm' => false, 'data-method' => false,// for overide yii data api
                                    'data-request-method' => 'post',
                                    'data-confirm-title' => 'Вы уверены?',
                                    'data-confirm-message' => 'Вы уверены, что хотите удалить эту запись?'
                                ]),
                        ]) .
                        '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            Yii::error($e->getTraceAsString(), '_error');
            Yii::$app->session->setFlash('error', $e->getMessage());
        } ?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
