<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "guest_tracks".
 *
 * @property string $id
 * @property string $guest_id
 * @property integer $status
 * @property string $created_at
 *
 * @property Guest $guest
 */
class GuestTrack extends \yii\db\ActiveRecord
{
    public $trackFullName; // Полное имя, ID дополненное нулями слева до 11 цифр
    public $trackDir;      // Директория, полученная из ID (к примеру: 34/12)
    public $trackFile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'guest_tracks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guest_id', 'status', 'created_at'], 'integer'],
            [['guest_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Guest::className(),
                'targetAttribute' => ['guest_id' => 'id']
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
            'guest_id' => Yii::t('app', 'Guest ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Формируем полное имя трека и путь к нему
     *
     * Дополняем ID для 11 знаков нулями слева,
     * берем последние 2 символа -> название директории,
     * берем 2 символа перед предыдущими -> название поддиректории.
     * Алгоритм обеспечивает более-менее нормальное распределение файлов по папкам.
     * @param $id
     * @return array
     */
    public static function getTrackFullAddress($id) {
        $trackFullName = str_pad($id, 11, '0', STR_PAD_LEFT);
        $trackDir = substr($trackFullName,-2, 2) .'/'. substr($trackFullName,-4, 2);

        return [$trackDir, $trackFullName];
    }

    public function afterFind()
    {
        list($this->trackDir, $this->trackFullName) = self::getTrackFullAddress($this->id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuest()
    {
        return $this->hasOne(Guest::className(), ['id' => 'guest_id']);
    }
}
