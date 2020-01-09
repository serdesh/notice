<?php

use app\models\Functions;
use kato\DropZone;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\House */
/* @var $doc_model app\models\Document */
/* @var $form yii\widgets\ActiveForm */


if ($model->isNewRecord || !$model->document_id) {
    $display_style_drop_zone = 'display: block;';
    $display_style = 'display: none;';
} else {
    $display_style_drop_zone = 'display: none;';
    $display_style = 'display: block;';
}
?>

    <div class="house-form">

        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'address')
                    ->textInput(['id' => 'address-str'])
                    ->hint('Например: мск новокузнецкая 15. После ввода, для подстановки адреса нажимте "Enter"') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'fias_number')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'cadastral_number')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'residential_number')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'non_residential_number')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5>Документ</h5>
            </div>
            <div class="panel-body">
                <div id="drop-zone" style="<?= $display_style_drop_zone ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($doc_model, 'name')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php try {
                                echo DropZone::widget([
                                    'options' => [
                                        'url' => Url::to(['/document/upload']),
//                                'paramName' => 'document',
//                                'renameFile' => time(),
                                        'maxFilesize' => '5',
                                        'addRemoveLinks' => true,
                                        'dictRemoveFile' => 'Удалить',
                                        'dictDefaultMessage' => "Кликните для выбора документа или перетащите файл в это поле",
                                        'uploadMultiple' => false,
                                        'maxFiles' => 1,
                                        'dictMaxFilesExceeded' => 'Вы можете загрузить только один файл.',
                                        'dictFileSizeUnits' => 'Превышен допустимый размер файла (5 мегабайт)'
                                    ],
                                    'clientEvents' => [
                                        'complete' => "function(file, index){
                                             var file = {
                                                 name: file.name,
                                                 size: file.size,
                                                 type: file.type,
                                                 path: ($('#path').val()+file.name),
                                             }; 
                                        }",
                                        'removedfile' => "function(file){
                                            $.get('/document/remove-file')
                                        }"
                                    ],
                                ]);
                            } catch (Exception $e) {
                                Yii::error($e->getTraceAsString(), '_error');
                                Yii::$app->session->setFlash('error', $e->getMessage());
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div id="drop-file" class="row" style="<?= $display_style ?>">
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Файл: <?= Html::a($doc_model->name,
                                    Url::to([
                                            'download',
                                        'file' => $doc_model->local_path,
                                        'name' => $doc_model->name . Functions::getExtension($doc_model->local_path),
                                    ])) ?></span>
                            <?= Html::button('Удалить', [
                                'id' => 'del-doc-btn',
                                'class' => 'btn btn-danger btn-xs',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <?= $form->field($model, 'additional_info')->textarea(['rows' => 6]) ?>

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update',
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
        <?php } ?>

        <?php ActiveForm::end(); ?>

    </div>
<?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>

<?php
if ($model->document_id) {
    $script = <<<JS
    $(document).ready(function() {
        $(document).on('click', '#del-doc-btn', function(){
            $.get('/document/delete', {id: $model->document_id});
            $('#drop-file').hide();
            $('#drop-zone').show();
        });
    })
JS;
    $this->registerJs($script);
}
?>

<?php
$script2 = <<<JS
    $(document).ready(function() {
        var input = $('#address-str');
        var field = $('.field-address-str');

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
$this->registerJs($script2);
