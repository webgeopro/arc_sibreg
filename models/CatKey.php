<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_keys".
 *
 * @property string $id
 * @property string $provider_id
 * @property string $tariff_id
 * @property string $encrypt_key_id
 * @property string $file
 *
 * @property CatProviders $provider
 * @property CatTariffs $tariff
 */
class CatKey extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cat_keys';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_id', 'tariff_id', 'encrypt_key_id'], 'integer'],
            [['file'], 'string', 'max' => 100],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatProviders::className(), 'targetAttribute' => ['provider_id' => 'code']],
            [['tariff_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatTariffs::className(), 'targetAttribute' => ['tariff_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'provider_id' => Yii::t('app', 'Provider ID'),
            'tariff_id' => Yii::t('app', 'Tariff ID'),
            'encrypt_key_id' => Yii::t('app', 'Code'),
            'file' => Yii::t('app', 'File'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(CatProviders::className(), ['code' => 'provider_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(CatTariffs::className(), ['id' => 'tariff_id']);
    }

    /**
     * Проверка провайдера
     * (результат раскодирования KeyMaster)
     * ??? Перенести в UserKey ???
     *
     * @param $providerId
     * @param $encryptKeyId
     * @return bool
     */
    public static function checkProvider($providerId, $encryptKeyId)
    {
        $catKey = self::findOne(['provider_id' => $providerId, 'encrypt_key_id' => $encryptKeyId]);
        if (null != $catKey) {
            //return $catKey->encrypt_key_id == $providerCode;
            return true;
        }

        return false;
    }
}
