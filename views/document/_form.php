<?php

use app\models\House;
use kartik\file\FileInput;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Document */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="document-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
    echo $form->field($model, 'house')
        ->widget(Select2::className(), [
            'data' => House::getAddresses(),
            'options' => [
                'placeholder' => 'Выберите дом',
            ]
        ])->label('Дом')
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php // echo $form->field($model, 'outer_id')->textInput(['maxlength' => true]) ?>

    <?php // echo $form->field($model, 'local_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'file')->widget(FileInput::classname(), [
        'options' => [
            'multiple' => false,
        ],
        'pluginOptions' => [
            'uploadUrl' => Url::to(['/document/upload']),
            'showPreview' => false,
        ]
    ])->label('Выберите файл документа и нажмите "Загрузить"'); ?>

	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
