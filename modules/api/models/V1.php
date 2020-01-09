<?php

namespace app\modules\api\models;

use yii\base\Model;

class V1 extends Model
{
    public $phone;
    public $address;
    public $company_id;
    public $files;
    public $agreement;
    public $verifyCode;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone', 'address'], 'string'],
            [['company_id', 'agreement'], 'integer'],
            [
                ['files'],
                'file',
                'extensions' => ['jpg', 'png', 'jpeg', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'odt'],
                'maxFiles' => 5,
            ],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha'],
            [['agreement'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон',
            'address' => 'Адрес',
            'verifyCode' => 'Код верификации',
            'agreement' => 'Согласие на обработку персональных данных',
        ];
    }
}