<?php

namespace app\controllers;

use app\models\User;
use app\models\UserDevice;
use app\models\UserKey;
use app\models\UserPin;
use app\models\UserTrack;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class CabinetController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload' => ['post'],// Загрузка треков только методом POST
                    'limits' => ['put'], // Лимиты списываются только методом PUT
                    'pin' => ['put'],  // Пинкоды устанаваются только методом PUT
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        return $this->render('index', [
            'config' => \Yii::$app->user->identity->config,
        ]);
    }

    public function actionLimits($id)
    {
        die('Inside Limits');
    }

    /**
     * Создание / обновление пинкода
     *
     * @return bool
     */
    public function actionPin()
    {
        $id = \Yii::$app->request->post('id');

        UserPin::changePin($id);
        return $this->redirect(['/cabinet']);
    }

    /**
     * Загрузка треков и активаций
     *
     */
    public function actionUpload()
    {
        $user = User::findOne(\Yii::$app->user->id);
        $out = 'false';
        $file = UploadedFile::getInstanceByName('file');
        $ext = $file->getExtension();

        if ('gpx' == $ext) { // Загружен пользовательский трек
            $item = new UserTrack();
            $item->link('user', $user);
            $item->trackFile = $file->tempName;
            $item->name = $file->getBaseName();

            if ($item->save()) {
                $out = 'true';
            }

        } elseif('txt' == $ext) { // Загружен файл запроса
            try {// Проверка на keymaster
                $json = UserKey::checkRequestFromFile($file, true);

                if ($json) { // Проверка пройдена
                    /* Вставка в базу */
                    //todo Заблокировано на данный моментю
                    if (false && !empty($json->deviceId)) { // ID устройства из запроса обязательно
                        // Новое устройство / уже существующее + пользователь
                        /*$device = UserDevice::findOne([
                            'deviceid' => $json->deviceId,
                            'user_id' => Yii::$app->user->id,
                        ]) || new UserDevice;*/
                        $device = UserDevice::findOne([
                            'deviceid' => $json->deviceId,
                            'user_id' => Yii::$app->user->id,
                        ]);
                        if (null == $device) {
                            $device = new UserDevice;
                            $device->deviceid = $json->deviceId;
                            $device->user_id = Yii::$app->user->id;
                            $device->name = $json->deviceId;
                            $device->save();
                        };
                        /* Новый UserKey */
                        // Проверка лимитов + генерация ключа

                        // Списание 1 лимита + обновление записи
                    }

                    die('txt Key AFTER');
                }die(print_r($json));
            } catch(\Exception $e) {
                //todo logging
                return $e->getMessage(). '. Код ошибки: ' . $e->getCode();
            }
        }

        return $out;
    }
}
