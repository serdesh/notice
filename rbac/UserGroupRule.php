<?php
/**
 * Created by PhpStorm.
 * User: Indigo
 * Date: 23.10.2018
 * Time: 18:06
 */

namespace app\rbac;

use Yii;
use yii\rbac\Rule;

class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return bool|int
     */
    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->isGuest) {
//            $group = Yii::$app->user->identity->permission;
//            if ($item->name === 'super_administrator') {
//                return $group == 'super_administrator';
//            }
//            elseif ($item->name === 'super_manager') {
//                return $group == 'super_administrator' || $group == 'super_manager';
//        } elseif ($item->name === 'administrator') {
//            return $group == 'super_administrator' || $group == 'administrator';
//        }
//              elseif ($item->name === 'manager') {
//                return $group == 'administrator' || $group == 'manager';
//            } elseif ($item->name === 'specialist') {
//                return $group == 'super_administrator' || $group == 'specialist';
//            }
    } else
{
    //для не авторизованного пользователя.
return 1;
}

return true;
}
}