<?php
/**
 * @author Vah <darvah@yandex.ru>
 * @since: 13.06.2016
 * @link yii users [command]
 * @copyright Copyright (c) 2016 Sibreg LLC
 */

namespace app\commands;

use app\models\UserConfig;
use app\models\UserTrack;
use budyaga\users\models\User;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Json;

/**
 * Консольное приложение для переноса пользователей из старого сайта.
 *
 * Class UsersController
 * @package app\commands
 */
class UsersController extends Controller
{
    /**
     * @var string $dir Директория с пользователями
     */
    protected $dir;
    /**
     * @var string $dirTracks Директория с треками пользователей
     */
    protected $dirTracks;
    /**
     * @var string $usersJSON Файл с пользователями
     */
    protected $usersJSON;
    /**
     * @var string $usersAuth Файл с логинами и паролями пользователей
     */
    protected $usersAuth;
    /**
     * @var int USER_PROCESSED @const Данные пользователя обработаны. Defaults to 1
     * @var int USER_NOT_PROCESSED @const Данные пользователя пока не обработаны. Defaults to 0
     */
    const USER_PROCESSED = 1;
    const USER_NOT_PROCESSED = 0;
    /**
     * @var array $errors Ошибки при переносе пользователя
     */
    protected $errors = [];

    public function init()
    {
        $this->dir = \Yii::$app->params['usersDir'];
        $this->dirTracks = \Yii::$app->params['usersDirTracks'];
        $this->usersJSON = $this->dir .DIRECTORY_SEPARATOR. 'users.json';
        $this->usersAuth = $this->dir .DIRECTORY_SEPARATOR. 'userkeys.pwd';

        parent::init();
    }

    /** Default action */
    public function actionIndex()
    {
        $mess = "
            DEFAULT ACTIONS:\n
            1. migrate: Migrating all users\n
            2. migrateUser username: Migrating the one user 'username'
        ";
        $this->stdout($mess, Console::BG_GREEN, Console::FG_CYAN, Console::BOLD);
    }

    /**
     * Получить список пользователей
     * При флаге $refresh=true или отстутствии JSON-файла
     * принудительно проходит директорию с пользователями и создает его.
     * Список пользователей возвращается на основе этого файла.
     *
     * @param bool $refresh Флаг обновления
     * @return array|mixed Список пользователей [[username=>[process, tracks, keys, devices]], ...]
     */
    protected function getListUsers($refresh = true) {
        // Файла не существует или
        // принудительно надо обновить JSON-файл с именами пользователей
        if ($refresh || !file_exists($this->usersJSON)) {
            // Создаем список пользователей
            $users = [];
            // Данные авторизации по всем пользователям
            $auths = $this->getUsersAuths();

            foreach (new \DirectoryIterator($this->dir) as $username) {
                if($username->isDot() || $username->isFile()) continue; // Только папки с пользователями

                $name = $username->getFilename(); // Имя пользователя === название директории === login
                if (!key_exists($name, $auths)) {
                    $this->errors[$name][] = 'User Auth error: not present in auths. Block getListUsers';
                    continue;
                }
                $users[$name] = [ // [username=>[process, tracks, keys, devices]]
                    'process' => self::USER_NOT_PROCESSED, // Данные пользователя не обработаны
                    'auth' => $auths[$name], // Данные регистрации пользователя
                ];
            }
            // Кодируем в JSON-формат для удобного хранения в файле
            $strUsers = Json::encode($users);
            //Сохраняем файл
            file_put_contents($this->usersJSON, $strUsers);

            return $users;
        }
        // Получаем список пользователей из JSON-файла
        $strUsers = file_get_contents($this->usersJSON);

        return Json::decode($strUsers);
    }

    /**
     * Считать из файла данные авторизации пользователей и преобразовать их в массив
     *
     * @return array|null
     */
    protected function getUsersAuths()
    {
        if (file_exists($this->usersAuth)) {
            $lines = file($this->usersAuth);
            $auths = []; // Массив с данными авторизации пользователей
            foreach ($lines as $line) {
                $parts = explode(':', $line);
                if (null != $parts) {
                    $username = $parts[0];
                    $auths[$username] =[
                        'password_hash' => $parts[1],
                        'auth_key' => $parts[3],
                        'email' => $parts[2],
                        'created_at' => $parts[4],
                        'updated_at' => $parts[4],
                    ];
                }
            }

            return $auths;
        }

        return null;
    }

