<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_configs".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $request_file
 * @property string $provider_key
 * @property string $soft_expired
 * @property string $hard_expired
 * @property string $serial
 * @property string $cnt_limits
 *
 * @property User $user
 */
class UserConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_configs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'soft_expired', 'hard_expired', 'cnt_limits'], 'integer'],
            [['provider_key'], 'string', 'max' => 50],
            [['request_file','serial'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
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
            'request_file' => Yii::t('app', 'Request File'),
            'provider_key' => Yii::t('app', 'Provider Key'),
            'soft_expired' => Yii::t('app', 'Soft Expired'),
            'hard_expired' => Yii::t('app', 'Hard Expired'),
            'serial' => Yii::t('app', 'Serial'),
            'cnt_limits' => Yii::t('app', 'Limits'),
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
     * Хелпер, извлечение данных из ini-файла конфигурации
     *
     * @param $lines [name=value, ...]
     * @return array
     */
    public static function extractCSV($lines) {
        $sub = [
            'requestFile' => 'request_file', 'providerKey' => 'provider_key',
            'softExpire' => 'soft_expired', 'hardExpire' => 'hard_expired',
            'serialNumber' => 'serial',
        ];
        $config = [];
        foreach ($lines as $line) {
            $params = str_getcsv($line, '=');
            // Изменяем названия полей из файла на соответствующие полям таблицы БД
            if (key_exists($params[0], $sub)) {
                // Убираем лишние начальные "users/" request_file и provider_key
                $config[$sub[$params[0]]] = empty($params[1]) ? '' : preg_replace('@^users\/@', '', $params[1]); #str_replace('users/', '', $params[1]);
            }
        }
        return $config;
    }

    /**
     * Получить количество доступных активаций
     *
     * @return int|string
     */
    public function getLimit()
    {
        return empty($this->cnt_limits) ? 0 : $this->cnt_limits;
    }

}
