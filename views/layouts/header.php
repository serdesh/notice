<?php

use app\models\Call;
use app\models\Petition;
use app\models\Users;
use yii\helpers\Html;
use yii\bootstrap\Modal;

$display_warning_call = 'display: none;';
$display_warning_email = 'display: none;';

if (!Users::isSpecialist()) {
    $call_model = Call::find()
            ->andWhere(['company_id' => Users::getCompanyIdForUser()])
            ->andWhere(['petition_id' => null])
            ->all() ?? null;

    if ($call_model) {
        $display_warning_call = 'display: block;';
    } else {
        $display_warning_call = 'display: none;';
    }

    $exist_new_petition = Petition::availableNewPetition();

    if ($exist_new_petition) {
        $display_warning_email = 'display: block;';
    } else {
        $display_warning_email = 'display: none;';
    }
}


?>

    <header class="main-header">

        <?= Html::a('<span class="logo-mini">A</span><span class="logo-lg">' . Yii::$app->name . '</span>',
            Yii::$app->homeUrl,
            ['class' => 'logo']) ?>

        <nav class="navbar navbar-static-top" role="navigation">

            <a href="#" onclick="$.post('/site/menu-position');" class="sidebar-toggle" data-toggle="push-menu"
               role="button"><span class=""></span> </a>
            <div class="part-navbar">
                <div class="warnings-btn">
                    <div class="navbar-warning">
                        <?php
                        echo Html::a('<span class="glyphicon glyphicon-earphone"></span>&nbsp;Имеются необработанные звонки',
                            ['/call'], [
                                'id' => 'warning-call-btn',
                                'class' => 'btn btn-warning',
                                'style' => $display_warning_call,
                            ]) ?>
                    </div>
                    <div class="navbar-warning">
                        <?php
                        echo Html::a('<span class="glyphicon glyphicon-envelope"></span>&nbsp;Имеются необработанные письма ',
                            ['/petition'], [
                                'id' => 'warning-email-btn',
                                'class' => 'btn btn-warning',
                                'style' => $display_warning_email,
                            ]) ?>
                    </div>
                </div>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="hidden-xs">
                            <?php
                            if (!empty(Yii::$app->user->identity->fio)) {
                                echo Yii::$app->user->identity->fio;
                            }
                            ?>
                        </span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <p> <?php
                                        if (!empty(Yii::$app->user->identity->fio)) {
                                            echo Yii::$app->user->identity->fio;
                                        }
                                        ?> </p>
                                </li>
                                <?php
                                if (!empty(Yii::$app->user->identity->id)) { ?>
                                    <li class="user-footer">
                                        <?php if (Users::isSuperAdmin() || Users::isAdmin()): ?>
                                            <div class="pull-left">
                                                <?= Html::a('Профиль',
                                                    ['users/view', 'id' => Yii::$app->user->identity->id],
                                                    [
                                                        'role' => 'modal-remote',
                                                        'title' => 'Профиль пользователя',
                                                        'class' => 'btn btn-default btn-flat'
                                                    ]); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="pull-right">
                                            <?= Html::a(
                                                'Выход',
                                                ['/site/logout'],
                                                ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                            ) ?>
                                        </div>
                                    </li>
                                <?php }
                                ?>
                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </header>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "size" => "modal-lg",
    "options" => [
        "tabindex" => false,
    ],
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>