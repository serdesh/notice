<?php

use kartik\file\FileInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="import-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row" style="display: flex; align-items: center;">

        <div class="col-md-12">
            <?= $form->field($model, 'file')->widget(FileInput::classname(),[
                'options' => [
                    'multiple' => false,
                ],
            ])->label('Выберите файл для импорта'); ?>
        </div>
    </div>
<!--    <div class="row">-->
<!--        <div class="col-md-12">-->
<!--            <h4>Требования к файлу импорта:</h4>-->
<!--            <ul>-->
<!--                <li>Данные для импорта находятся на листе с названием "Параметры"</li>-->
<!--                <li>Наименования столбцов соответствуют наименованиям параметров в базе (Справочники->Параметры)</li>-->
<!--                <li>Типы столбцов (время, дата, число) соответствуют типам параметров в базе (Справочники->Параметры)</li>-->
<!--                <li>Не допустимо переименовывание следующих столбцов:-->
<!--                    <ul>-->
<!--                        <li>Предприятие</li>-->
<!--                        <li>Оборудование</li>-->
<!--                        <li>Пользователь</li>-->
<!--                        <li>Продукт</li>-->
<!--                    </ul>-->
<!--                </li>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton('Импортировать', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>
