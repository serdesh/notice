<?php use app\models\Company;
use app\models\Status;
use app\models\Users;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\helpers\Html;

/* @var $model \app\models\Petition */

$request = Yii::$app->request;

$specialist_id = $request->post('specialist') ?? 0;

//Определяем отображение фильтра
$status_filter = $request->post('filter-status');
if ($status_filter == 'open'){
    $display = 'block';
    $btn_text = 'Свернуть';
} else {
    $display = 'none';
    $btn_text = 'Развернуть';
}

$form = ActiveForm::begin(); ?>
    <div class="panel">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-6">
                    <h4>Фильтры</h4>
                </div>
                <div class="col-md-2 col-md-offset-4 text-right">
                    <?= Html::button($btn_text, [
                        'id' => 'filters-btn',
                        'class' => 'btn btn-info btn-block',
                        'status' => 'closed'
                    ]) ?>
                </div>
            </div>
        </div>
        <div id="body-filter" class="panel-body" style="display: <?= $display; ?>;">
            <div class="row">
                <div class="col-md-4">
                    <?php
                    try {
                        echo Select2::widget([
                            'name' => 'company',
                            'data' => Company::getList(),
                            'value' => $request->post('company'),
                            'options' => [
                                'placeholder' => 'Выберите комапнию...',
                                'onchange' => '
                                var spec = $("#specialist-list");
                                spec.attr("disabled", "true");
                                $.post(
                                    "/users/get-spec-by-company",
                                    {
                                        company: $(this).val()
                                    },
                                    function(res){
                                        spec.removeAttr("disabled");
                                        spec.html(res);
                                    }
                                )                                
                                if ($(this).val() != ""){
                                    spec.removeAttr("disabled");
                                } 
                            ',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);
                    } catch (Exception $e) {
                        Yii::error($e->getTraceAsString(), '_error');
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                    ?>
                </div>
                <div class="col-md-4">
                    <?php
                    try {
                        echo Select2::widget([
                            'name' => 'specialist',
                            'data' => Users::getListByPosition(Users::USER_ROLE_SPECIALIST ,$request->post('company')),
                            'value' => $request->post('specialist'),
                            'options' => [
                                'id' => 'specialist-list',
                                'placeholder' => 'Выберите исполнителя...',
                                'disabled' => $specialist_id > 0 ? false : true,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);
                    } catch (Exception $e) {
                        Yii::error($e->getTraceAsString(), '_error');
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                    ?>
                </div>
                <div class="col-md-4">
                    <?php
                    try {
                        echo Select2::widget([
                            'name' => 'status',
                            'attribute' => 'status',
                            'data' => Status::getList(true),
                            'value' => $request->post('status'),
                            'options' => [
                                'id' => 'status-list',
                                'placeholder' => 'Выберите статус...',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);
                    } catch (Exception $e) {
                        Yii::error($e->getTraceAsString(), '_error');
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <?php
                    try {
                        echo SwitchInput::widget([
                            'name' => 'search_in_archive',
                            'value' => $request->post('search_in_archive'),
                            'pluginOptions' => [
//                            'size' => 'large',
                                'onColor' => 'success',
                                'offColor' => 'warning',
                                'onText' => 'Искать в архивированных',
                                'offText' => 'Не искать в архиве'
                            ]
                        ]);
                    } catch (Exception $e) {
                        Yii::error($e->getTraceAsString(), '_error');
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                    ?>
                </div>
                <div class="col-md-5">
                    <?php
                    try {
                        echo SwitchInput::widget([
                            'name' => 'search_in_expired',
                            'value' => $request->post('search_in_expired'),
                            'pluginOptions' => [
//                            'size' => 'large',
                                'onColor' => 'success',
                                'offColor' => 'warning',
                                'onText' => 'Искать в просроченных',
                                'offText' => 'Не искать в просроченных'
                            ]
                        ]);
                    } catch (Exception $e) {
                        Yii::error($e->getTraceAsString(), '_error');
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }
                    ?>
                </div>
                <div class="col-md-2">
                    <?php
                    echo Html::submitButton('Применить фильтр', [
                        'class' => 'btn btn-info btn-block',
                    ])
                    ?>
                </div>
            </div>
        </div>
    </div>

<?= Html::hiddenInput('filter-status', $status_filter, ['id' => 'status-input']) ?>

<?php ActiveForm::end(); ?>

<?php
$script = <<<JS
    $(document).ready(function() {
        var filters_btn = $('#filters-btn');
        var status_input = $('#status-input');
        $(document).on('click', '#filters-btn', function(){
            var status = filters_btn.attr('status');
            if (status === 'closed'){
                $('#body-filter').slideDown('fast', function(){
                    filters_btn.attr('status', 'open');
                    filters_btn.text('Свернуть');
                    status_input.val('open');
                });
            } else {
                $('#body-filter').slideUp('fast', function(){
                    filters_btn.attr('status', 'closed');
                    filters_btn.text('Развернуть');
                    status_input.val('closed');
                });
            }
        })
    })
JS;

$this->registerJs($script);
