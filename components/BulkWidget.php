<?php
namespace app\components;

use johnitvn\ajaxcrud\BulkButtonWidget;

class BulkWidget extends BulkButtonWidget{

    public $buttons;

    public function init(){
        parent::init();

    }

    public function run(){
        $content = '<div class="pull-left">'.
            '<span class="glyphicon glyphicon-arrow-right"></span>&nbsp;&nbsp;С выбранным&nbsp;&nbsp;'.
            $this->buttons.
            '</div>';
        return $content;
    }
}
?>
