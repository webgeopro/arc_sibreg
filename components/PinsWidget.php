<?php
/**
 * Created by PhpStorm.
 * User: Webgeopro
 * Date: 12.07.2016
 * Time: 12:26
 */

namespace app\components;

use Yii;
use yii\base\Widget;

class PinsWidget extends Widget
{
    public $pins;
    public $config;

    public function init()
    {
        parent::init();
        $this->pins = Yii::$app->user->identity->pins;
    }

    public function run()
    {
        return $this->render('pins', [
            'pins' => $this->pins,
        ]);
    }
}