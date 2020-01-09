<?php

namespace app\models;

use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property IdentityInterface $identity
 */
class User extends Users implements IdentityInterface
{

    public $fake_login = 0;

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
//        return static::findOne(['login' => $username]);


        //Ищем по email
        $model = static::findOne(['email' => $username]) ?? null;
        //Ищем по логину
        if (!$model) {
            $model = static::findOne(['login' => $username]) ?? null;
        }
        //Ищем по ИНН
        if (!$model) {
            $model = static::findOne(['inn' => $username]) ?? null;
            if ($model){
                //Получаем пароль компании
                $model->password = Company::findOne(['inn' => $username])->password ?? null;
            }
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @param int $company_id ID компании, передается когда суперадмин входит под компанией
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password, $company_id = 0)
    {
        \Yii::info('Проверяемый пароль: ' . $password, 'test');
        if ($company_id) {
            \Yii::info('Вход суперадмина под компанией', 'test');
            return $this->password === $password;
        }
        return $this->password === md5($password);
    }
}
