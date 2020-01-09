<?php

/* @var $this yii\web\View */

use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;

//$this->title =  Yii::$app->name;
?>
<div class="site-index">

    <div class="body-content">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="text-center">Импорт сведений о многоквартирных домах</h2>
                    </div>
                    <div class="import-form col-md-6 col-md-offset-3">

                        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                        <div class="col-md-12">
                            <?= $form->field($model, 'file')->widget(FileInput::classname(), [
                                'options' => [
                                    'multiple' => false,
                                ],
                                'pluginOptions' => [
                                    'showPreview' => false,
                                ]
                            ])->label('Выберите файл для импорта и нажмите "Загрузить"'); ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
