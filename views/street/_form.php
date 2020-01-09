<?php

use app\models\Type;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Street */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="street-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'type_id')->widget(kartik\select2\Select2::className(), [
                'data' => Type::getTypeList(),
                'options' => [
                    'placeholder' => 'Выберите тип улицы'
                ]
            ])->label('Тип') ?>
        </div>
        <div class="col-md-9">

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>



  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
