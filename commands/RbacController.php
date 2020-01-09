<?php
/**
 * Created by PhpStorm.
 * User: Indigo
 * Date: 23.10.2018
 * Time: 18:05
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use \app\rbac\UserGroupRule;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;

        // Create roles
        $guest  = $authManager->createRole('guest');
        $user  = $authManager->createRole('user');
        $manager = $authManager->createRole('manager');
        $tech_specialist = $authManager->createRole('tech_specialist');
        $administrator = $authManager->createRole('administrator');

        // Create simple, based on action{$NAME} permissions
        $login  = $authManager->createPermission('login');
        $authorization  = $authManager->createPermission('authorization');
        $logout = $authManager->createPermission('logout');
        $error  = $authManager->createPermission('error');
        $index  = $authManager->createPermission('index');
        $view   = $authManager->createPermission('view');
        $manager_view   = $authManager->createPermission('manager-view');
        $actions_view   = $authManager->createPermission('actions-view');
        $creator_view   = $authManager->createPermission('creator-view');
        $update = $authManager->createPermission('update');
        $delete = $authManager->createPermission('delete');
        $full_delete = $authManager->createPermission('full-delete');
        $additional_delete = $authManager->createPermission('additional-delete');
        $bulk_delete = $authManager->createPermission('bulk-delete');
        $create = $authManager->createPermission('create');
        $comment_create = $authManager->createPermission('comment-create');
        //request, order-part, comment
        $create_part = $authManager->createPermission('create-part');
        $nomenclature_list = $authManager->createPermission('nomenclature-list');
        $get_nomenclature = $authManager->createPermission('get-nomenclature');
        $change_order_status = $authManager->createPermission('change-order-status');
        $get_order_statuses = $authManager->createPermission('get-order-statuses');
        $get_main_order_status = $authManager->createPermission('get-main-order-status');
        $file_upload = $authManager->createPermission('file-upload');
        $file_remove = $authManager->createPermission('file-remove');
        $manager_nomenclature = $authManager->createPermission('manager-nomenclature');
        $tech_nomenclature = $authManager->createPermission('tech-nomenclature');
        $request_details = $authManager->createPermission('request-details');
        $get_measures = $authManager->createPermission('get-measures');
        $editable = $authManager->createPermission('editable');
        $update_detail = $authManager->createPermission('update-detail');
        $files_upload = $authManager->createPermission('files-upload');

        // Add permissions in Yii::$app->authManager
        $authManager->add($login);
        $authManager->add($authorization);
        $authManager->add($logout);
        $authManager->add($error);
        $authManager->add($index);
        $authManager->add($view);
        $authManager->add($update);
        $authManager->add($delete);
        $authManager->add($full_delete);
        $authManager->add($additional_delete);
        $authManager->add($bulk_delete);
        $authManager->add($create);
        $authManager->add($comment_create);

        $authManager->add($create_part);
        $authManager->add($nomenclature_list);
        $authManager->add($get_nomenclature);
        $authManager->add($change_order_status);
        $authManager->add($get_order_statuses);
        $authManager->add($get_main_order_status);
        $authManager->add($file_upload);
        $authManager->add($file_remove);
        $authManager->add($manager_view);
        $authManager->add($actions_view);
        $authManager->add($creator_view);
        $authManager->add($manager_nomenclature);
        $authManager->add($tech_nomenclature);
        $authManager->add($request_details);
        $authManager->add($get_measures);
        $authManager->add($editable);
        $authManager->add($update_detail);
        $authManager->add($files_upload);

        // Add rule, based on UserExt->group === $user->group
        $userGroupRule = new UserGroupRule();
        $authManager->add($userGroupRule);

        // Add rule "UserGroupRule" in roles
        $guest->ruleName  = $userGroupRule->name;
        $user->ruleName  = $userGroupRule->name;
        $manager->ruleName = $userGroupRule->name;
        $administrator->ruleName  = $userGroupRule->name;
        $tech_specialist->ruleName  = $userGroupRule->name;

        // Add roles in Yii::$app->authManager
        $authManager->add($guest);
        $authManager->add($user);
        $authManager->add($manager);
        $authManager->add($administrator);
        $authManager->add($tech_specialist);

        // Add permission-per-role in Yii::$app->authManager
        // Guest
        $authManager->addChild($guest, $login);
        $authManager->addChild($guest, $authorization);

        // user
        $authManager->addChild($user, $guest);
        $authManager->addChild($user, $login);
        $authManager->addChild($user, $authorization);
        $authManager->addChild($user, $logout);
        $authManager->addChild($user, $error);
        $authManager->addChild($user, $index);
        $authManager->addChild($user, $view);
        $authManager->addChild($user, $create);
        $authManager->addChild($user, $create_part);
        $authManager->addChild($user, $nomenclature_list);
        $authManager->addChild($user, $get_nomenclature);
        $authManager->addChild($user, $change_order_status);
        $authManager->addChild($user, $get_order_statuses);
        $authManager->addChild($user, $get_main_order_status);
        $authManager->addChild($user, $file_upload);
        $authManager->addChild($user, $file_remove);
        $authManager->addChild($user, $comment_create);
        $authManager->addChild($user, $manager_view);
        $authManager->addChild($user, $actions_view);
        $authManager->addChild($user, $creator_view);
        $authManager->addChild($user, $manager_nomenclature);
        $authManager->addChild($user, $tech_nomenclature);
        $authManager->addChild($user, $request_details);
        $authManager->addChild($user, $get_measures);
        $authManager->addChild($user, $editable);
        $authManager->addChild($user, $update_detail);
        $authManager->addChild($user, $files_upload);

        // manager
        $authManager->addChild($manager, $update);
        $authManager->addChild($manager, $guest);
        $authManager->addChild($manager, $login);
        $authManager->addChild($manager, $authorization);
        $authManager->addChild($manager, $logout);
        $authManager->addChild($manager, $error);
        $authManager->addChild($manager, $index);
        $authManager->addChild($manager, $view);
        $authManager->addChild($manager, $additional_delete);
        $authManager->addChild($manager, $create);
        $authManager->addChild($manager, $create_part);
        $authManager->addChild($manager, $nomenclature_list);
        $authManager->addChild($manager, $get_nomenclature);
        $authManager->addChild($manager, $change_order_status);
        $authManager->addChild($manager, $get_order_statuses);
        $authManager->addChild($manager, $get_main_order_status);
        $authManager->addChild($manager, $file_upload);
        $authManager->addChild($manager, $file_remove);
        $authManager->addChild($manager, $comment_create);
        $authManager->addChild($manager, $manager_view);
        $authManager->addChild($manager, $actions_view);
        $authManager->addChild($manager, $creator_view);
        $authManager->addChild($manager, $manager_nomenclature);
        $authManager->addChild($manager, $tech_nomenclature);
        $authManager->addChild($manager, $request_details);
        $authManager->addChild($manager, $get_measures);
        $authManager->addChild($manager, $editable);
        $authManager->addChild($manager, $update_detail);
        $authManager->addChild($manager, $files_upload);

        //tech_specialist
        $authManager->addChild($tech_specialist, $update);
        $authManager->addChild($tech_specialist, $guest);
        $authManager->addChild($tech_specialist, $login);
        $authManager->addChild($tech_specialist, $authorization);
        $authManager->addChild($tech_specialist, $logout);
        $authManager->addChild($tech_specialist, $error);
        $authManager->addChild($tech_specialist, $index);
        $authManager->addChild($tech_specialist, $view);
        $authManager->addChild($tech_specialist, $additional_delete);
        $authManager->addChild($tech_specialist, $create);
        $authManager->addChild($tech_specialist, $create_part);
        $authManager->addChild($tech_specialist, $nomenclature_list);
        $authManager->addChild($tech_specialist, $get_nomenclature);
        $authManager->addChild($tech_specialist, $change_order_status);
        $authManager->addChild($tech_specialist, $get_order_statuses);
        $authManager->addChild($tech_specialist, $get_main_order_status);
        $authManager->addChild($tech_specialist, $file_upload);
        $authManager->addChild($tech_specialist, $file_remove);
        $authManager->addChild($tech_specialist, $comment_create);
        $authManager->addChild($tech_specialist, $manager_view);
        $authManager->addChild($tech_specialist, $actions_view);
        $authManager->addChild($tech_specialist, $creator_view);
        $authManager->addChild($tech_specialist, $manager_nomenclature);
        $authManager->addChild($tech_specialist, $tech_nomenclature);
        $authManager->addChild($tech_specialist, $request_details);
        $authManager->addChild($tech_specialist, $get_measures);
        $authManager->addChild($tech_specialist, $editable);
        $authManager->addChild($tech_specialist, $update_detail);
        $authManager->addChild($tech_specialist, $files_upload);

        // admin
        $authManager->addChild($administrator, $guest);
        $authManager->addChild($administrator, $user);
        $authManager->addChild($administrator, $manager);
        $authManager->addChild($administrator, $tech_specialist);
        $authManager->addChild($administrator, $login);
        $authManager->addChild($administrator, $authorization);
        $authManager->addChild($administrator, $update);
        $authManager->addChild($administrator, $delete);
        $authManager->addChild($administrator, $full_delete);
        $authManager->addChild($administrator, $additional_delete);
        $authManager->addChild($administrator, $bulk_delete);
        $authManager->addChild($administrator, $create);
        $authManager->addChild($administrator, $create_part);
        $authManager->addChild($administrator, $nomenclature_list);
        $authManager->addChild($administrator, $get_nomenclature);
        $authManager->addChild($administrator, $change_order_status);
        $authManager->addChild($administrator, $get_order_statuses);
        $authManager->addChild($administrator, $get_main_order_status);
        $authManager->addChild($administrator, $file_upload);
        $authManager->addChild($administrator, $file_remove);
        $authManager->addChild($administrator, $comment_create);
        $authManager->addChild($administrator, $manager_view);
        $authManager->addChild($administrator, $actions_view);
        $authManager->addChild($administrator, $creator_view);
        $authManager->addChild($administrator, $manager_nomenclature);
        $authManager->addChild($administrator, $tech_nomenclature);
        $authManager->addChild($administrator, $request_details);
        $authManager->addChild($administrator, $get_measures);
        $authManager->addChild($administrator, $editable);
        $authManager->addChild($administrator, $update_detail);
        $authManager->addChild($administrator, $files_upload);
    }
}