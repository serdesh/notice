<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use \app\components\BulkWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\DocumentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Документы';
$this->params['breadcrumbs'][] = 'Документы';

CrudAsset::register($this);

?>
<div class="document-index">
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
                            ['role' => 'modal-remote', 'title' => 'Создать документ', 'class' => 'btn btn-default']) .
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
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список документов',
//                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
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
        }
        ?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
<?php
$script = <<<JS
    $(document).ready(function(){
        
        $(document).on('click', '#not-cloud-btn', function(){
            var cloud_btn = $(this);
            cloud_btn.text('Загрузка');
            cloud_btn.attr('disabled', 'disabled');
            var document_id = cloud_btn.attr('data-id');
            // console.log(document_id);
            $.post(
                '/document/send-to-cloud',
                {
                    document_id: document_id
                },
                function (response) {
                    if (response[0] === 'success'){
                        cloud_btn.removeClass('btn-warning');
                        cloud_btn.addClass('btn-success');
                        cloud_btn.text('Да');
                    } else {
                        cloud_btn.text('Нет');
                        cloud_btn.removeAttr('disabled');
                        alert(response[1]);
                    }
                }
            )
        })
    })

JS;

$this->registerJS($script, \yii\web\View::POS_READY);