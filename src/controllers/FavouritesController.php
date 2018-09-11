<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp\controllers;

use ournameismud\acousticapp\AcousticApp;

use Craft;
use craft\web\Controller;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class FavouritesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    
    // Name: post
    // Required: testId
    // Optional: none
    // Purpose: action to post a test as a favourite
    // Services: 
    //      favourites/processFavourite
    
    
    public function actionPost()
    {
        $this->requirePostRequest();
        $user = Craft::$app->getUser();
        $site = Craft::$app->getSites()->getCurrentSite();
        $request = Craft::$app->getRequest();
        $favourite = AcousticApp::getInstance()->favourites->processFavourite( [ 'userId' => $user->id, 'testId' => $request->getBodyParam( 'testId' ), 'siteId' => $site->id] );

        if ($request->getAcceptsJson()) {
            return $this->asJson(['response' => $favourite]);
        } else {
            Craft::$app->getSession()->setNotice($favourite);
            // $redirect = $request->getBodyParam('redirect');
            return $this->redirectToPostedUrl();
        }
    }
}
