<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 * @property string $password Пароль
 * @property int company_id id компании, если входит суперадмин под компанией
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $company_id = 0; //Для входа суперадмина под любым пользователем
    public $inn;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'inn'], 'required', 'message' => 'Не заполнено обязательное поле'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password, $this->company_id ?? null)) {
                $this->addError($attribute, 'Неверный логин или пароль');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        Yii::info('Before validate', 'test');
        if ($this->company_id) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        Yii::error($this->errors, '_error');
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
//        if ($this->_user === false || User::isSuperAdmin()) {
//            $this->_user = User::findByUsername($this->username);
//        }
//
        if ($this->_user === false || User::isSuperAdmin()) {
            $this->_user = User::find()
                ->joinWith(['company'])
                ->andWhere(['company.inn' => $this->inn])
                ->andWhere(['OR', 'company.inn = "' . $this->username . '"', 'users.email = "' . $this->username . '"', 'login = "' . $this->username . '"'])
                ->one();
        }
        return $this->_user;
    }
}
