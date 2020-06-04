<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_tracks".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $name
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property User $user
 */
class UserTrack extends \yii\db\ActiveRecord
{
    public $trackFullName; // Полное имя, ID дополненное нулями слева до 10 цифр
    public $trackDir;      // Директория, полученная из ID (к примеру: 34/12)
    public $trackFile;

    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_BLOCKED = 0;
    const STATUS_PUBLISHED = 3;

    public $statuses = [
        self::STATUS_BLOCKED => 'Заблокирован',
        self::STATUS_NEW => 'Новый',
        self::STATUS_ACTIVE => 'Активный',
        self::STATUS_PUBLISHED => 'Опубликованный',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_tracks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'required'],
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 80],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
            ['trackFile', 'file', //'mimeTypes'=>'xml', // Нужно расширение fileinfo
                'extensions' => 'gpx, txt',
                'maxSize' => 2097152, //2048*1024. 2 Mb
                'checkExtensionByMimeType' => false,
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
            'name' => Yii::t('app', 'Track Name'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function behaviors()
    {
        return [
            /** Поведение автоматически заполняет тек. временем поля created_at и updated_at */
            TimestampBehavior::className(),
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

    public function afterDelete()
    {
        unlink( // Удаляем файл с треком
            \Yii::$app->params['usersDirTracks']
            . DIRECTORY_SEPARATOR
            . $this->trackDir
            . DIRECTORY_SEPARATOR
            . $this->trackFullName
        );
        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (null != $this->trackFile) {
            list($this->trackDir, $this->trackFullName) = self::getTrackFullAddress($this->id);

            if (!$this->trackFileCopy($this->trackFile)) { //todo throw Error
                echo 'Doesnt Copy File: '. $this->trackFile ."\n";
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }

    public function getCreated()
    {
        return date('Y.m.d', $this->created_at);
    }

    public function getUpdated()
    {
        return date('Y.m.d', $this->updated_at);
    }

    public function getStatusName()
    {
        return $this->statuses[$this->status];
    }

    protected function trackFileCopy($trackFile) {
        #echo $this->trackDir, '::', $this->trackFullName, " \n";
        #die("\$trackFile=$trackFile");#die(file_get_contents($trackFile));
        if (file_exists($trackFile)) {
            $destDir = \Yii::$app->params['usersDirTracks'] .'/'. $this->trackDir;
            $this->createDir($destDir);
            if( copy($trackFile, $destDir .'/'.  $this->trackFullName .'.gpx') ) {
                return true;
            } else {
                //todo throw Error
                echo 'Doesnt Copy File To: '. $destDir .'/'.  $this->trackFullName .".gpx \n";
                return false;
            }
        } else //todo throw Error
            echo 'File Doesnt Exists: '. $trackFile ."\n";
    }

    protected function createDir($dir)
    {
        if(!is_dir($dir)) {
            $this->createDir(dirname($dir));
            mkdir($dir); //todo throw Error
        }
    }
}
