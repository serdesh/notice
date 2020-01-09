<?php

/* @var $this yii\web\View */

use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

//$this->title =  Yii::$app->name;
?>
<div class="site-index">

    <div class="body-content">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="text-center">Импорт сведений о лицевых счетах</h2>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4 class="text-danger text-bold">ВНИМАНИЕ!</h4>
                        <p>Перед импортом сведений о ЛС необходимо выполнить <?= Html::a('импорт сведений о МКД', ['/site/import-mkd']) ?> </p>
                        <p>Если этого не сделать, то жильцы не будут привязаны к домам и помещениям!</p>
                        <p>&nbsp;</p>
                    </div>
                    <div class="col-md-6 col-md-offset-3 import-form">

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
