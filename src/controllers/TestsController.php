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
use ournameismud\acousticapp\records\Tests AS TestRecord;
use ournameismud\acousticapp\records\Seals AS SealRecord;
use ournameismud\acousticapp\records\TestsSeals AS TestsSealsRecord;

use Craft;
use craft\web\Controller;
use craft\web\UploadedFile;


/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class TestsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['upload-tests','hash','fetch-tests'];

    // Public Methods
    // =========================================================================

    // Name: fetch-tests
    // Required: none
    // Optional: none
    // Purpose: action to fetch (search) against Tests
    // Services: 
    //      tests/getTests
    
    public function actionFetchTests()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $product = $request->getBodyParam('product');
        $testId = $request->getBodyParam('id');
        // define this in form itself
        $redir = $request->getBodyParam('redirect');
        $redir = empty($redir) ? 'acousticsearch/results' : $redir;
        if ($testId) {
            $tests = AcousticApp::getInstance()->tests->getTests( $testId );
            // Craft::dd( $tests );
            return $this->renderTemplate($redir, ['results'=>[$tests]]);
        } elseif ($product) {
            // abstract to service here?
            $seals = SealRecord::find()->where([ 
                'craftId' => $product
            ])->column();
            $tests = AcousticApp::getInstance()->tests->getTestsBySeals( $seals );
            return $this->renderTemplate($redir, ['results'=>$tests]);
        }

        $fields = [
            'fireRating',
            'dB_min',
            'dB_max',
            'doorset',
            'glassType',
            'manufacturer',
            'doorThickness_min',
            'doorThickness_max',
        ];
        $criteria = [];
        foreach ($fields AS $field) {
            $criteria[ $field ] = $request->getBodyParam( $field );
        }
        $tests = AcousticApp::getInstance()->tests->getTests( $criteria );
        if (\Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson($tests);
        } else {
            return $this->renderTemplate($redir, ['results'=>$tests, 'criteria'=> $criteria]);
            // return $this->asJson($tests);
        }
    }

    // Name: hash
    // Purpose: action to load CP view based on hash
    // Required: string $hash
    // Services: 
    //      searches/getSearch

    public function actionHash( string $hash )
    {
        // $variables = [
        //     'testId' => $testId,
        //     'testRecord' => AcousticApp::getInstance()->tests->getTests( $testId ),
        //     'testSeals' => AcousticApp::getInstance()->seals->getSealsByTest( $testId )
        //     // 'sealsRecord' => AcousticApp::getInstance()->seals->getSeals( $testId )
        // ];
        $searchRecord = AcousticApp::getInstance()->searches->getSearch( $hash );
        $criteria = (array)json_decode($searchRecord->criteria);
        return $this->renderTemplate('acoustic-app/tests/index', $criteria);
    }

    // Name: view
    // Purpose: action to show specific tests in the admin area
    // Required: int $testId (TestRecord)
    // Optional: none
    // Services: 
    //      tests/getTests
    //      seals/getSealsByTest

    public function actionView( int $testId )
    {
        $variables = [
            'testId' => $testId,
            'testRecord' => AcousticApp::getInstance()->tests->getTests( $testId ),
            'testSeals' => AcousticApp::getInstance()->seals->getSealsByTest( $testId )
            // 'sealsRecord' => AcousticApp::getInstance()->seals->getSeals( $testId )
        ];
        
        return $this->renderTemplate('acoustic-app/tests/test', $variables);
    }

    // Name: upload-tests
    // Purpose: action to upload tests from CSV to Records
    // Required: file testsFile (CSV)
    // Optional: none
    // Services: 
    //      tests/processUpload

    public function actionUploadTests()
    {
        $this->requirePostRequest();        
        $result = 'Welcome to the TestsController actionDoSomething() method';
        $request = Craft::$app->getRequest();
        $file = UploadedFile::getInstanceByName('testsFile');
        // check if CSV here
        $result = AcousticApp::getInstance()->tests->processUpload( $file );
        return $result;
    }

    public function actionSaveTest()
    {
        $this->requirePostRequest();        
        $request = Craft::$app->getRequest();
        $testId = $request->getBodyParam('testId');
        $report = $request->getBodyParam('report')[0];
        // $file = UploadedFile::getInstanceByName('testsFile');
        // update record
        // 
        // 
        $testRecord = TestRecord::find()->where([ 
            'id' => $testId
        ])->one();
        $testRecord->report = $report;
        $testRecord->save();
        Craft::dd($testRecord);
        if (!$seals) {
            $seals = new SealRecord;
            $seals->siteId = $site->id;
            $seals->sealCode = trim($sealCode);
            $seals->save();
            // return $seals->id;
        } 
        Craft::dd($report);
        Craft::dd($testId);
    }
}
