<?php
/**
 * User: Webgeopro
 * Date: 10.06.2016
 */

namespace app\models;

use yii\base\Model;

/**
 * RegForm is the model behind the registration form.
 *
 * Class RegForm
 * @package app\models
 */
class RegForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'filter', 'filter' => 'trim'],
            [['username', 'email', 'password'], 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique',
                'targetClass' => User::className(),
                'message' => Yii::t('reg', 'This username has already been taken.'),
            ],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique',
                'targetClass' => User::className(),
                'message' => Yii::t('reg', 'This email address has already been taken.'),
            ],

            ['password', 'string', 'min' => 5, 'max' => 50],

            ['status', 'default', 'value' => User::STATUS_ACTIVE, 'on' => 'default'],
            ['status', 'in', 'range' =>
                User::STATUS_NOT_ACTIVE,
                User::STATUS_ACTIVE
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Signs User Up
     *
     * @return User|null
     */
    public function reg()
    {
        if ( !$this->validate() ) {
            return null;
        }
        $user = new User();

        $user->username = $this->username;
        $user->email = $this->email;
        $user->status = $this->status;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}