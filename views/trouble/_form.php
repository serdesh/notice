<?php

use app\models\TroubleshootingPeriod;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TroubleshootingPeriod */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="troubleshooting-period-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'trouble')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'period')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'group')->widget(\kartik\select2\Select2::className(), [
        'data' => TroubleshootingPeriod::getGroupsList(),
        'options' => [
            'value' => TroubleshootingPeriod::getIdTroubleGroup($model->group),

            'placeholder' => 'Выберите тип неисправности'
        ]
    ]) ?>


    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update',
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>
