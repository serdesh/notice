<?php

use app\components\BulkWidget;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var int $house_id ID дома */


$dataProvider = new \yii\data\ActiveDataProvider([
    'query' => \app\models\Apartment::find()->andWhere(['house_id' => $house_id])?? null,
]);
$dataProvider->pagination = [
    'pageSize' => 50,
];


try {
    echo GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => require(__DIR__ . '/_expand_columns.php'),
        'toolbar' => [
            ['content' =>
                Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create', 'house_id' => $house_id],
                    ['role' => 'modal-remote', 'title' => 'Создать помещение', 'class' => 'btn btn-default']) .
                Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Сбросить сетку']) .
                '{toggleData}' .
                '{export}'
            ],
        ],
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'panel' => [
            'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-list"></i> Список помещений',
//                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
            'after' => BulkWidget::widget([
                    'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Удалить выделенные',
                        ["bulkdelete"],
                        [
                            "class" => "btn btn-danger btn-xs",
                            'role' => 'modal-remote-bulk',
                            'data-confirm' => false, 'data-method' => false,// for overide yii data api
                            'data-request-method' => 'post',
                            'data-confirm-title' => 'Вы уверены?',
                            'data-confirm-message' => 'Действительно удалить все выделенные элементы?',
                        ]),
                ]) .
                '<div class="clearfix"></div>',
        ]
    ]);
} catch (Exception $e) {
    Yii::error($e->getMessage(), '_error');
    echo 'Ошибка загрузки помещений';
}