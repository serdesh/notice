<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
?>
<div class="users-view">
    <div class="col-xs-3">
        <a href="<?=Yii::$app->request->referrer?>"><span class="fa fa-chevron-circle-left"></span>&nbsp;Назад</a>
        <a class="text-center" href="/users/update?id=<?=$model -> id?>"><span class="fa fa-pen-square"></span>&nbsp;Редактировать&nbsp;</a>
        <a class="pull-right" href="/users/delete?id=<?=$model -> id?>"><span class="fa fa-remove"></span>&nbsp;Удалить&nbsp;</a>
    </div>
    <div class="col-xs-6">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
//                'id',
                'fio',
                'login',
                'email',
                [
                    'attribute' => 'permission',
                    'value' => $model->getRoleDescription(),
                ],
                'access',
                'telephone',
            ],
        ]) ?>
    </div>
    <div class="col-xs-3"></div>

</div>
