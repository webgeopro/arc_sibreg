<?php

namespace app\controllers;

use app\models\Guest;
use budyaga\users\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
#use app\models\User;
#use app\models\RegForm;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) { /** Неавторизованный посетитель / Гость */
            if (Guest::isNewGuest()) {
                Guest::reg(); // Регистрация нового пользователя
                $this->refresh(); // Обновление страницы, чтобы изменения вошли в силу
            }

            if ( null == ($guest = Guest::findByCookieToken())) { // Куки установлены, но гость не найден в таблице
                //Guest::removeCookieToken(); // Удаляем куки
                //$this->refresh(); // Обновление страницы, чтобы изменения вошли в силу
            }
            $lastTrack = $guest->lastTrack;
            $tracks = $guest->allTracks;
#die(print_r($lastTrack));
        } else {  /** Зарегистрированный пользователь */
            // У пользователя есть новый трек (< 10 минут назад)
            $lastTrack = Yii::$app->user->identity->lastTrack;
            // Все треки пользователя
            $tracks = Yii::$app->user->identity->allTracks;
            //die(print_r(implode('@@', $tracks)));
        }
        return $this->render('index', [
            'lastTrack' => isset($lastTrack) ? $lastTrack : '',
            'tracks' => isset($tracks)
                #? json_encode($tracks, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES)
                ? implode('@@', $tracks)
                : '',
        ]);
    }

    public function actionTracks()
    {
        die('!!!');
    }

    public function actionMigrate() {
        $user = User::findByEmailOrUserName('kris');

        if (null != $user->lastTrack) { // У пользователя есть новый трек (< 10 минут назад)

            return $this->render('index', [
                'lastTrack' => Url::base(true) . '/uploads/tracks'
                    . '/' . $user->lastTrack->trackDir
                    . '/' . $user->lastTrack->trackFullName
                    . '.gpx',
            ]);
        }
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    /*public function reg()
    {
        $model = new RegForm();
        
    }*/


    // Остатки кода из файла по умолчанию
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

}
