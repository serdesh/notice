<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Инструкции';
$url_dir_instructions = Url::to(['/instructions'], true);
$path_instructions = Url::to('@webroot/instructions');
$files = '';
Yii::info($path_instructions, 'test');
if (is_dir($path_instructions)) {
    $files = array_diff(scandir($path_instructions), array('..', '.'));;
}
?>
<div class="site-index">

    <div class="body-content">
        <div class="box box-default">
            <div class="box-body">
                <?php if ($files): ?>
                    <?php foreach ($files as $key => $file): ?>
                        <div class="row">
                            <?= Html::a($key - 1 . '. ' . $file , $url_dir_instructions . '/' . $file); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="row">
                        Инструкции не найдены!
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
