<?php

use yii\helpers\Html;
use app\models\Settings;

/* @var $this \yii\web\View */
/* @var $content string */


if (Yii::$app->controller->action->id === 'login') {
    /**
     * Do not use this code in your template. Remove it.
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {
    dmstr\web\AdminLteAsset::register($this);
    app\assets\AppAsset::register($this);

    if(Yii::$app->user->isGuest == false){
        $companyId = Yii::$app->user->identity->company_id;
        $companyCallApiKey = Settings::getValueByKeyFromCompany(Settings::KEY_ATC_CODE, $companyId);
    } else {
        $companyCallApiKey = '';
    }


    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <meta name="company-call-api-key" content="<?=$companyCallApiKey?>">
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <?php
    $session = Yii::$app->session;
    if ($session['menu'] == 'large') $position = "sidebar-collapse";
    else $position = "";
    ?>
    <body class="hold-transition skin-blue sidebar-mini <?= $position ?>">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
