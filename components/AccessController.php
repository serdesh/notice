<?php

namespace app\components;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Controller;

class AccessController extends Controller
{

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (!Yii::$app->user->can($action->id)) {
                throw new ForbiddenHttpException('У вас нет прав на это действие.');
            }
            return true;
        } else {
            return false;
        }
    }

}
