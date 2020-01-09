<?php
/**
 * Created by PhpStorm.
 * User: Indigo
 * Date: 23.10.2018
 * Time: 18:06
 */
namespace app\rbac;

use yii\rbac\Rule;

class UserGroupRule_back extends Rule
{
    public $name = 'userGroup';

    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->isGuest) {
            $group = \Yii::$app->user->identity->permission;
//            $group = \Yii::$app->user->identity->group;
            if ($item->name === 'administrator') {
                return $group == 'administrator';
            } elseif ($item->name === 'user') {
                return $group == 'administrator' || $group == 'user';
            } elseif ($item->name === 'manager') {
                return $group == 'administrator' || $group == 'manager';
            } elseif ($item->name === 'tech_specialist') {
                return $group == 'administrator' || $group == 'tech_specialist';
            }
        } else {
            //для не авторизованного пользователя.
            return 1;
        }
        return true;
    }
}