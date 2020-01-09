<?php
use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use yii\helpers\VarDumper;
?>
<div class="drive-yandex-index">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
</div>

<div class="row">
    <div class="col-md-12">
        <?php
        // передать OAuth-токен зарегистрированного приложения.
        $disk = new Disk('AgAAAAAKWXLNAAWzB9G5rLRtnkmPlkBwAPLBzzs');

        VarDumper::dump($disk->toArray(), 10, true);
        echo '<br>';

        //Получаем все файлы на диске
        /** @var Arhitector\Yandex\Disk\Resource\ $resource */
        foreach ($disk->getResources(10, 30) as $resource){
            echo $resource->name . ' - ' . $resource->path . '<br>';
        }
        echo '<br>';

        echo 'Общий объём: ' . $disk->total_space / 1024 /1024 . 'Мб<br>';
        echo 'Свободный объём: ' . (int)($disk->free_space / 1024 /1024) . 'Мб<br>';
        echo 'Приложения: ' . $disk->applications / 1024 /1024 . 'Мб<br>';


        /**
         * Получить Объектно Ориентированное представление закрытого ресурса.
         * @var  Closed $resource
         */
        $resource = $disk->getResource('All');
        foreach ($resource->items as $item)
        {
            if ($item->type == 'dir') {
                $type = 'Папка';
            } else {
                $type = 'Файл';
            }
            echo $type . ' ' . $item->name . '<br>';
            // $item объект ресурса `Resource\\*`, вложенный в папку.
        }
        // проверить сущестует такой файл на диске ?
//             $resource->has() . '<br>'; // вернет, например, false
        //        VarDumper::dump($resource->has(),10, true);


//        $resource = $disk->getResource('file1.png');
//        $path_file = Url::to('@webroot/images/file1.png');
//        echo 'Путь к файлу: ' . $path_file;
//        if (!$resource->has()){
//            $resource->upload($path_file);
//        }
//        echo 'Размер файла: ' . $resource->size / 1024 /1024 . 'Мб<br>';


        // файл загружен, вывести информацию.
//        VarDumper::dump($resource, 10, true);

        // теперь удалить в корзину.
//        $removed = $resource->delete();

//       echo 'Removed: ' . $removed;
        ?>
    </div>
</div>
