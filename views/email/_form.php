<?php

use app\models\EmailSettings;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EmailSettings */
/* @var $form yii\widgets\ActiveForm */

if ($model->isNewRecord){
    $server = '';
    if ($model->email){
        $server = EmailSettings::getServer($model->email);
    }
    $model->incoming_server = 'imap.' . $server;
    $model->incoming_port = '993';
    $model->smtp_server = 'smtp.' . $server;
    $model->smtp_port = '465';
    $model->login = $model->email;
}
?>

<div class="email-settings-form">

    <?php if (isset($model->connect_errors)): ?>
        <div class="row">
            <div class="col-md-12">
                <?php Yii::info($model->connect_errors, 'test') ?>
                <?php // echo $model->connect_errors['mail_parameter_error']; ?>
                <p class="text-danger">Ошибка подключения. Проверьте настройки.</p>
                <p>
                    <?php
                    $error = explode('error:', $model->connect_errors['mail_parameter_error']);
                    echo '<small>' . $error[1] . '</small>'; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
    <?php Yii::info($model->toArray(), 'test'); ?>
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-md-9">
            <?= $form->field($model, 'email')->textInput([
                'disabled' => $model->email ? true : false,
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'as_default')->dropDownList([0 => 'Нет', 1 => 'Да']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'incoming_server')->textInput() ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'incoming_port')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'smtp_server')->textInput() ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'smtp_port')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'protection')
                ->dropDownList([
                    EmailSettings::EMAIL_PROTECTION_SSL => 'SSL',
                    EmailSettings::EMAIL_PROTECTION_TLS => 'TLS'
                ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'type')
                ->dropDownList([EmailSettings::EMAIL_TYPE_IMAP => 'IMAP', EmailSettings::EMAIL_TYPE_POP => 'POP']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update',
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>
