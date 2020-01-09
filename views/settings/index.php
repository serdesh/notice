<?php

use app\models\Settings;
use app\models\Users;
use app\modules\drive\models\Auth;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

//use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var array $all_settings Массив настроек для компании ['key' => 'value'] */

$this->title = 'Настройки';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

$drive = $all_settings['drive_type'] ?? '';
$atc_code = $all_settings['atc_code'] ?? '';
$atc_key = $all_settings['atc_key'] ?? '';

$yandex_checked = '';
$google_checked = '';
if ($drive) {
    if ($drive == 'yandex') {
        $yandex_checked = 'checked';
    } elseif ($drive == 'google') {
        $google_checked = 'checked';
    }
}

?>
    <div class="settings-index">

        <?php if (Users::isSuperAdmin()): ?>
            <div class="panel panel-default settings-panel">
                <div class="row">
                    <div class="col-md-4">
                        <h4>ID приложения Яндекс-диска</h4>
                    </div>
                    <div class="col-sm-6">
                        <?= Html::input('text', 'yandex_id', Settings::getValueByKey('yandex_disk_client_id'), [
                            'id' => 'input-id',
                            'class' => 'form-control',
                        ]) ?>

                    </div>
                    <div class="col-sm-2">
                        <?= Html::button('Сохранить', [
                            'id' => 'save-btn',
                            'class' => 'btn btn-success btn-block',
                            'style' => 'display: none',
                        ]) ?>
                    </div>
                </div>
            </div>

            <?php
            if (!is_file(Url::to('@app/tokens/google/credentials.json'))): ?>
                <div class="panel panel-default settings-panel">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4 class="text-warning">Внимание! Для подключения компаний к Google Drive необходим файл
                                "credentials.json"!</h4>
                        </div>
                        <form id="cred-form" method="POST" enctype="multipart/form-data"
                              action="<?= Url::to('/settings/credentials-upload'); ?>">
                            <div class="col-sm-3">
                                <?php
                                echo Html::fileInput('credentials', '', ['id' => 'file-input'])
                                ?>
                            </div>
                            <div class="col-sm-2">
                                <?= Html::submitButton('Сохранить', [
                                    'id' => 'save-cred-btn',
                                    'class' => 'btn btn-success',
                                ]) ?>
                            </div>
                            <input type="hidden" name="_csrf"
                                   value="<?= Yii::$app->request->getCsrfToken() ?>"/>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="row">
                <div class="col-md-12 text-left">
                    <h4>Код виджета отправки обращения</h4>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="row">
                    <div class="col-sm-12" style="margin: 15px;">
                        <code>
                            &lt;iframe src="<?= Url::to('@web/api/v1/widget?company=' . Users::getCompanyIdForUser(),
                                true); ?>" frameborder="0" width="100%" height="100%"&gt;&lt;/iframe&gt;
                        </code>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-left">
                    <h4>Код формы проверки статуса обращения</h4>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="row">
                    <div class="col-sm-12" style="margin: 15px;">
                        <code>
                            &lt;iframe src="<?= Url::to('@web/api/v1/check-form', true); ?>" frameborder="0"
                            width="100%" height="100%" scrolling="no"&gt;&lt;/iframe&gt;
                        </code>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="row text-center drive-block" style="margin: 30px;">
                    <div class="col-sm-6">
                        <div class="drive">
                            <input id="check-yandex" type="checkbox" <?= $yandex_checked ?>>
                            <h3>Яндекс диск</h3>
                        </div>
                        <div class="row">
                            <?php if (!Settings::getValueByKeyFromCompany('yandex_disk_token',
                                Users::getCompanyIdForUser())): ?>
                                <div class="col-sm-12">
                                    <?= Html::a('Открыть доступ к яндекс диску',
                                        Url::to(['/drive/yandex/yandex-disk', 'oauth' => 1]), [
                                            'class' => 'btn btn-success',
                                            'role' => 'modal-remote',
                                        ]) ?>
                                </div>
                            <?php else: ?>
                                <div class="col-sm-6">
                                    <h4 class="text-success"><?= Html::a('Сервис подключен', 'https://oauth.yandex.ru',
                                            ['target' => '_blank']) ?></h4>
                                </div>
                                <div class="col-sm-6">
                                    <?= Html::button('Отключить', [
                                        'class' => 'btn btn-danger btn-block',
                                        'id' => 'exit-drive',
                                        'data-drive' => 'yandex',
                                    ]) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="drive">
                            <input id="check-google" type="checkbox" <?= $google_checked ?>>
                            <h3>Google Drive</h3>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <?php if ((new Auth)->checkAccessToken()): ?>
                                <h4 class="text-success"><?= Html::a('Сервис подключен',
                                        'https://myaccount.google.com/permissions?utm_source=google-account&utm_medium=web',
                                        ['target' => '_blank']) ?></h4>

                            </div>
                            <div class="col-sm-6">
                                <?= Html::button('Отключить', [
                                    'class' => 'btn btn-danger btn-block',
                                    'id' => 'exit-drive',
                                    'data-drive' => 'google',
                                ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!(new Auth)->checkAccessToken()): ?>

                            <div class="row">
                                <div class="col-xs-12">
                                    <?php if (file_exists(Url::to(Auth::GOOGLE_TOKEN_DIR . 'credentials.json'))): ?>
                                        <p><?= Html::a('Открыть доступ к Google Drive',
                                                ['/drive/google/first-authenticate'], [
                                                    'class' => 'btn btn-success btn-large',
                                                    'role' => 'modal-remote',
                                                ]) ?></p>
                                    <?php else: ?>
                                        <p><b> Подключение к Google Drive невозможно. <br>Отсутствует информация о
                                                приложении GoogleDrive обратитесь к администратору CRM.</b></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!--Телефония-->
            <div class="row">
                <div class="col-md-12 text-left">
                    <h4>Телефония</h4>
                </div>
            </div>
            <div class="panel panel-default" style="padding: 15px;">
                <div class="row">
                    <div class="col-sm-4">

                        <?= Html::input('text', 'atc_code', $atc_code, [
                            'id' => 'atc-code',
                            'class' => 'form-control',
                            'placeholder' => 'Уникальный код вашей АТС'
                        ]) ?>
                    </div>
                    <div class="col-sm-4">
                        <?= Html::input('text', 'atc_key', $atc_key, [
                            'id' => 'atc-key',
                            'class' => 'form-control',
                            'placeholder' => 'Ключ для создания подписи'
                        ]) ?>
                    </div>
                    <div class="col-sm-2">
                        <?= Html::button('Сохранить', [
                            'id' => 'save-atc-btn',
                            'class' => 'btn btn-success btn-block'
                        ]) ?>
                    </div>
                    <div class="col-sm-2">
                        <?= Html::button('Сгенерировать', [
                            'class' => 'btn btn-info btn-block',
                            'onclick' => '
                                    var code = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                                    var key = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

                                    $("#atc-code").val(code);
                                    $("#atc-key").val(key);
                                '
                        ]) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>

<?php
$script = <<<JS
$(document).ready(function(){
    var check_yandex = $('#check-yandex');
    var check_google = $('#check-google');
    var yandex_checked = false;
    var google_checked = false;
    var alert_msg = $('.alert-success'); 
    
    $(document).on('click', '#check-yandex', function() {
        yandex_checked = check_yandex.prop('checked');
        google_checked = check_google.prop('checked');
        if (yandex_checked === true){
            google_checked = false;
            saveDrive('yandex');
        }
        check_google.prop('checked', google_checked);
        if (google_checked === false && yandex_checked === false){
                //Если все флаги сняты
                saveDrive('nothing');
        }
    });
    
    $(document).on('click', '#check-google', function() {
        yandex_checked = check_yandex.prop('checked');
        google_checked = check_google.prop('checked');
        if (google_checked === true){
            yandex_checked = false;
            saveDrive('google');
        } 
        check_yandex.prop('checked', yandex_checked);
        if (google_checked === false && yandex_checked === false){
                //Если все флаги сняты
                saveDrive('nothing');
        }
    });
    
    function saveDrive(type) {
        $.get(
            '/settings/save-drive',
            {
                drive: type
            },
            function(response) {
              console.log(response);
              setAlert('success', response, 3000)
            }
        )
    }
    
    $(document).on('input', '#input-id',function(){
      $('#save-btn').show();
    });
    $(document).on('click', '#save-btn', function() {
        var id = $('#input-id').val();
        $.get(
            '/drive/yandex/set-id',
            {
                id: id
            },
            function (response) {
                if (response[0] === 'success'){
                    alert('ID приложения Яндекс диска сохранен')
                } else {
                    alert('Ошибка сохранения ID приложения Яндекс диска')
                }
            }
        )
    });
    $(document).on('click', '#save-cred-btn', function() {
         var data = $('#cred-form').serializeArray();
         $.each(data,function(){
             console.log(this.name+'='+this.value);
        });
     });
    $(document).on('click', '#exit-drive', function() {
        var drive = $(this).attr('data-drive');
        $.get(
            'settings/reset-drive',
            {
                drive: drive
            },
            function(response) {
                if (response[0] === 'success'){
                    console.log(response[1]);
                    if (drive === 'yandex'){
                        $(location).attr('href',response[1]);
                    } else {
                        $(location).attr('href',response[1]);
                        window.open(response[2], "_blank");
                    }
                } else {
                    alert(response[1]);
                }
            }
        )
    });
    $(document).on('click', '#save-atc-btn', function() {
        var btn = $(this);
        btn.text('Сохранение');
        btn.attr('disabled', true);
        var code = $('#atc-code').val();
        var key = $('#atc-key').val();
        $.post(
            '/settings/save-atc',
            {
                code: code,
                key: key
            },
            function(response) {
                console.log(response);
                if (response[0] === 'fail'){
                    alert(response[1]);
                    btn.text('Сохранить');
                } else {
                    btn.text('Сохранено');
                }
                btn.removeAttr('disabled');
            }
        );
    });
     function setAlert(type, text, delay) {
         alert_msg.removeClass('alert-danger');
         alert_msg.removeClass('alert-success');
         if (type === 'success'){
             alert_msg.addClass('alert-success');
         } else {
             alert_msg.addClass('alert-danger');
         }
         alert_msg.text(text);
         alert_msg.slideToggle(400);
         alert_msg.delay(delay).slideToggle(400);
     }
    
})
JS;

$this->registerJs($script, \yii\web\View::POS_READY);