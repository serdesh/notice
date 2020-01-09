<?php

use app\models\User;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Company */
/* @var $contact_model app\models\Contact */
/* @var $form yii\widgets\ActiveForm */



?>

    <div class="company-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'director')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'inn')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($contact_model, 'email')->textInput() ?>
            </div>
            <div class="col-md-4">
                <?php if (!$model->password): ?>
                    <?= $form->field($model, 'password')->textInput([
                        'id' => 'input-password',
                        'title' => "Требования к паролю: 
                        Не короче 6 символов, 
                        пароль может содержать только латинские строчные и заглавные буквы, цифры и спецсимволы, 
                        пароль должен содержать как минимум одну строчную букву, 
                        как минимум одну заглавную букву и как минимум одну цифру
                        ",
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'new_password')->textInput([
                        'id' => 'input-password',
                        'title' => "Требования к паролю: 
                        Не короче 6 символов, 
                        пароль может содержать только латинские строчные и заглавные буквы, цифры и спецсимволы, 
                        пароль должен содержать как минимум одну строчную букву, 
                        как минимум одну заглавную букву и как минимум одну цифру
                        ",
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($contact_model, 'address')
                    ->textInput(['id' => 'address-str'])
                    ->hint('Например: мск новокузнецкая 15. После ввода, для подстановки адреса нажимте "Enter"') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($contact_model, 'phone')->textInput() ?>
            </div>
        </div>

        <?php if(User::isAdmin()): ?>

        <div class="row">
            <div class="col-md-12">
                <?php
//                if (!$model->isNewRecord) {
//                    $model->houses = House::getHousesByCompany($model->id);
//                    $model->employee = User::getUsersByCompany($model->id);
//                }
//                echo $form->field($model, 'houses')->widget(Select2::className(), [
//                    'data' => $model->isNewRecord ? House::getFreeHouses() : House::getHousesByCompanyAndFreeHouses($model->id),
//                    'options' => [
//                        'placeholder' => 'Выберите дома...',
//                        'multiple' => true,
//                    ],
//                    'pluginOptions' => [
//                        'allowClear' => true
//                    ],
//                ])->label('Список домов')
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php
//                echo $form->field($model, 'employee')->widget(Select2::className(), [
//                    'data' => $model->isNewRecord ? User::getFreeUsers() : User::getUsersByCompanyAndFreeUsers($model->id),
//                    'options' => [
//                        'placeholder' => 'Выберите сотрудников...',
//                        'multiple' => true,
//                    ],
//                    'pluginOptions' => [
//                        'allowClear' => true
//                    ],
//                ])->label('Сотрудники')
                ?>
            </div>
        </div>

        <?php endif; ?>

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update',
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
        <?php } ?>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$script = <<<JS
    $(document).ready(function() {
        var input = $('#address-str');
        var field = $('.field-address-str');

        $('#input-password').tooltip();
        
        input.keydown(function(e){
            if (e.keyCode === 13){
                input.attr('disabled', 'true');
                e.preventDefault();
               $.get(
                    '/petition/check-address-on-dadata',
                    {
                        'address': input.val()
                    },
                    function (data) {
                        if (data[0] === 'success'){
                            field.removeClass('has-error');
                            field.addClass('has-success');
                            field.find('.help-block').text('');
                            input.val(data[1]);
                        } else {
                            field.removeClass('has-success');
                            field.addClass('has-error');
                            field.find('.help-block').text(data[1]);
                        }
                        input.removeAttr('disabled');
                    }
                )
            }
        });
    })
JS;
$this->registerJs($script);