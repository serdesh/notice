<?php

use app\models\Company;
use app\models\Petition;
use app\models\Resident;
use app\modules\api\models\V1;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var V1 $model */
/** @var Petition $petition_model */
/** @var Resident $resident_model */
/** @var integer $company_id */

$company_name = Company::findOne($model->company_id)->name ?? 'Управляющая компания';
if (!isset($upload)){
    $upload = new \app\models\UploadForm();
}
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Отправка обращения для <?= $company_name ?></title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12 text-right">
            <p class="head-form-uk">Отправка обращения. <?= $company_name ?></p>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'action' => Url::to(['@web/api/v1/petition'], true)
    ]); ?>

    <div class="row">
        <div class="col-sm-4 col-xs-12">
            <?= $form->field($resident_model, 'last_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-sm-4 col-xs-12">
            <?= $form->field($resident_model, 'first_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-sm-4 col-xs-12">
            <?= $form->field($resident_model, 'patronymic')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-8 col-sm-12">
            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">

    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <?= $form->field($petition_model, 'header')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6 col-sm-12">
            <?php
            $items = $petition_model->getTypeList();
            $items['2'] = 'Вопрос';
            ?>
            <?= $form->field($petition_model, 'petition_type')->dropDownList($items, [
                'placeholder' => 'Выберите тип'
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($petition_model, 'text')->textarea(['rows' => 5]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($upload, 'files[]')->fileInput(['multiple' => true])->label('Вложения'); ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'agreement')->checkbox(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-2 col-xs-12">
            <?= Html::submitButton('Отправить', [
                'class' => 'btn btn-block btn-success'
            ]) ?>
        </div>
    </div>


    <?= $form->field($model, 'company_id')->hiddenInput(['value' => $model->company_id])->label(false) ?>
    <?= $form->field($model, '_csrf')->hiddenInput(['value' => Yii::$app->request->getCsrfToken()])->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
</body>
</html>
