<?php

use app\models\Petition;
use app\models\Resident;
use app\models\Status;
use app\models\TroubleshootingPeriod;
use app\models\Users;
use johnitvn\ajaxcrud\CrudAsset;
use kartik\datetime\DateTimePicker;
use kartik\grid\GridView;
use kartik\select2\Select2;
use mihaildev\ckeditor\CKEditor;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Dropdown;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Petition */
/* @var $form yii\widgets\ActiveForm */
/* @var $msg_DataProvider \yii\data\ActiveDataProvider */

CrudAsset::register($this);

$request_resident_id = Yii::$app->request->get('resident_id') ?? null;
if ($request_resident_id) {
    $model->resident_id = $request_resident_id;
}

if (!Users::isSpecialist()) {
    $disabled_execution_date = false;
} else {
    $disabled_execution_date = true;
}
$session = Yii::$app->session;

if (!$model->isNewRecord) {
    //Пишем в сессию данные, для возврата в эту же форму после добавления жильца, квартиры или дома
    $session->set('request_page', 'update_petition');
    $session->set('request_id', $model->id);
} else {
    $session->set('request_page', 'create_petition');

}

Yii::info($model->toArray(), 'test');

?>
    <div class="petition-form">
        <div class="row">
            <?php if (isset($errors)): ?>
                <div class="col-md-12 error-msg">
                    <pre>
                    <?php \yii\helpers\VarDumper::dump($errors, 10, true) ?>
                    </pre>
                </div>
            <?php endif; ?>
        </div>
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-7">
                <?= $form->field($model, 'resident_id')->widget(Select2::className(), [
                    'data' => Resident::getList(),
                    'options' => [
                        'placeholder' => 'Выберите заявителя...',
                        'onchange' => '
                        var resident_id = $(this).val();
                        $.post(
                            "/resident/get-address",
                            {id: resident_id},
                            function(res){
                            console.log(res);
                                if (res){
                                    $("#address-str").val(res)
                                }
                            }
                        )
                    ',
                    ]
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'petition_type')->widget(Select2::className(), [
                    'data' => $model->getTypeList(),
                    'hideSearch' => true,
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= Html::a('Добавить... ', ['#'], [
                    'class' => 'dropdown-toggle btn btn-info btn-block',
                    'data-toggle' => 'dropdown',
                ]) ?>
                <?php
                try {
                    echo Dropdown::widget([
                        'items' => [
                            '<li>' . Html::a('Жильца', Url::to(['/resident/create']), [
                                'role' => 'modal-remote',
                            ]) . '</li>',
                            '<li>' . Html::a('Квартиру', Url::to(['/apartment/create']), [
                                'role' => 'modal-remote',
                            ]) . '</li>',
                            '<li>' . Html::a('Дом', Url::to(['/house/create']), [
                                'role' => 'modal-remote',
                            ]) . '</li>',
                        ],
                        'options' => [
                            'aria-labelledby' => 'dropdownMenuButton',
                        ]
                    ]);
                } catch (Exception $e) {
                    Yii::error($e->getTraceAsString(), '_error');
                }
                ?>
            </div>

        </div>

        <div class="row">
            <div class="col-md-10">
                <?= $form->field($model, 'address')
                    ->textInput(['id' => 'address-str'])
                    ->hint('Например: мск новокузнецкая 15. После ввода нажимте Enter или "Проверить"') ?>
            </div>
            <div class="col-md-2">
                <?= Html::button('Проверить', [
                    'id' => 'check-address-btn',
                    'class' => 'btn btn-info btn-block',
                    'disabled' => $model->isNewRecord,
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'header')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'status_id')->dropDownList(Status::getList())->label('Статус') ?>
            </div>
        </div>
        <?= $form->field($model, 'text')->widget(CKEditor::class, [
            'editorOptions' => [
                'preset' => 'basic',
                //разработанны стандартные настройки basic, standard, full данную возможность не обязательно использовать
                'inline' => false,
                //по умолчанию false
            ],
        ]);
        ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'trouble_id')->widget(Select2::className(), [
                    'data' => TroubleshootingPeriod::getList(),
                    'options' => [
                        'placeholder' => 'Выберите неисправность',
                        'onchange' => '
                            if ($(this).val()){
                                $("#trouble-input").attr("disabled", "true");
                            } else {
                                 $("#trouble-input").removeAttr("disabled");
                            }
                            ',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label('Неисправность') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'trouble_description')->textInput([
                    'id' => 'trouble-input',
                    'disabled' => $model->trouble_id,
                ]) ?>
            </div>
        </div>


        <?php if (!Users::isSpecialist()): ?>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'relation_petition_id')->widget(Select2::className(), [
                        'data' => Petition::getRootPetitionsList(),
                        'options' => [
                            'id' => 'relation-petition',
                            'placeholder' => 'Выберите связанное обращение...',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Связанное обращение') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'execution_date')->widget(DateTimePicker::classname(), [
                        'disabled' => $disabled_execution_date,
                        'options' => [
                            'id' => 'execution-date',
                            'placeholder' => 'Дата исполнения',
                            'value' => $model->execution_date ? Yii::$app->formatter->asDatetime($model->execution_date) : null,
                            'autocomplete' => 'off',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'dd.mm.yyyy H:i',
                        ]
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?php echo $form->field($model, 'specialist_id')->widget(Select2::className(), [
                        'data' => isset($model->company->id) ? Users::getListByPosition(Users::USER_ROLE_SPECIALIST,
                            $model->company->id) : Users::getListByPosition(Users::USER_ROLE_SPECIALIST),
                        'options' => [
                            'id' => 'spec-list-select',
                            'placeholder' => 'Выберите специалиста...'
                        ],
                    ])->label('Специалист') ?>
                </div>
            </div>
        <?php endif; ?>

        <?= $form->field($model, 'additional_info')->textarea(['rows' => 2])->label('Дополнительная информация') ?>

        <?php if (!$model->isNewRecord && !Users::isSpecialist()): ?>
            <?php

            echo $form->field($model, 'email_id')->dropDownList(Resident::getListEmails($model->resident_id), ['prompt' => 'Выберите email заявителя для ответа']);

            echo $form->field($model, 'answer')->textarea(['rows' => 6]);
            ?>
        <?php endif; ?>


        <?php

        if ($model->isNewRecord) {
            echo $form->field($model, 'where_type', ['template' => '{input}'])->hiddenInput([
                'value' => Petition::WHERE_TYPE_MANUAL_INPUT
            ]);
        }
        ?>
        <?= $form->field($model, 'manager_id', ['template' => '{input}'])->hiddenInput([
            'value' => Yii::$app->user->id,
        ]) ?>

        <?= $form->field($model, 'call_id', ['template' => '{input}'])->hiddenInput([
            'value' => $model->call_id,
        ]) ?>

        <?php //if (!$model->isNewRecord && !Yii::$app->request->isAjax): ?>
        <?php if (!Yii::$app->request->isAjax): ?>
            <div class="form-group text-right">
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => 'submit']) ?>
            </div>
        <?php endif; ?>

        <?php ActiveForm::end(); ?>

    </div>
<?php if (!$model->isNewRecord): ?>
    <div class="petition-msg">
        <?php
        try {
            echo GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $msg_DataProvider,
//            'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => require(__DIR__ . '/_msg_columns.php'),
                'toolbar' => [
                    [
                        'content' => '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список сообщений',
//                    'before' => $additional_filters,
                    'after' => '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            Yii::error($e->getTraceAsString(), '_error');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        ?>
    </div>
<?php endif; ?>
<?php
/** @lang JavaScript */
$script =
    <<<JS
$(document).ready(function(){
    var input = $('#address-str');
    var check_btn = $('#check-address-btn');
    var field = $('.field-address-str');
     
    input.keydown(function(e){
        if (e.keyCode === 13){
            e.preventDefault();
           check_btn.click();
        }
    });

    // $(document).on('click', '#to-work-btn', function(){
    //     var id = $(this).attr('data-id');
    //     var specialist_id = $('#spec-list-select').val();
    //     var relation_petition_id = $('#relation-petition').val();
    //     var execution_date = $('#execution-date').val();
    //     if (specialist_id !== ''){
    //         $.post(
    //             '/petition/to-work',
    //             {
    //                 id: id,
    //                 specialist_id: specialist_id,
    //                 relation_petition_id: relation_petition_id,
    //                 execution_date: execution_date
    //             },
    //             function (response) {
    //                 if (response !== 1){
    //                    alert(response);
    //                 }  
    //             }
    //         );
    //     } else {
    //         alert('Для постановки обращения в работу выберите специалиста.')
    //     }
    // });
    
    $(document).on('input keyup', input, function() {
        check_btn.removeAttr('disabled');
    });
    
    $(document).on('click', '#check-address-btn', function(){
        input.attr('disabled', 'true');
        $.get(
            '/petition/check-address-on-dadata',
            {
                'address': input.val()
            },
            function (data) {
                console.log(data[0]);
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
    })
})
    
JS;

$this->registerJs($script, \yii\web\View::POS_READY);
