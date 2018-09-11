<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp\services;

use ournameismud\acousticapp\AcousticApp;
use ournameismud\acousticapp\records\Searches AS SearchRecord;

use Craft;
use craft\base\Component;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class Searches extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    
    public $site, $user;

    public function init() {
        $user = Craft::$app->getUser();
    } 

    // Name: logSearch
    // Purpose: service to check search and save if not already exists
    // Required: $criteria (array or object)
    // Optional: none
    // Records: 
    //      Search
    // Returns: hash (string)

    public function logSearch( $criteria ) {
        $site = Craft::$app->getSites()->getCurrentSite();
        $user = Craft::$app->getUser();

        $hash = md5(serialize( $criteria ));
        $searchRecords = $this->getSearch( $hash );
        if( count($searchRecords) == 0 ) {
            $search = new SearchRecord;
            $search->siteId = $site->id;
            $search->criteria = $criteria;
            $search->hash = $hash;
            $search->userId = $user->id;
            $search->save();
        } elseif (is_array($searchRecords)) {
            $search = $searchRecords[0];
        } else {
            $search = $searchRecords;
        }    
        return $search->hash;
    }
    // Name: getSearch
    // Purpose: service to check search results against hash
    // Required: $hash (string)
    // Optional: none
    // Records: 
    //      Search
    // Returns: records (array)
    public function getSearch( $hash ) {
        $records = SearchRecord::find()
            ->where( ['hash' => $hash] )->one();
        return $records;

    }
}
