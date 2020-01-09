<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Компании';
$this->params['breadcrumbs'][] = 'Компании';

CrudAsset::register($this);

?>
    <div class="company-index">
        <div id="ajaxCrudDatatable">
            <?php try {
                echo GridView::widget([
                    'id' => 'crud-datatable',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'pjax' => true,
                    'columns' => require(__DIR__ . '/_columns.php'),
                    'toolbar' => [
                        [
                            'content' =>

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
                        'heading' => '<i class="glyphicon glyphicon-list"></i> Список компаний',
                    'before' =>  Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить компанию', ['create'],
                            [
                                'role' => 'modal-remote',
                                'title' => 'Создать компанию',
                                'class' => 'btn btn-info'
                            ]) ,
                        'after' =>
//                            BulkWidget::widget([
//                                'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Delete All',
//                                    ["bulkdelete"],
//                                    [
//                                        "class" => "btn btn-danger btn-xs",
//                                        'role' => 'modal-remote-bulk',
//                                        'data-confirm' => false,
//                                        'data-method' => false,// for overide yii data api
//                                        'data-request-method' => 'post',
//                                        'data-confirm-title' => 'Вы уверены?',
//                                        'data-confirm-message' => 'Вы уверены, что хотите удалить эту запись?'
//                                    ]),
//                            ]) .
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
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
<?php

$script = <<<JS
    $(document).ready(function() {
        
        $(document).on('click', '#enabled-btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var id =  btn.attr('data-id');
            var notes = '';
            
            btn.attr('disabled', 'true');
            
            if (btn.text() === 'Да'){
                notes = prompt('Укажите причину отключения компании');
                if (!notes){
                    alert('Не указана причина. Отключение компании отменено');
                    btn.removeAttr('disabled');
                    return;
                }
            }
            
            $.get(
                '/company/enabled',
                {
                    id: id,
                    notes: notes
                },
                function (response) {
                    if (response[0] === 'success'){
                        if (response[1] === 1){
                            btn.removeClass('btn-danger');
                            btn.addClass('btn-success');
                            btn.text('Да');
                        } else {
                            btn.removeClass('btn-success');
                            btn.addClass('btn-danger');
                            btn.text('Нет');
                        }
                        var tr = btn.parents('tr');
                        console.log(tr);
                        tr.find('[data-col-seq=6]').html(notes);
                    } else {
                        alert('Ошибка смены статуса. ' + response[1]);
                    }
                    btn.removeAttr('disabled');
                }
            )
        })
       
    })
JS;
$this->registerJs($script);