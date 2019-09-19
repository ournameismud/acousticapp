<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp;

use ournameismud\acousticapp\services\Favourites as FavouritesService;
use ournameismud\acousticapp\services\Tests as TestsService;
use ournameismud\acousticapp\services\Seals as SealsService;
use ournameismud\acousticapp\services\Searches as SearchesService;
use ournameismud\acousticapp\variables\AcousticAppVariable;
use ournameismud\acousticapp\elements\Tests as TestsElement;
use ournameismud\acousticapp\elements\Seals as SealsElement;
use ournameismud\acousticapp\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class AcousticApp
 *
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 *
 * @property  FavouritesService $favourites
 * @property  TestsService $tests
 * @property  SealsService $seals
 */
class AcousticApp extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AcousticApp
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.14';
    public $cols = array(
        'Date of test' => 'test_testDate',
        'Acoustic Rating' => 'test_dB',
        'Fire Door rating' => 'test_fireRating',
        'Blank/Door manufacturer' => 'test_manufacturer',
        'Brand' => 'test_blankName',
        'Ref no' => 'test_intRef',
        'Doorset type' => 'test_doorset',
        'Door thickness (mm)' => 'test_doorThickness',
        'Glass Type' => 'test_glassType',
        'Head and Jamb seal' => 'seal_sealCode',
        'Meeting stile seal' => 'seal_sealCode',
        'Threshold seal' => 'seal_sealCode',
        'Threshold plate' => 'seal_sealCode',
        'Glazing seal' => 'seal_sealCode',
        'Web Ref' => 'test_lorientId',
    );
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'searches' => SearchesService::class
        ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'acoustic-app/favourites';
                $event->rules['siteActionTrigger2'] = 'acoustic-app/tests';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['acoustic-app/tests/test/<testId:\d+>'] = 'acoustic-app/tests/view';
                $event->rules['acoustic-app/hash/<hash:.+>'] = 'acoustic-app/tests/hash';
                $event->rules['cpActionTrigger1'] = 'acoustic-app/favourites/do-something';
                $event->rules['cpActionTrigger2'] = 'acoustic-app/tests/do-something';
            }
        );

        // Event::on(
        //     Elements::class,
        //     Elements::EVENT_REGISTER_ELEMENT_TYPES,
        //     function (RegisterComponentTypesEvent $event) {
        //         $event->types[] = TestsElement::class;
        //         $event->types[] = SealsElement::class;
        //     }
        // );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('acousticApp', AcousticAppVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'acoustic-app',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [
            'tests' => ['label' => 'Tests', 'url' => 'acoustic-app/tests'],
            'searches' => ['label' => 'Searches', 'url' => 'acoustic-app/searches'],
            'favourites' => ['label' => 'Favourites', 'url' => 'acoustic-app/favourites'],
            'upload' => ['label' => 'Upload', 'url' => 'acoustic-app/upload'],
        ];
        return $item;

    }

    // Protected Methods
    // =========================================================================

    // Craft: Settings
    // -------------------------------------------------------------------------

    public $hasCpSettings = true;

    protected function createSettingsModel ()
    {
        return new Settings();
    }

    // protected function settingsHtml()
    // {
    //     return \Craft::$app->getView()->renderTemplate(
    //         'simplemap/settings',
    //         [ 'settings' => $this->getSettings() ]
    //     );
    // }


    protected function settingsHtml()
    {
        return \Craft::$app->getView()->renderTemplate(
            'acoustic-app/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    public function getSettingsResponse()
    {
        return \Craft::$app->controller->renderTemplate('acoustic-app/settings');
        // return \Craft::$app->controller->renderTemplate('plugin-handle/settings/template');
    }

    
}
