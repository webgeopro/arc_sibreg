<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\Cookie;
use yii\helpers\Url;

/**
 * This is the model class for table "guest".
 *
 * @property string $id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $expired_at
 * @property string $token
 *
 * @property GuestTracks[] $guestTracks
 */
class Guest extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_BLOCKED = 3;

    const COOKIE_NAME = 'sibreg_guest';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'guest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'created_at', 'updated_at', 'expired_at'], 'integer'],
            [['token'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'expired_at' => Yii::t('app', 'Expired At'),
            'token' => Yii::t('app', 'Token'),
        ];
    }

    /**
     * Установка куки после сохранения в БД
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {#die(print_r($this));
        self::setCookieToken($this->token);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Регистрация Гостя (установка уникальной куки, запись в БД)
     *
     * @return bool
     */
    public static function reg() {
        $guest = new self;
        $guest->token = Yii::$app->security->generateRandomString();
        $guest->status = self::STATUS_ACTIVE;
        $guest->expired_at = time() + Yii::$app->params['guestCookieExpired'];

        return $guest->save();
    }

    /**
     * Пользователь первый раз на сайте
     *
     * @return mixed
     */
    public static function isNewGuest()
    {
        return !self::getCookieToken();
    }

    /**
     * Найти пользователя в БД по его токену в куки.
     *
     * @param $cookie
     * @return null|static
     */
    public static function findByCookieToken($cookie='')
    {
        $cookie = (null == $cookie) ? self::getCookieToken() : $cookie;
        if (null == $cookie) {
            return null;
        }
        return static::findOne(['token' => $cookie, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Установка в куки уникальной строки для аутентификации гостя
     */
    public static function getCookieToken()
    {
        return Yii::$app->request->cookies->getValue(self::COOKIE_NAME);
    }

    /**
     * Установка в куки уникальной строки для аутентификации гостя
     *
     * @param string $token
     */
    public static function setCookieToken($token='')
    {
        $token = (null == $token) ? Yii::$app->security->generateRandomString(32) : $token;

        Yii::$app->response->cookies->add(new Cookie([
            'name' => self::COOKIE_NAME,
            'value' => $token,
            'expire' => time() + Yii::$app->params['guestCookieExpired'],
        ]));
    }

    /**
     * Удаление куки уникальной строки для аутентификации гостя
     */
    public static function removeCookieToken()
    {
        Yii::$app->response->cookies->remove(self::COOKIE_NAME);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuestTracks()
    {
        return $this->hasMany(GuestTracks::className(), ['guest_id' => 'id']);
    }

    /**
     * Получить все треки гостя и преобразовать их в набор файлов-треков на диске
     *
     * @return array
     */
    public function getAllTracks()
    {
        $allTracks = GuestTrack::find()
            ->select(['id', 'created_at'])
            ->where(['guest_id' => $this->id])
            ->andWhere(['>=', '`status`', UserTrack::STATUS_NEW])
            ->orderBy('created_at DESC, id DESC')
            ->all();
        $tracks = [];
        if (null != $allTracks) {
            foreach ($allTracks as $track) {
                $index =  $track->created_at;
                $tracks[] = $index . '##'
                    . Url::base(true) . '/uploads/guests'
                    . '/' . $track->trackDir .'/'. $track->trackFullName .'.gpx';
            }
        }
        return $tracks;
    }

    /**
     * Вернуть самый новый трек неавторизованного пользователя (гостя)
     *
     * @return array|null|ActiveRecord
     */
    public function getLastTrack()
    {
        $lastTrack = GuestTrack::find()
            ->where(['guest_id' => $this->id,])
            ->andWhere(['<=', 'created_at', new Expression('UNIX_TIMESTAMP()+600')]) // -10 минут от текущего
            ->andWhere(['>=', '`status`', UserTrack::STATUS_NEW])
            ->orderBy('created_at DESC, id DESC')
            ->limit(1)->one();

        if (null != $lastTrack) {
            return Url::base(true)
            . '/uploads/guests'
            . '/' . $lastTrack->trackDir
            . '/' . $lastTrack->trackFullName
            . '.gpx';
        }

        return null;
    }
}
