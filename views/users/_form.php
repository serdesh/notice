<?php

use app\models\Company;
use app\models\Users;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $contact_model app\models\Contact */
/* @var $form yii\widgets\ActiveForm */

Users::isSuperAdmin() ? $num = 6 : $num = 12; //Величина блока col-md

$model->fake_login = $model->login;

?>

    <div class="users-form">

        <?php $form = ActiveForm::begin(); ?>
        <?php if (Users::isSuperAdmin() || (Users::isAdmin() && $model->id != Yii::$app->user->id)): ?>
            <div class="row">
                <div class="col-md-<?= $num; ?>">
                    <?php echo $form->field($model, 'permission')
                        ->dropDownList($model->getRoleList(), ['prompt' => 'Выберите должность'])->label(false);
                    ?>
                </div>
                <?php if (Users::isSuperAdmin()): ?>
                    <div class="col-md-6">
                        <?php echo $form->field($model, 'company_id')
                            ->dropDownList(Company::getList(), ['prompt' => 'Выберите компанию'])->label(false);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'fio')
                    ->textInput(['maxlength' => true, 'placeholder' => 'ФИО'])->label(false) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'snils')
                    ->textInput(['maxlength' => true, 'placeholder' => 'СНИЛС'])->label(false) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?php if (Users::isAdmin() && $model->id == Yii::$app->user->id): //Если админ редактирует сам себя?>
                    <?= $form->field($model, 'fake_login')
                        ->textInput(['maxlength' => true, 'placeholder' => 'Логин', 'disabled' => true])->label(false) ?>
                <?php else : ?>
                <?= $form->field($model, 'login')
                    ->textInput(['maxlength' => true, 'placeholder' => 'Логин'])->label(false) ?>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?= $model->isNewRecord ? $form->field($model, 'password')
                    ->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Пароль',
                        'id' => 'input-password',
                        'title' => "Требования к паролю: 
                        Не короче 6 символов, 
                        пароль может содержать только латинские строчные и заглавные буквы, цифры и спецсимволы, 
                        пароль должен содержать как минимум одну строчную букву, 
                        как минимум одну заглавную букву и как минимум одну цифру
                        ",
                    ])->label(false) :
                    $form->field($model, 'new_password')
                        ->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Новый пароль',
                            'id' => 'input-new-password',
                            'title' => "Требования к паролю: 
                        Не короче 6 символов, 
                        пароль может содержать только латинские строчные и заглавные буквы, цифры и спецсимволы, 
                        пароль должен содержать как минимум одну строчную букву, 
                        как минимум одну заглавную букву и как минимум одну цифру
                        ",
                        ])->label(false) ?>
            </div>
        </div>
        <?php if (isset($contact_model)): ?>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($contact_model, 'address')
                        ->textInput(['id' => 'address-str', 'placeholder' => 'Введите адрес'])
                        ->hint('Например: мск новокузнецкая 15. После ввода, для подстановки адреса нажимте "Enter"')
                        ->label(false)
                    ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'email')
                        ->textInput(['placeholder' => 'Email'])->label(false) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($contact_model, 'phone')
                        ->textInput(['placeholder' => 'Телефон'])->label(false) ?>
                </div>
            </div>
        <?php endif; ?>

        <?= $form->field($model, 'created_by',
            ['template' => '{input}'])->hiddenInput(['value' => Yii::$app->user->id]) ?>

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать',
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