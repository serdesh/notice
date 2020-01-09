<?php

use app\models\Status;
use app\models\Users;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use \app\components\BulkWidget;

/* @var $this yii\web\View */
/* @var bool $is_archive */
/* @var $searchModel app\models\search\PetitionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$add_petition_btn = Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
    ['data-pjax' => 0, 'title' => 'Создать обращение', 'class' => 'btn btn-default', 'role' => 'modal-remote']);

if ($type == 'archive') {
    $this->params['breadcrumbs'][] = 'Архив обращений';
    $add_petition_btn = '';
} elseif ($type == 'complaint') {
    $this->params['breadcrumbs'][] = 'Жалобы';
} else {
    $this->params['breadcrumbs'][] = 'Обращения';
}

if (Users::isSpecialist()) {
    $add_petition_btn = '';
}

//$this->params['breadcrumbs'][] = $this->title;

$check_mail_btn = Html::a('Проверить почту', ['/petition/get-mail-for-company', 'id' => Users::getCompanyIdForUser()],
    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Сбросить таблицу']);
if (Users::isSpecialist()) {
    $check_mail_btn = '';
}

CrudAsset::register($this);

?>

<?php
if ($type == 'index') {
    require '_filter.php';
}
?>

<div class="petition-index">
    <div id="ajaxCrudDatatable">
        <?php
        try {
            echo GridView::widget([
                'rowOptions' => function ($model, $key, $index, $grid) {
                    $background_color = '';
                    if ($model->status_id == Status::getStatusByName('Просрочено')) {
                        $background_color = '#ffb899';
                    } elseif ($model->status_id == 1){ //Новое
                        $background_color = '#c1caca';
                    }
                    return [
                        'data-key-value' => $key,
                        'style' => 'background-color: ' . $background_color . ';'
                    ];
                },
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => require(__DIR__ . '/_columns.php'),
                'toolbar' => [
                    [
                        'content' =>
                            $add_petition_btn .
                            $check_mail_btn .
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
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список обращений',
//                    'before' => $additional_filters,
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
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
