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

    protected function arrayFind($array, $field, $value)
    {
       foreach($array as $key => $item)
       {
          if ( $item[$field] === $value )
             return $key;
       }
       return false;
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
        } elseif ( isset($criteria) && is_array($criteria) && array_key_exists('product',$criteria) ) {
            $productId = $criteria['product'];
            $seals = SealRecord::find()->where([ 
                'craftId' => $productId
            ])->column();
            if (count($seals)) {
                $sealIds = [];
                $testSeals = TestsSealsRecord::find()->where(['sealId'=>$seals[0]])->all();
                foreach($testSeals AS $testSeal) {
                    $sealIds[] = $testSeal['testId'];
                }
                $records = TestRecord::find()->where(['in','id',$sealIds])->orderBy('dB ' . strtoupper($sort));                
            } else {
                $redir = 'null!!';
            }            
            
        } elseif ( isset($criteria) && is_array($criteria)) {
            $crits = [];
            $paras = [];
            $i = 0;
            $records = TestRecord::find();
            foreach ( $criteria AS $key => $value) {
                if (is_array($value) && $key == 'glassType') {
                    // Craft::dd($value);
                    if (in_array('Glazed',$value) AND in_array('Unglazed',$value)) {
                        $paras[':para_' . $i] = '';
                        $q = $key . '!=:para_' . $i;
                    } elseif (in_array('Glazed',$value)) {
                        $paras[':para_' . $i] = 'Unglazed';
                        $q = $key . '!=:para_' . $i;
                    } elseif (in_array('Unglazed',$value)) {
                        $paras[':para_' . $i] = 'Unglazed';
                        $q = $key . '=:para_' . $i;                        
                    }                    
                    if (isset($q)) {
                        if ($i == 0) $records->where( $q );
                        else $records->andWhere( $q );    
                    }
                } elseif (is_array($value)) {
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
        if ( isset($criteria) && is_numeric($criteria) ) {
            return $records->one();
        } else {            
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
            // check tags here
            $tags = \craft\elements\Tag::find()
                ->group('productCodes')
                ->title( $sealCode )
               ->all();
            if (count($tags) > 0) {
                $product = \craft\elements\Entry::find()
                    ->section('products')
                    ->relatedTo( $tags )
                   ->one();
                if (count($product)) {
                    $seals->craftId = $product->id;
                } 
            } 
            $seals->save();
        } else {
            // attempt to upgrade craft id if null
            if ($seals->craftId == '') {
                $tags = \craft\elements\Tag::find()
                    ->group('productCodes')
                    ->title( $sealCode )
                   ->all();
                if (count($tags) > 0) {
                    $product = \craft\elements\Entry::find()
                        ->section('products')
                        ->relatedTo( $tags )
                       ->one();
                    if (count($product)) {
                        $seals->craftId = $product->id;
                    } 
                } 
                $seals->save();   
            }
        }
        return $seals->id;

    }    

    // Name: processSeals
    // Purpose: service to process codes and quantitiies from CSV upload
    // Required: string $seals
    // Optional: none
    // Returns: array
    public function processSeals( array $tmpSeals, int $testRecordId )
    {
        $site = Craft::$app->getSites()->getCurrentSite();
        $sealsCount = 0;        
        $failCodes = [];
        foreach ($tmpSeals AS $key => $value) {
            // clear testSeals with testId == $testRecordId && context == $key
            $testSeals = TestsSealsRecord::find()->where([ 
                'siteId' => $site->id,
                'testId' => $testRecordId,
                'context' => $key
            ])->all();
            // TODO: delete here ???
            // explode on delimiter (, or &)
            // $seals = explode('&',$value);
            $seals = explode(',',$value);
            $sealIds = [];
            foreach ($seals AS $seal) {
                $seal = trim($seal);
                if (strpos(strtoupper($seal),'2 X') !== false) {
                    $multiplier = explode('2 X',strtoupper($seal));
                    $q = 2;
                    $seal = trim($multiplier[1]);
                } else {
                    $q = 1;
                    $seal = trim($seal);
                }
                $sealId = $this->checkSeal($seal);

                $tags = \craft\elements\Tag::find()
                    ->group('productCodes')
                    ->title( $seal )
                    ->all();
                if (count($tags) == 0) $failCodes[] = $seal;
                $testSeal = TestsSealsRecord::find()->where([ 
                    'siteId' => $site->id,
                    'testId' => $testRecordId,
                    'sealId' => $sealId,
                    'context' => $key
                ])->one();
                // if doesn't exist create 
                if (!$testSeal) {
                    $testSeal = new TestsSealsRecord;
                } 
                $testSeal->siteId = $site->id;
                $testSeal->testId = $testRecordId;
                $testSeal->sealId = $sealId;
                $testSeal->context = $key;
                $testSeal->quantity = $q;
                $testSeal->save();
                $sealsCount++;
            }
        }
        return array('success'=>$sealsCount,'fail'=>array_unique($failCodes));
        // Craft::dd($tmpSeals);
        // if (strlen(trim($seals)) == false) return array();
        // $response = [];
        // $seals = explode('&',$seals);
        // $sealsCount = count($seals);
        // foreach ($seals AS $seal) {
        //     $seal = trim($seal);
        //     $matches = preg_split("/2 x/i", $seal);
        //     $code = array_filter($matches);
        //     $code = array_values($code);
        //     if( count($matches) > 1) {
        //         if (array_key_exists($code[0], $response)) $response[trim($code[0])] += 2;
        //         else $response[trim($code[0])] = 2;
        //     } else {
        //         if (array_key_exists($seal, $response)) $response[$seal] += 1;
        //         else $response[$seal] = 1;
        //     }
        // }
        // return $response;
    }
    

    // Name: processRows
    // Purpose: service to process rows from CSV
    // Required: 
    //      int $cols
    //      array $ref
    //      array $data
    // Optional: none
    
         
    public function processRows( int $cols, $refs, $data )
    {
        $site = Craft::$app->getSites()->getCurrentSite();
        $seals = 0;
        $tests = 0;
        $fail = [];
        $webRefIndex = $this->arrayFind($refs,'lable','test_lorientId');
        $webRef = $refs[$webRefIndex];
        // loop through data row
        foreach($data AS $row) {
            // get cols from data
            $col = explode(',',$row);
            $items = str_getcsv($row);
            $tmp = array_filter($items);
            if (count($tmp) == 0) continue;            
            
            if (count($items) <> $cols) {
                // log error here
                Craft::dd($items);                
            } else {
                $lorientIdIndex = $webRef['index'];
                $lorientId = $items[$lorientIdIndex];

                // check test against record
                // get id
                $testRecord = TestRecord::find()->where([ 
                    'lorientId' => $lorientId
                ])->one();
                
                // see if text already exists, if not define new one            
                if (!$testRecord) $testRecord = new TestRecord;

                // loop through reference items
                $tmpSeals = [];
                foreach ($refs AS $ref) {
                    // if lable == 'seal_sealCode'
                    // explode by comma or ampersand
                    // loop through all items
                    // pass to service (sealRecord)
                    //      if new create
                    //      else get record id
                    // build out testRecord
                    // build arrays ready to pass to testsSealsRecord

                    $title = $ref['key'];
                    $handle = $ref['lable'];
                    $data = trim($items[$ref['index']]);
                    if (strpos($handle,'seal_') !== false && $data != '') {
                        $tmpSeals[$title] = $data;
                    } elseif (strpos($handle,'test_') !== false && $data != '') {
                        $property = substr($handle,5);
                        if ($property == 'testDate') {
                            $tmpDate = \DateTime::createFromFormat('d.m.y',$data);
                            if ($tmpDate) $testRecord->$property = $tmpDate->format('Y-m-d H:i:s');
                        } elseif ($property == 'dB') {
                            preg_match('/\d+/',$data,$matches);
                            $testRecord->$property = $matches[0];                                
                        } elseif ($property == 'doorThickness') {
                            preg_match('/\d+/',$data,$matches);
                            $testRecord->$property = $matches[0];
                        } else {
                            $testRecord->$property = $data;
                        }                        
                    }                                        
                }
                $testRecord->siteId = $site->id;
                $testRecord->save();
                // pass to seals service
                //      $testRecord->id
                //      $tmpSeals
                // Craft::dd( $tmpSeals);
                $data = $this->processSeals($tmpSeals, $testRecord->id);
                $seals += $data['success'];
                $tests++;
                $fail = array_merge($fail, $data['fail']);
                
            }
        }
        return array('tests'=>$tests,'seals'=>$seals,'fail'=>array_unique($fail));     
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
         
    public function processUpload( $file, $step = null )
    {
        // https://stackoverflow.com/questions/23904850/read-csv-in-yii-framework
        $site = Craft::$app->getSites()->getCurrentSite();
        
        // open file
        $fileHandler = fopen($file->tempName,'r');        
        $csv = file($file->tempName);
        $srcCols = AcousticApp::getInstance()->cols;

        $cols = [];
        foreach ($srcCols AS $key=> $value) {
            $cols[] = $key;
        }
        $data = array(
            'data' => json_encode($csv),
            'header' => str_getcsv(array_shift( $csv )),
            'cols' => $cols
        );
        
        return $data;
        /*()
        // define index columns
        $indexes = str_getcsv(array_shift( $csv ));
        $lorientId = array_search('Web Ref',$indexes);    

        $values = array();
        
        // get column names and ids defined in ./AcousticApp.php
        $cols = AcousticApp::getInstance()->cols;
        $count = 0;
        
        $tmp = [];
        foreach ($csv AS $item) {
            $row = str_getcsv($item);            
            foreach (range(11, 15) as $number) {
                $tmp[] = $row[$number];
            }
            
        }
        $tmp = array_unique($tmp);
        $codes = [];
        echo '<pre>';
        foreach ($tmp AS $code) {
            $codeTmp = str_replace(',',' & ',$code);
            $codeArr = explode('&',$codeTmp);
            foreach ($codeArr AS $codeStr) {
                $codeStr = trim($codeStr);
                $splitMultiplier = explode('2 X', strtoupper($codeStr));                
                foreach ($splitMultiplier AS $splitCode) {
                    $codes[] = trim($splitCode);
                }
                // echo $codeStr;
            }
            // print_r($codeTmp);
        }
        $codes = array_unique($codes);
        sort($codes);
        
        $prods = [];
        foreach ($codes AS $code) {
            $tags = \craft\elements\Tag::find()
                ->group('productCodes')
                ->title( $code )
               ->all();
            if (count($tags) == 1) {
                $tmpEntry = $tags[0];
                $prods[$code] = $tmpEntry->id;  
            } elseif (count($tags) > 1) {
                $prods[$code] = 'MULTIPLE MATCHES';  
            } else {
                $prods[$code] = 'NO MATCH';  
            }
            echo $code . ': ' . count($tags) . '<br />';
        }
        Craft::dd($prods);
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
        */      
    }
}
