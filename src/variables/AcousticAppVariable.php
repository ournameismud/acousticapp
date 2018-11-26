<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp\variables;

use ournameismud\acousticapp\AcousticApp;
use ournameismud\acousticapp\records\Tests AS TestRecord;
use ournameismud\acousticapp\records\Seals AS SealRecord;


use Craft;
use craft\db\Query;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class AcousticAppVariable
{
    // Public Methods
    // =========================================================================

    // Name: getVars
    // Purpose: variable to retrieve range of parameters from Test table
    // Required: none
    // Optional: $criteria, %$append
    // Returns: array of values
    
    public function getVars( $criteria, $append = '---' ) {
        $results = (new Query())
            ->select($criteria)
            ->distinct( true )
            ->from(['{{%acousticapp_tests}}'])
            ->orderBy( $criteria )
            ->all();
        $tmp = [];
        if ($append) $tmp[''] = $append;
        foreach( $results AS $result ) {
            $tmp[$result[ $criteria ]] = $result[ $criteria ];
        }    
        return $tmp;
    }
    
    // Name: logSearch
    // Purpose: variable to check a specific search and return hash
    // Required: none
    // Optional: $criteria
    // Services: 
    //      searches/logSearch
    // Returns: hash (string)
    
    public function logSearch( $criteria = null ) {
        // $request->getIsCpRequest();
        // save search if not in CP        
        $search = AcousticApp::getInstance()->searches->logSearch( $criteria );
        return $search;        
    }

    // Name: getTests
    // Purpose: variable to retrieve tests by defined criteria
    // Required: none
    // Optional: $criteria, $sort
    // Services: 
    //      tests/getTests
    // Returns: Tests records 
    
    public function getTests( $criteria = null, $sort = 'asc' ) {
        
        $results = AcousticApp::getInstance()->tests->getTests( $criteria, $sort );        
        return $results;
    }

    // Name: getFaves
    // Purpose: variable to retrieve favourites by defined criteria
    // Required: none
    // Optional: $user
    // Services: 
    //      favourites/getFavourites
    // Returns: Favourites records 
    
    public function getFaves( $user = null, $format = null) {        
        $user = isset($user) ? (int) $user : $user;
        $results = AcousticApp::getInstance()->favourites->getFavourites( $user );
        if ($format == 'array')  {
            $ids = [];
            foreach($results AS $result) $ids[] = $result->testId;
            return $ids;
        } else return $results;
    }
    

    // Name: getTestSeals
    // Purpose: variable to retrieve stored relationships between seals and tests based on test Id
    // Required: testId
    // Optional: none
    // Services: 
    //      seals/getSealsByTest
    // Returns: TestSeals records 
    
    public function getTestSeals( int $testId ) {        
        $testSeals = AcousticApp::getInstance()->seals->getSealsByTest( $testId );
        return $testSeals;
    }

    public function getProdIds() {        
        $testSeals = AcousticApp::getInstance()->seals->getProdIds();
        return $testSeals;
    }
    
}
