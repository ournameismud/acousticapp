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
use ournameismud\acousticapp\records\Favourites AS FavouriteRecord;

use Craft;
use craft\base\Component;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class Favourites extends Component
{
    // Public Methods
    // =========================================================================

    // Name: processFavourite
    // Purpose: service to process favourite (create if not exists, remove otherwise)
    // Required: $criteria
    // Optional: none
    // Services: 
    //      favourites/getFavourite()
    //      favourites/deleteFavourite()
    //      favourites/addFavourite()
    // Returns: record (object or string)

    public function processFavourite( array $criteria )
    {
        $record = $this->getFavourite( $criteria );
        if( $record ) $record = $this->deleteFavourite( $criteria);
        else $record = $this->addFavourite( $criteria );
        // Craft::dd
        return $record;
    }
    
    // Name: getFavourite
    // Purpose: service to favourites (by user or all)
    // Required: none
    // Optional: $user
    // Returns: records (array)

    public function getFavourites( $user = null )
    {
        $currentUser = Craft::$app->getUser();
        $records = FavouriteRecord::find();
        if (isset($user)) $records->where(['userId' => $user ]);
        return $records->all();   
    }
    
    // Name: getFavourite
    // Purpose: service to retrieve a specific favourite
    // Required: none
    // Optional: $criteria
    // Returns: record (single)

    public function getFavourite( array $criteria )
    {
        $records = FavouriteRecord::find()
            ->where( $criteria )->one();
        return $records;
    }

    // Name: addFavourite
    // Purpose: service to save a specific test as a favourite
    // Required: none
    // Optional: $criteria
    // Returns: string
    
    public function addFavourite( array $criteria )
    {
        $record = new FavouriteRecord;
        foreach($criteria AS $key => $value) {
            $record->$key = $value;
        }
        $record->save();
        return 'Favourite added';
    }

    // Name: deleteFavourite
    // Purpose: service to remove a specific test as a favourite
    // Required: none
    // Optional: $criteria
    // Returns: string
    
    public function deleteFavourite( array $criteria )
    {
        $records = FavouriteRecord::find()
            ->where( $criteria )->one();
        $records->delete();
        return 'Favourite removed';
    }
}
