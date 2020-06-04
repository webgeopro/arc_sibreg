<?php
/**
 * Активации: кол-во + пополнение счета
 * User: Webgeopro
 * Date: 12.07.2016
 */

namespace app\components;

use yii\base\Widget;

class TracksWidget extends Widget
{
    public $tracks;

    public function init()
    {
        parent::init();
        $this->tracks = \Yii::$app->user->identity->tracks;
    }

    public function run()
    {
        return $this->render('tracks', ['tracks'=>$this->tracks]);
    }
}