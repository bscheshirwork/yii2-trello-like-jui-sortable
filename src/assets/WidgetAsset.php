<?php
namespace bscheshirwork\tljs\assets;

use yii\web\AssetBundle;

/**
 * Class AppAsset represent main AssetBundle for the GUI module.
 */
class WidgetAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/files/';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/main.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        "js/main.js",
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\jui\JuiAsset',
    ];
}
