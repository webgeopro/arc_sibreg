<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
        '//fonts.googleapis.com/css?family=Roboto+Condensed:400,700&subset=latin,cyrillic',
        '//fonts.googleapis.com/css?family=PT+Sans:400,700&subset=latin,cyrillic',
        //'css/font-awesome.min.css',// 
        //maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css
        'css/site.min.css', // Базовые стили
    ];
    public $js = [
        //'//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',

        'js/scripts.min.js', // Базовые скрипты
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
