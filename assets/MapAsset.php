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
class MapAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'css/leaflet.min.css', // локальный CSS для картографической библиотеки Leaflet
        '//cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css', // CSS для картографической библиотеки Leaflet
    ];
    public $js = [
        '//cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js',
        #'js/leaflet.min.js',
        '//api-maps.yandex.ru/2.0/?load=package.map&lang=ru-RU',
        #'js/Yandex.min.js',
        '//maps.google.com/maps/api/js?v=3',
        #'js/Google.min.js',
        #'js/Bing.min.js',
        #'js/GPX.min.js',
        'js/all-maps.min.js',

];
    public $depends = [
        'app\assets\AppAsset'
    ];
}
