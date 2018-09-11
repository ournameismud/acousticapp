<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp\models;

use ournameismud\acousticapp\AcousticApp;

use Craft;
use craft\base\Model;
use craft\validators\DateTimeValidator;
use DateTime;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class Tests extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['dB', 'number'],
            ['fireRating', 'string'],
            ['manufacturer', 'string'],
            ['blankName', 'string'],
            ['intRef', 'string'],
            ['doorThickness', 'number'],
            ['doorset', 'string'],
            ['glassType', 'string'],
            ['lorientId', 'number'],
            ['testDate', DateTimeValidator::class],
        ];
    }
}
