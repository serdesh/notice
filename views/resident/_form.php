<?php

use app\models\Contact;
use app\models\House;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Resident */
/* @var $contact_model app\models\Contact */
/* @var $form yii\widgets\ActiveForm */

if (!$model->isNewRecord) {
    if (isset($model->apartment->id)) {
        $model->address = $model->apartment->getFullAddress();
    }
    $display_input = 'display: block;';
    $display_input_group = 'display: none;';
    $model->resident_emails = $model->getAllEmails();
} else {
    $display_input = 'display: none;';
    $display_input_group = 'display: block;';
}

$get = Yii::$app->request->get();

if($model->isNewRecord)
{
    if(isset($get['phone']))
    {
        $phone = $get['phone'];
    } else {
        $phone = '';
    }
} else {
    $phone = implode(', ', Contact::getPhonesWithContact($model->contact_id) ?? null);
}

Yii::info($model->resident_emails, 'test');
?>

    <div class="resident-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'patronymic')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'birth_date')->widget(DatePicker::className(), [
//                    'removeButton' => false,
                    'options' => [
//                    'value' => $model->birth_date ? Yii::$app->formatter->asDate($model->birth_date) : null,
                    'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'startView' => 'years',
                        'format' => 'dd.mm.yyyy',
                        'allowClear' => true,
                        'closeOnSelect' => true,
                    ]
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'snils')->textInput(['maxlength' => true]) ?>

            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'owner')->dropDownList(['1' => 'Да', '0' => 'Нет'], [
                    'onchange' => '
                        var field = $("#related-degree");
                        
                        if ($(this).val() == 0){
                            field.removeAttr("disabled");
                        } else {
                            field.val("");
                            field.attr("disabled", "true");
                        }
                        '
                ])->label('Собственник') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'related_degree')->textInput([
                    'id' => 'related-degree',
                    'maxlength' => true,
                    'disabled' => !$model->isNewRecord,
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo $form->field($model, 'resident_emails')->textInput()->hint('Несколько адресов вводятся через запятую') ?>
            </div>
            <div class="col-md-6">
                <?php echo $form->field($model,
                    'phone')->textInput(['value' => $phone])->hint('Несколько номеров вводятся через запятую') ?>
            </div>
        </div>

        <div id="input-address" class="row" style="<?= $display_input; ?>">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-10">
                        <?= $form->field($model, 'address')->textInput(['disabled' => true]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::button('Изменить адрес', [
                            'id' => 'change-address-btn',
                            'class' => 'btn btn-info btn-block'
                        ]) ?>
                    </div>
                </div>

            </div>
        </div>

        <div id="input-address-block" class="row" style="<?= $display_input_group; ?>">
            <div class="col-md-6">
                <?php
                echo $form->field($model, 'house')
                    ->widget(Select2::className(), [
                        'data' => House::getAddresses(),
                        'options' => [
                            'placeholder' => 'Выберите дом',
                            'onchange' => '
                                var house_id = $(this).val();
                                $.post(
                                    "/apartment/get-apartments",
                                    {
                                        house: house_id
                                    },
                                    function(response){
                                       $("#apartment-list").html(response);
                                    }
                                );
                            ',
                        ]
                    ])->label('Дом')
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo $form->field($model, 'apartment_id')
                    ->widget(Select2::className(), [
                        'data' => [],
                        'options' => [
                            'id' => 'apartment-list',
                            'placeholder' => 'Выберите квартиру',
                            'onchange' => '
                                var apartment_id = $(this).val();
                                $.post(
                                    "/apartment/get-rooms",
                                    {
                                        apartment: apartment_id
                                    },
                                    function(response){
                                       $("#room-list").html(response);
                                    }
                                );
                            ',
                        ]
                    ])->label('Квартира')
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo $form->field($model, 'room_id')
                    ->widget(Select2::className(), [
                        'data' => [],
                        'options' => [
                            'id' => 'room-list',
                            'placeholder' => 'Выберите комнату'
                        ]
                    ])->label('Комната')
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'additional_info')->textarea(['rows' => 6]) ?>
            </div>
        </div>

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать',
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
        <?php } ?>
        <?= $form->field($model, 'created_by_company')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'contact_id')->hiddenInput()->label(false) ?>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$script = <<<JS
    $(document).ready(function() {
        var input = $('#address-str');
        var field = $('.field-address-str');

        input.keydown(function(e){
            if (e.keyCode === 13){
                e.preventDefault();
                input.attr('disabled', 'true');
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
        
        $(document).on('click', '#change-address-btn', function() {
            $('#input-address').hide();
            $('#input-address-block').show();
        })
    })
JS;
$this->registerJs($script);