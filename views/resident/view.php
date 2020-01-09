<?php

use app\models\Apartment;
use app\models\Contact;
use app\models\Resident;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Resident */
/* @var $contact_model app\models\Contact */
?>
<div class="resident-view">

    <?php try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'last_name',
                'first_name',
                'patronymic',
                [
                    'attribute' => 'owner',
                    'value' => $model->owner ? 'Да' : 'Нет',
                ],
                'birth_date:date',
                'snils',
                'related_degree',
                'additional_info:ntext',
                [
                    'attribute' => 'apartment_id',
                    'label' => 'Адрес',
                    'value' => (new Apartment())->getFullAddress($model->apartment_id),
                ],
                [
                    'attribute' => 'email',
                    'value' => function(Resident $model){
                        return $model->getAllEmails();
                    },
                ],
                [
                    'attribute' => 'phone',
                    'label' => 'Телефон',
                    'value' => function(Resident $model){
                        return implode(', ', Contact::getPhonesWithContact($model->contact->id));
                    },
                ],
            ],
        ]);
    } catch (Exception $e) {
        Yii::error($e->getMessage(), '_error');
    } ?>

    <?php
    try {
        echo GridView::widget([
            'rowOptions' => function ($model, $key, $index, $grid) {
                $background_color = '';
                if ($model->status_id == \app\models\Status::getStatusByName('Просрочено')) $background_color = '#ffb899';
                return [
                    'data-key-value' => $key,
                    'style' => 'background-color: ' . $background_color . ';'
                ];
            },
            'id' => 'crud-datatable',
            'dataProvider' => $dataProvider,
            'pjax' => true,
            'columns' => require(__DIR__ . '/_history_columns.php'),
            'toolbar' => [
                [
                    'content' =>
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
                'heading' => '<i class="glyphicon glyphicon-list"></i> История обращений',
//                    'before' => $additional_filters,
                'after' =>
//                    BulkWidget::widget([
//                        'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить все',
//                            ["bulkdelete"],
//                            [
//                                "class" => "btn btn-danger btn-xs",
//                                'role' => 'modal-remote-bulk',
//                                'data-confirm' => false,
//                                'data-method' => false,// for overide yii data api
//                                'data-request-method' => 'post',
//                                'data-confirm-title' => 'Вы уверены?',
//                                'data-confirm-message' => 'Вы уверены, что хотите удалить эту запись?'
//                            ]),
//                    ]) .
                    '<div class="clearfix"></div>',
            ]
        ]);

    } catch (Exception $e) {
        Yii::error($e->getMessage(), '_error');
    }
    ?>

</div>
<?php
//$script = <<<JS
//    $(document).ready(function() {
//        $(document).on('click', '#addition_btn', function(e) {
//            e.preventDefault();
//            alert('В разработке.');
//        })
//    })
//JS;
//$this->registerJs($script);