<?php
/**
 * Активации: кол-во + пополнение счета
 * User: Webgeopro
 * Date: 12.07.2016
 */

namespace app\components;

use yii\base\Widget;

class LimitsWidget extends Widget
{
    public $config;

    public function run()
    {
        return $this->render('limits', ['limits'=>$this->config->limit]);
    }
}