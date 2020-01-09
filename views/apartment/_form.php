<?php

use app\models\House;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Apartment */
/* @var $room_models app\models\Room */
/* @var $form yii\widgets\ActiveForm */

?>

    <div class="apartment-form">

        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'house_id')->widget(Select2::className(), [
                    'data' => House::getAddresses(),
                    'options' => [
                        'placeholder' => 'Выберите адрес дома',
                    ]
                ])->label('Адрес') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'cadastral_number')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?php
                $room_list = '';
                if (!$model->isNewRecord && isset($room_models)) {

                    foreach ($room_models as $room){
//                        Yii::info($room->number, 'test');
                        if ($room){
                            $model->room_list .= $room->number . ', ';
                        }
                    }
                    Yii::info($room_list, 'test');
                }
                ?>
                <?php
//                echo $form->field($model, 'room_list')
//                    ->textInput([
//                        'id' => 'input-number-room',
//                        'title' => "Несколько номеров разделяются запятыми",
//                    ])
//                    ->label('Комната(ы)')
                ?>
                <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>
            </div>
            <?php
            echo $form->field($model, 'room_list')
                ->hiddenInput([
                    'id' => 'input-number-room',
                ])
                ->label(false);
            ?>
            <div class="col-md-2">
                <?= $form->field($model, 'is_residential')->widget(SwitchInput::className(), [
                    'pluginOptions' => [
                        'onColor' => 'success',
                        'offColor' => 'warning',
                        'onText' => 'Да',
                        'offText' => 'Нет'
                    ]
                ])->label('Жилое') ?>
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
<?php
$this->registerJs(<<<JS
     $('#input-number-room').tooltip();
JS
);