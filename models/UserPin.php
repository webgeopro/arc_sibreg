<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace app\models;

use Yii;
use budyaga\users\models\User;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "user_pins".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $device_id
 * @property string $name
 * @property string $pin
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UserDevices $device
 * @property User $user
 */
class UserPin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_pins';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'device_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['pin'], 'string', 'max' => 10],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDevice::className(), 'targetAttribute' => ['device_id' => 'id']],
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
            'device_id' => Yii::t('app', 'Device ID'),
            'name' => Yii::t('app', 'Name'),
            'pin' => Yii::t('app', 'Pin'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(UserDevice::className(), ['id' => 'device_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Преобразование даты создания пинкода в "человеческий" формат
     * @return bool|string
     */
    public function getCreated()
    {
        return date('d.m.Y', $this->created_at);
    }

    /**
     * Преобразование даты обновления пинкода в "человеческий" формат
     * @return bool|string
     */
    public function getUpdated()
    {
        return date('d.m.Y', $this->updated_at);
    }

    /**
     * Создать новый объект пинкод
     * use budyaga\users\models\User;
     * @return bool
     */
    public static function changePin($id=null)
    {
        $limit = Yii::$app->user->identity->config->limit; // Проверка лимитов

        if ($limit && $pincode = self::generatePin()) { // Генерация ПИНа при достаточном кол-ве активаций
            $config = UserConfig::find()->where(['user_id'=>Yii::$app->user->identity->id])->one();
            // Запись в БД
            $pin = (null == $id ) ? new self : self::findOne($id);
            $pin->user_id = Yii::$app->user->identity->id;
            $pin->pin = $pincode;

            if ($pin->save()) { // Проверка записи
                return $config->updateCounters(['cnt_limits' => -1]); // Списывание лимита
            }

            return false;
        }
    }

    /**
     * Создать новый ПИНКОД
     *
     * @param int $cnt [eosD-01am]
     * @param int $repeat
     * @return string
     */
    public static function generatePin($cnt=4, $repeat=2)
    {
        $base = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';

        for ($s = '', $i = 0; $i < $repeat; ++$i, $s.='-')
            for ($cntBase = strlen($base)-1, $j = 0;
                $j < $cnt;
                $s .= $base[mt_rand(0, $cntBase)], ++$j);

        return substr($s, 0, -1);
    }

}