    /** Получить треки, активации и прочее конкретного пользователя
     *
     * @param array $userData
     * @return boolean
     */
    protected function saveUserAttributes($userData) {
        $username = key($userData);
        $auth = $userData[$username]['auth'];
        $dir = $this->dir .DIRECTORY_SEPARATOR. $username .DIRECTORY_SEPARATOR;

        if (file_exists($dir . 'config.cfg')) { // Файл конфигурации
            $model = new UserConfig();
            $user  = new User();

            $lines = file($dir . 'config.cfg');
            $config = UserConfig::extractCSV($lines);

            $user->username = (string)$username;
            $user->status = User::STATUS_ACTIVE;
            $user->attributes = $auth;

            if ($user->save()) {
                $model->attributes = $config;
                $model->link('user', $user); // Сохранение связанных данных
                if ($model->save()) {
                    // Изменяем статус в JSON-файле на "обработано"
                    $this->changeUserProcessedStatus($username, self::USER_PROCESSED);
                    // Треки пользователя
                    $this->saveUserTracks($user);
                } else {
                    $this->errors[$username][] = 'Error save to DB. Block UserAttributes.'
                        . 'User ID: ' . $user->id . "\n"
                        #. implode(chr(10), compact($model->errors));
                        . print_r($model->errors, 1);
                }

                if (file_exists($dir . 'payed.key')) { // Файл полученных ключей
                    #$model = new UserKey();
                    #$keys = file($dir . 'payed.key');
                }
            } else {
                $this->errors[$username][] = 'Error save user to DB. Block User'
                    . 'Username: ' . $username ."\n"
                    . print_r($user->errors, 1);
            }
        }

        if (empty($this->errors[$username])) { // Стандартное сообщение Success
            return 1;
        }

        return 0; // Есть ошибки
    }

    /**
     * Измение статуса обработки данных пользователя
     *
     * @param $username
     * @param $status
     *
     * @ignore Задел на будущее
     */
    protected function changeUserProcessedStatus($username, $status) {}

    /**
     * Сохранение пользовательских треков
     *
     * @param User $user
     * @return null
     */
    protected function saveUserTracks(User $user) {
        $userDir = $this->dir .DIRECTORY_SEPARATOR. $user->username .DIRECTORY_SEPARATOR. 'tracks';
        if (!is_dir($userDir))
            return null;

        foreach (new \DirectoryIterator($userDir) as $track) {
            if($track->isDot() || !$track->isFile()) continue;

            $modelTrack = new UserTrack();
            $modelTrack->link('user', $user);
            $modelTrack->trackFile = $userDir .'/'. $track->getFilename();
            // Только для Windows
            $modelTrack->name = iconv('WINDOWS-1251', 'UTF-8', $track->getBasename());
            /*$modelTrack->name = mb_convert_encoding(
                $track->getBasename(), 'utf-8', mb_detect_encoding($track->getBasename())
            );*/
            echo 'modelname: ' . $modelTrack->name . "\n";
            if (!$modelTrack->save()) {
                $this->errors[$user->username][] = 'Error save track to DB. Block saveUserTracks'
                    . 'Username: ' . $user->username ."\n"
                    . print_r($modelTrack->errors, 1);
            } /*else {
                'sucess: ' . $modelTrack->trackFile;
            }*/
        }
    }

    /**
     * Перенос всех пользователей
     *
     * @param bool $refresh
     */
    public function actionMigrate($refresh = false) {
        $users = $this->getListUsers($refresh);

        foreach ($users as $name=>$val) {
            $this->actionMigrateUser([$name=>$val]); #$res =
        }
        file_put_contents($this->dir .DIRECTORY_SEPARATOR. 'errors.txt', print_r($this->errors, 1));
    }

    /**
     * Перенос отдельного пользователя
     *
     * @param array $user [name => val]
     * @return boolean
     */
    public function actionMigrateUser($user) {
        return
            $this->saveUserAttributes($user);
    }

    /**
     * Формирует массив пользовательских треков
     *
     * @deprecated 01.07.2016
     */
    public function actionTracks()
    {
        $userTracks = UserTrack::find()->orderBy('user_id')->with('user')->asArray()->all();
        foreach ($userTracks as $track) {
            list($trackDir, $trackFullName) = UserTrack::getTrackFullAddress($track['id']);
            $destFile = $this->dirTracks .DIRECTORY_SEPARATOR. $trackDir .DIRECTORY_SEPARATOR. $trackFullName;
            $srcFile = $this->dir .DIRECTORY_SEPARATOR. $track['user']['username'] . 'tracks';
            echo "{$destFile} : {$srcFile} \n";
        }
    }
}