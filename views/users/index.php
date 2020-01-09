<?php

use app\models\Company;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\UsersSearch */

$company_name = Company::findOne(\app\models\User::getCompanyIdForUser())->name ?? null;

//$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = ['label' => 'Базы данных'];
$this->params['breadcrumbs'][] = 'Пользователи';


CrudAsset::register($this);

?>
<div class="atelier-index">
    <div id="ajaxCrudDatatable">
        <?php try {
            echo GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'responsiveWrap' => false,
                'options' => ['style' => 'font-size:12px;'],
                'columns' => require(__DIR__ . '/_columns.php'),
                'toolbar' => [
                    ['content' =>
                        Html::a('Создать', ['create'],
                            ['role' => 'modal-remote', 'title' => 'Создать', 'class' => 'btn btn-primary']) .
                        Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                            ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Обновить']) .
                        '{toggleData}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список пользователей. ',
                    'before' => '',
                    'after' =>
//                        Functions::getBulkButtonWidget() .
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
