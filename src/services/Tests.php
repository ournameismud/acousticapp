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
use ournameismud\acousticapp\records\Tests AS TestRecord;
use ournameismud\acousticapp\records\Seals AS SealRecord;
use ournameismud\acousticapp\records\TestsSeals AS TestsSealsRecord;

use Craft;
use craft\base\Component;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class Tests extends Component
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


    public function getTestsBySeals( $id ) {
        // Craft::dd( $id );
        $testIds = [];
        $tests = TestsSealsRecord::find()
            ->where( ['sealId' => $id ] )->all();
        foreach ($tests as $test) {
            $testIds[] = $test->testId;
        }
        $testIds = array_unique($testIds);
        // $records = 
        $records = $this->getTests(['id'=>$testIds]);
        return $records;
    }
    


    // Name: getTests
    // Purpose: service to fetch tests based on defined criteria
    // Required: none
    // Optional: $criteria (array or object), $sort
    // Records: 
    //      Tests
    // Returns: Tests query results (array)

    public function getTests( $criteria = null, $sort = 'asc' ) {
        $request = Craft::$app->getRequest();
        if ( isset($criteria) && is_numeric($criteria) ) {
            $records = TestRecord::find()
                ->where( ['id' => $criteria ] );
        } elseif ( isset($criteria) ) {
            $crits = [];
            $paras = [];
            $i = 0;
            $records = TestRecord::find();
            foreach ( $criteria AS $key => $value) {
                if (is_array($value)) {
                    if ($i == 0) $records->where( ['in', $key, $value ]);
                    else $records->andWhere( ['in', $key, $value ]);
                } elseif ($value != '') {
                    $tmp = substr($key,0,(strlen($key) - 4));
                    // echo $tmp . '<br />';
                    if(strpos($key,'_min') !== false) {
                        $q = $tmp . '>=:para_' . $i;
                        // $crits[] = $tmp . '>=:para_' . $i;
                    } elseif(strrpos($key,'_max') !== false) {
                        $q = $tmp . '<=:para_' . $i;
                        // $crits[] = $tmp . '<=:para_' . $i;
                    } else {
                        $q = $key . '=:para_' . $i;
                        // $crits[] = $tmp . '=:para_' . $i;
                    }
                    $paras[':para_' . $i] = $value;
                    if ($i == 0) $records->where( $q );
                    else $records->andWhere( $q );
                }                    
                $i++;
            }
            $records->addParams( $paras );                        
        } else {
            $records = TestRecord::find();
        }
        if ( isset($criteria) && is_numeric($criteria) ) return $records->one();
        else {            
            return $records->orderBy('dB ' . strtoupper($sort))->all();
        }
    }

    // Name: checkSeal
    // Purpose: service to check existence of specific seal by code
    // Required: string $sealCode
    // Optional: none
    // Records: 
    //      Seals
    // Returns: Seal Record ID
         
    public function checkSeal( string $sealCode ) {
        $site = Craft::$app->getSites()->getCurrentSite();
        $seals = SealRecord::find()->where([ 
            'sealCode' => trim($sealCode)
        ])->one();
        if (!$seals) {
            $seals = new SealRecord;
            $seals->siteId = $site->id;
            $seals->sealCode = trim($sealCode);
            $seals->save();
            // return $seals->id;
        } 
        return $seals->id;

    }    

    // Name: processSeals
    // Purpose: service to process codes and quantitiies from CSV upload
    // Required: string $seals
    // Optional: none
    // Returns: array
    
    public function processSeals( string $seals )
    {
        if (strlen(trim($seals)) == false) return array();
        $response = [];
        $seals = explode('&',$seals);
        $sealsCount = count($seals);
        foreach ($seals AS $seal) {
            $seal = trim($seal);
            $matches = preg_split("/2 x/i", $seal);
            $code = array_filter($matches);
            $code = array_values($code);
            if( count($matches) > 1) {
                if (array_key_exists($code[0], $response)) $response[trim($code[0])] += 2;
                else $response[trim($code[0])] = 2;
            } else {
                if (array_key_exists($seal, $response)) $response[$seal] += 1;
                else $response[$seal] = 1;
            }
        }
        return $response;
    }
    
    // Name: processUpload
    // Purpose: service to process CSV file upload
    // Required: file $file
    // Optional: none
    // Records: 
    //      Test
    //      TestsSeals
    // Services:
    //      processSeals
    //      checkSeal
    // Returns: TO DO
         
    public function processUpload( $file )
    {
        // https://stackoverflow.com/questions/23904850/read-csv-in-yii-framework
        $site = Craft::$app->getSites()->getCurrentSite();
        
        // open file
        $fileHandler = fopen($file->tempName,'r');        
        $csv = file($file->tempName);
        
        // define index columns
        $indexes = str_getcsv(array_shift( $csv ));
        $lorientId = array_search('Web Ref',$indexes);    

        $values = array();
        
        // get column names and ids defined in ./AcousticApp.php
        $cols = AcousticApp::getInstance()->cols;
        $count = 0;
        
        // echo '<pre>';
        // loop through CSV lines
        foreach ($csv AS $key => $row) {
            
            $tmpValues = str_getcsv($row);
            // skip empty lines
            if(count(array_filter($tmpValues)) == 0 OR $tmpValues[$lorientId] == '') continue; 
            $test = TestRecord::find()->where([ 
                'lorientId' => $tmpValues[$lorientId]
            ])->one();
            
            // see if text already exists, if not define new one            
            if (!$test) $test = new TestRecord;
            $tmpSeals = [];            
            $sealCodes = [];            
            // loop through line columns            
            foreach( $tmpValues AS $i => $v) {
                $index = trim($indexes[$i]);
                // if ($cols['test_lorientId'] == '') continue;
                // check  if column type is already defined 
                if (array_key_exists($index, $cols)) {
                    // conditional loop against type of cell contents
                    if (strpos($cols[$index],'test_') !== false) {
                        $tmpIndex = substr($cols[$index],5);
                        if ($tmpIndex == 'testDate' && $v !== '') {
                            $tmpDate = \DateTime::createFromFormat('d.m.y',$v);
                            if ($tmpDate) $test->$tmpIndex = $tmpDate->format('Y-m-d H:i:s');
                        } elseif ($tmpIndex == 'dB') {
                            preg_match('/\d+/',$v,$matches);
                            $test->$tmpIndex = $matches[0];
                        } elseif ($tmpIndex == 'doorThickness') {
                            preg_match('/\d+/',$v,$matches);
                            $test->$tmpIndex = $matches[0];
                        } else {
                            $test->$tmpIndex = $v;    
                        }
                    } elseif (strpos($cols[$index],'seal_') !== false) {
                        // get seal record (created if not exists)
                        $seals = $this->processSeals($v);
                        $tmpSeals[$index] = $seals;
                        $sealCodes = array_merge($sealCodes,$seals);
                    } else {
                        // echo $cols[$index] . ': ' . $v . '<hr />';
                    }                    
                }                
            }
            $test->siteId = $site->id;
            // save record
            $test->save();

            // loop through seal relationships if exist
            if (count($tmpSeals)) {
                foreach($tmpSeals AS $type => $codes) {
                    foreach($codes AS $code => $quantity) {
                        $sealId = $this->checkSeal( $code );
                        $testsSeal = TestsSealsRecord::find()->where([ 
                            'siteId' => $site->id,
                            'testId' => $test->id,
                            'sealId' => $sealId,
                            'context' => $type
                        ])->one();
                        // if doesn't exist create 
                        if (!$testsSeal) {
                            $testsSeal = new TestsSealsRecord;
                        } 
                        $testsSeal->siteId = $site->id;
                        $testsSeal->testId = $test->id;
                        $testsSeal->sealId = $sealId;
                        $testsSeal->context = $type;
                        $testsSeal->quantity = $quantity;              
                        $testsSeal->save();
                        $count++;
                    }
                }
            }
            // echo $count;
        }        
    }
}
