<?php

use app\models\Functions;
use yii\bootstrap\Modal;
use johnitvn\ajaxcrud\CrudAsset;

/* @var $this yii\web\View */
/* @var \app\models\Petition $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Отчеты';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

$request = Yii::$app->request;

$date_start = $request->post('date_start');
$date_end = $request->post('date_end');
$specialist_id = $request->post('specialist');
$search_in_expired = $request->post('search_in_expired');

if ($date_start && $date_end) {
    $dataProvider->query->andWhere([
        'BETWEEN',
        'petition.created_at',
        Functions::getDateForDb($date_start) . ' 00:00:00',
        Functions::getDateForDb($date_end) . ' 23:59:59'
    ]);
};

if ($specialist_id) {
    $dataProvider->query->andWhere(['specialist_id' => $specialist_id]);
}

if ($search_in_expired) {
    $dataProvider->query->andWhere(['status_id' => 6]); //Просрочено
}

$dp_count = clone $dataProvider;
$petition_count = $dp_count->query->count(); //Общее количество обращений

$dp_expired = clone $dataProvider;
$petition_expired = $dp_expired->query->andWhere(['status_id' => 6])->count();

$dp_done = clone $dataProvider;
$petition_done = $dp_done->query
    ->andWhere(['status_id' => 3])//Решено
    ->count();

$dp_refused = clone $dataProvider;
$petition_refused = $dp_refused->query
    ->joinWith(['status s'])
    ->andWhere(['s.id' => 4])//Отменено
    ->count();

$dp_in_work = clone $dataProvider;
$petition_in_work = $dp_in_work->query
    ->joinWith(['status s'])
    ->andWhere(['s.id' => 2])//В работе
    ->count();

$dp_new = clone $dataProvider;
$petition_new = $dp_new->query
    ->andWhere(['status_id' => 1])
    ->count(); //Новое

$dp_archive = clone $dataProvider;
$petition_archive = $dp_archive->query
    ->andWhere(['status_id' => 5])//Архивировано
    ->count();

?>

<?php
require '_report_filter.php';
?>
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="report-index table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Всего обращений</th>
                        <th>Просрочено</th>
                        <th>Решено</th>
                        <th>Отменено</th>
                        <th>В работе</th>
                        <th>Новое</th>
                        <th>Архивировано</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th><?= $petition_count; ?></th>
                        <th><?= $petition_expired; ?></th>
                        <th><?= $petition_done; ?></th>
                        <th><?= $petition_refused; ?></th>
                        <th><?= $petition_in_work; ?></th>
                        <th><?= $petition_new; ?></th>
                        <th><?= $petition_archive; ?></th>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
