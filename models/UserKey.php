<?php

namespace app\models;

#use budyaga\users\models\User;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_keys".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $device_id
 * @property string $sign
 * @property string $activation_key
 * @property string $request_txt
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $expired_at
 *
 * @property User $user
 */
class UserKey extends \yii\db\ActiveRecord
{
    const STATUS_BLOCKED = 0;
    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_keys';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'device_id', 'status', 'created_at', 'updated_at', 'expired_at'], 'integer'],
            [['sign'], 'string', 'max' => 100],
            [['activation_key'], 'string', 'max' => 300],
            [['request_txt'], 'string', 'max' => 255],
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
            'sign' => Yii::t('app', 'Sign'),
            'activation_key' => Yii::t('app', 'Activation Key'),
            'request_txt' => Yii::t('app', 'Request Txt'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'expired_at' => Yii::t('app', 'Expired At'),
        ];
    }

    public function behaviors()
    {
        return [
            [ /** created_at и updated_at */
                TimestampBehavior::className(),
            ],
            [ /** Свое поведение для expired_at */
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['expired_at'],
                ],
                'value' => 'NOW()+INTERVAL 365 DAY'
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Обновление ключа
     */
    public function updateExpired()
    {
        die('Inside UserKey::updateExpired()');
    }

    /**
     * Проверка валидности запроса для генерации ключа
     *
     * @param $file
     * @return bool
     * @throws Exception
     */
    public static function checkRequestFromFile($file, $returnData = false)
    {
        $command = Yii::$app->params['keymasterFile'] . ' -dr'
            . ' -pk='  . Yii::$app->params['keymasterDir'] . '\sibreg.keys'
            . ' -req=' . $file->tempName;

        try {
            exec($command, $out, $return);
        } catch(Exception $e) {
            throw new Exception('PHP-ошибка запуска программы проверки запроса.');
        }
        if (0 < $return) {
            throw new Exception('Внутренний сбой запуска программы проверки запроса.' . $return);

        } elseif (0 == $return) {
            // Вывод exec -> array, вывод KeyMaster -> json
            $out = implode('', $out); // Объединаем строки вывода exec в одну
            $result = @json_decode($out); // Декодируем json-результат работы KeyMaster
            if (!empty($result->providerId) && !empty($result->encryptKeyId)) {
                // Проверяем код провайдера
                $checkProvider = CatKey::checkProvider($result->providerId, $result->encryptKeyId);
                if ($returnData && $checkProvider) { // Вернуть данные
                    return $result;
                }

                return $checkProvider;
            }
        }

        return false;
    }

    /**
     * Сгенерировать новый пользовательский ключ
     *
     * @param UserDevice $device
     * @param User|\budyaga\users\models\User $user
     * @return bool
     */
    public static function changeUserKey(UserDevice $device, \budyaga\users\models\User $user)
    {
        $limit = Yii::$app->user->identity->config->limit; // Проверка лимитов

        if ($limit && $userKey = self::generateKey()) { // Генерация ПИНа при достаточном кол-ве активаций
            $config = UserConfig::find()->where(['user_id'=>$user->id])->one();
            // Запись в БД
            #$pin = (null == $id ) ? new self : self::findOne($id);
            #$pin->user_id = Yii::$app->user->identity->id;
            #$pin->pin = $pincode;

            /*if ($pin->save()) { // Проверка записи
                return $config->updateCounters(['cnt_limits' => -1]); // Списывание лимита
            }*/

            return false;
        }
    }
}
