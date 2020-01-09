<?php

use app\modules\drive\models\Auth;
use johnitvn\ajaxcrud\CrudAsset;
use yii\helpers\Html;

CrudAsset::register($this);
$this->title = 'Настройка доступа к Google Drive'
?>

<div class="container text-center">
    <div class="row">
        <div class="col-xs-12">
            <h2>Страница настройки и подключения Google Drive</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?php if ((new Auth)->checkAccessToken()): ?>
                <h3 class="text-success">Сервис подключен.</h3>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!(new Auth)->checkAccessToken()): ?>
    <div class="row">
        <div class="col-xs-12">
            <p><b> Для работы с GoogleDrive необходимы разрешения.</b></p>
            <p><b>Для выдачи разрешений нажмите кнопку ниже</b></p>
            <p><?= Html::a('Выдать разрешения', ['first-authenticate'], [
                    'class' => 'btn btn-success btn-large',
                    'role' => 'modal-remote',
                ]) ?></p>
        </div>
    </div>
    <?php endif; ?>

</div>
<div class="container">

    <div class="row">
        <div class="col-xs-12">
            <?php if (isset($files)){
                \yii\helpers\VarDumper::dump($files, 10, true);
            }?>
        </div>
    </div>
</div>
