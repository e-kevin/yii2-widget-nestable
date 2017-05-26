<?php
namespace wonail\nestable;

use yii\web\AssetBundle;

class NestableAsset extends AssetBundle
{

    public $sourcePath = '@wonail/nestable/assets';

    public $css = [
        'jquery.nestable.min.css',
    ];

    public $js = [
        'jquery.nestable.min.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
