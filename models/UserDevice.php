<?php

namespace app\models;

use Yii;


/**
 * This is the model class for table "user_devices".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $name
 * @property integer $status
 * @property string $deviceid
 * @property string $activationkey
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property UserPins[] $userPins
 */
class UserDevice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_devices';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'deviceid'], 'string', 'max' => 50],
            [['activationkey'], 'string', 'max' => 300],
            [['user_id', 'deviceid'], 'unique', 'targetAttribute' => ['user_id', 'deviceid'], 'message' => 'The combination of User ID and Deviceid has already been taken.'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'deviceid' => Yii::t('app', 'Device Id'),
            'activationkey' => Yii::t('app', 'Activation Key'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function behaviors()
    {
        return [\yii\behaviors\TimestampBehavior::className(), ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPins()
    {
        return $this->hasMany(UserPins::className(), ['device_id' => 'id']);
    }
}
