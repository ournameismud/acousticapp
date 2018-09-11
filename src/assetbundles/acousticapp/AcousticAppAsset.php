<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp\assetbundles\AcousticApp;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class AcousticAppAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@ournameismud/acousticapp/assetbundles/acousticapp/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/script.js',
        ];

        $this->css = [
            'css/style.css',
        ];

        parent::init();
    }
}
