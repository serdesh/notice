<?php

use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

CrudAsset::register($this);

/* @var string $code Код доступа */

?>

    <h3>Yandex Диск</h3>
    <div class="row">
            <div id="fail" class="col-md-4">
                <?= Html::a('Открыть доступ к яндекс диску', Url::to(['yandex-disk', 'oauth' => 1]), [
                    'class' => 'btn btn-info',
                    'role' => 'modal-remote',
                ]) ?>
                <p class="text-danger" id="fail-text">Ошибка получения токена</p>
            </div>
            <div id="success" class="col-md-4" style="display: none;">
                <p class="text-success">Операция прошла успешно</p>
            </div>
    </div>

<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>

<?php
$this->registerJS(<<<JS
    $(document).ready(function() {
        var token = /access_token=([^&]+)/.exec(document.location.hash)[1];
        var success_block = $('#success');
        var fail_block =  $('#fail');
        var fail_text = $('#fail-text');
        if (token){
            //Сохраняем токен
            $.post(
                'save-token',
                {
                    token: token
                },
                function(response) {
                  if (response === 'success'){
                      success_block.show();
                      fail_block.hide();
                  } else {
                      success_block.hide();
                     fail_block.show();
                  }
                }
            );
        } else {
           fail_text.html('Нет токена');
           fail_block.show();
        }
    })
JS
);

