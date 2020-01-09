<?php

use app\components\BulkWidget;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CallSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = '&nbsp;';
$this->params['breadcrumbs'][] = 'Звонки';

CrudAsset::register($this);

?>
<div class="call-index">
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
//                            Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
//                                [
//                                    'role' => 'modal-remote',
//                                    'title' => 'Создать звонок',
//                                    'class' => 'btn btn-default'
//                                ]) .
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
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список звонков',
                    //                'before'=>'<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                    'after' => BulkWidget::widget([
                            'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить все',
                                ["bulkdelete"],
                                [
                                    "class" => "btn btn-danger btn-xs",
                                    'role' => 'modal-remote-bulk',
                                    'data-confirm' => false,
                                    'data-method' => false,// for overide yii data api
                                    'data-request-method' => 'post',
                                    'data-confirm-title' => 'Вы уверены?',
                                    'data-confirm-message' => 'Подтвердите удаление данного элемента'
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

<?php
$script = <<<JS
 $(document).ready(function() {
     var alert_msg = $('.alert-success'); 

     $(document).on('change', '.branch-petition', function(){
         var dd = $(this);
         var call_id = dd.attr('call-id');
         var petition_id = dd.val();
         $.post(
             '/call/set-petition',
             {
                 call_id: call_id,
                 petition_id: petition_id
             },
             function (response) {
                if (response['success'] === 'true'){
                    setAlert('success', 'Ветка сохранена', 3000);
                    $('#exec-' + call_id).text(response['executor']);
                    var add_btn = $('#add-btn-' + call_id);
                    add_btn.removeClass('btn-primary');
                    add_btn.removeClass('btn');
                    add_btn.removeAttr('href');
                    add_btn.text('Обращение создано')
                } else {
                    setAlert('danger', response['data'], 10000);
                }
             }
         );
         console.log(petition_id);
     });
     
     function setAlert(type, text, delay) {
         alert_msg.removeClass('alert-danger');
         alert_msg.removeClass('alert-success');
         if (type === 'success'){
             alert_msg.addClass('alert-success');
         } else {
             alert_msg.addClass('alert-danger');
         }
         alert_msg.text(text);
         alert_msg.slideToggle(400);
         alert_msg.delay(delay).slideToggle(400);
     }
 })
JS;

$this->registerJs($script, \yii\web\View::POS_READY);
?>