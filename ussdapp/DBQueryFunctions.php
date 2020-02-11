<?php 
ob_start();
include_once 'connectionStr.php';
include_once 'smsApi.php';
include_once 'ussdresponsesender.php';
include_once 'ussdlog.php';
require 'vendor/autoload.php';
//require_once('AfricasTalkingGateway.php');
use AfricasTalking\SDK\AfricasTalking;
//include_once 'MoUssdReceiver.php';
//$receiver = new MoUssdReceiver();
//$smsApiObject = new smsApi();
//$connStrObject = new connectionStr();
  
 class DBQueryFunctions
 {
  
//

     public function __construct()
     {
         $this->smsApi = new smsApi;
         $this->connectionStr = new connectionStr;
         $this->ussdresponsesender = new ussdresponsesender;
     }
     public function LogUpdates($msisdn, $sessionID, $district, $menuvariable, $menuvalue)
     {
        
        if($district != NULL){
           $sql= "INSERT INTO `ussdtransaction_new`(`phone`, `sessionId`,`districtId`,`menuvariable`,`menuvalue`) VALUES ('$msisdn', '$sessionID','$district','$menuvariable','$menuvalue')";
            $result = $this->Insertion_UpdateQuerysMainSession($sql);
             // echo $result;
        }else{
            $sql= "INSERT INTO `ussdtransaction_new`(`phone`, `sessionId`,`menuvariable`,`menuvalue`) VALUES ('$msisdn', '$sessionID','$menuvariable','$menuvalue')";
            $result = $this->Insertion_UpdateQuerysMainSession($sql);
            
        }


        return $result;
    }
    public function checkData($id){
        $exists = 0;
        try{
            $conn = $this->connectionStr->ConnectionFc();
            $year = date('Y');
            //$today = date('Y-m-d');
            // $this->Languages_got();
            // $season = "unknown";
            // if((date('m') == 1) || (date('m') == 2) ) $season = 'JF';
            // else if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
            // else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
            // else $season = 'SOND';

            // $queryProc = "SELECT seasonal_forecast.year as year FROM division LEFT OUTER JOIN area_seasonal_forecast on area_seasonal_forecast.region_id =  division.region_id LEFT OUTER JOIN sub_region on area_seasonal_forecast.subregion_id = sub_region.id LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = 'kampala' AND seasonal_forecast.year = '$year' AND season_months.abbreviation = '$season' LIMIT 1";

            $queryProc = "SELECT * FROM daily_forecast WHERE date = '$today' AND language_id = '$id'";

            $query = $conn->query($queryProc);
            if ($query->num_rows > 0) {
                return 1;
            }else{
                return 0;
            }
            
        }catch(Exception $ex){ }
        
    }


    public function getDistrictDetails($district)
     {
         $regionDetails = "";
         try {
             $conn = $this->connectionStr->ConnectionFc();
             $districtName = strtoupper($district);
             $sql = "select division.id as districtid, division.division_name as districtname, 
                    region.region_name AS region, sub_region.sub_region_name as Subregion, 
                    region.id as regionid ,sub_region.id as subregionid , division.region_id, 
                    division.sub_region_id from division 
                    INNER JOIN region ON division.region_id = region.id 
                    INNER JOIN sub_region ON division.sub_region_id = sub_region.id "
                    . "WHERE division.division_name = '$districtName' ";
             $query = $conn->query($sql);
            
             if ($query->num_rows > 0) {
                 while ($row = $query->fetch_assoc()) {
                     if ($_SESSION['language'] == "english") {
                         $regionDetails = "District = ".$row["districtname"]." Region = ".$row["region"]." SubRegion: ".$row["Subregion"];
                     } elseif ($_SESSION['language'] == "luganda") {
                         $regionDetails = "Ddisitulikiti = ".$row["districtname"]." Region: = ".$row["region"]." SubRegion: ".$row["Subregion"];
                     } else {
                         $regionDetails = "District = ".$row["districtname"]." Region =  ".$row["region"]." SubRegion: ".$row["Subregion"];
                     }
                     $districtid = $row["districtid"];
                     $regionid = $row["regionid"];
                     $subregionid = $row["subregionid"];
                     $regionDetails = array($regionDetails,$districtid,$regionid,$subregionid);
               
                     // echo $regionDetails;
                 }
             } else {
                 $regionDetails = "";
             }
         } catch (Exception $e) {
         }
         return $regionDetails;
    }


    public function loadUssdMenu($menuname, $menu_table = NULL)
     {
        $menuVal = "";
        $queryProc = "";
        try {
            $conn = $this->connectionStr->ConnectionFc();

            $queryProc = "SELECT * FROM $menu_table WHERE menuname = '$menuname'";
            $query = $conn->query($queryProc);
            if ($query->num_rows > 0) {
                while ($row = $query->fetch_assoc()) {
                    $menuVal = $row["menudescription"];
                }
            }
        
            
            
        } catch (Exception $e) { }
      
        return $menuVal;
     }

    public function Get_Menu(){
       
        $queryProc = "";
        $menu_table = "";
        try {
            $lang = $_SESSION['language'];
            $conn = $this->connectionStr->ConnectionFc();
                
            $lang_qry = "SELECT language_text_table FROM ussdmenulanguage WHERE language = '$lang'";
            $qry = $conn->query($lang_qry);
            if ($qry->num_rows > 0) {
                while ($row = $qry->fetch_assoc()) {
                    $menu_table = $row['language_text_table'];
                }   
            }
        
        }catch(Exception $ex){}
        return $menu_table;
    }
     public function Languages_got(){
        $lang_qry = "SELECT * FROM ussdmenulanguage";
        try {
            $conn = $this->connectionStr->ConnectionFc();
            $lang_data = array();
            $ids = array();
            $qry = $conn->query($lang_qry);
            if ($qry->num_rows > 0) {
                $ct = 0;
                while ($row = $qry->fetch_assoc()) { $ct++;
                    // Must be changed to enable luganda and other language   from ">=" to "="
                    if($ct == 1){}
                    else{
                        $info = $this->checkData($row['id']);
                        if($info == 1){
                            $lang_data[] = $row['language'];
                            $ids[] = $row['id'];
                        }
                        
                    }
                }
                $_SESSION['languages'] = $lang_data;
                $_SESSION['ids'] = $ids;
            }
             
         } catch (Exception $e) {
         }
        
        return "fd";
    }
     function Messages($message,$phoneNumber){ 

        $resp = "";
        try{
            $textmessage = urlencode($message);
            $ch = curl_init();
            curl_setopt_array($ch,array(
            CURLOPT_RETURNTRANSFER =>1,   
             CURLOPT_URL =>'http://simplysms.com/getapi.php?email=rc4wids@yahoo.com&password=VBsd9A2&sender=8777&message='.$textmessage.'&recipients='.$phoneNumber,
            CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

            $resp = curl_exec($ch);

            curl_close($ch);
            
        }catch(Exception $e){}
        return $resp;

    }

     public function loaded_data($data_for, $ad=NULL){
        $menuVal = "";
        $queryProc = "";
         try {
            $conn = $this->connectionStr->ConnectionFc();
            $divisions = strtoupper($_SESSION['district']);
            $today = date('Y-m-d');
            $day = date("l", strtotime(date("Y-m-d")));
            $season_date = date('Y-m-d');
            $queryProc = "";


            // Determine table to get the final resulst from
            $lange = $_SESSION['language'];
            $qProc = "SELECT id, daily FROM ussdmenulanguage WHERE language = '$lange'";
            $seasonal_table = "";
            $identity = "";
            
            $query = $conn->query($qProc);
            while ($row = $query->fetch_assoc()) {
                $identity = $row['id'];
                // daily_forecast_data = $row['daily'];    
            }
            

            // $times = array('00:00','6:00','12:00','18:00');
            // // $actual_time = date('g:ia');

           
            // $timeframe = "";
            // if (time() >= strtotime($times[0]) && time() < strtotime($times[1])) {
            //     $timeframe = "Early Morning";
            // }else if (time() >= strtotime($times[1]) && time() < strtotime($times[2])) {
            //     $timeframe = "Late Morning";
            // }else if (time() >= strtotime($times[2]) && time() < strtotime($times[3])) {
            //     $timeframe = "Afternoon";
            // }else if (time() >= strtotime($times[3]) && time() < strtotime($times[0])) {
            //     $timeframe = "Late Evening";
            // }

            // checking for the month and getting the current season used to query the data
            $season = "unknown";
                // if((date('m') == 1) || (date('m') == 2) ) $season = 'JF';
              // else 
                if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
              else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
              else $season = 'SOND';


            //Read file translations
            $file = "Translation_files/".strtolower($_SESSION['language']);
            $handle = fopen($file.".txt", "r");
            $result = array();



            if($data_for == "Daily Forecast no advisory"){
                $queryProc = "SELECT daily_forecast_data.mean_temp as mean_temp, daily_forecast_data.wind_strength as strength, weather_category.cat_name as weather_desc, daily_forecast.weather as weather FROM daily_forecast LEFT OUTER JOIN daily_forecast_data ON daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN region ON region.id = daily_forecast_data.region_id LEFT OUTER JOIN division on division.region_id = region.id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$today' AND daily_forecast.language_id = '$identity'";
                $query = $conn->query($queryProc);
                if ($query->num_rows > 0) {
                     while ($row = $query->fetch_assoc()) {

                        if($_SESSION['language'] == "English"){
                            $menuVal = "$divisions, $day, $data_for\nWind Strength: ".$row["strength"]."\nTemperature: ".$row['mean_temp'].".C\nWeather: ".$row['weather_desc']."\nSummary: ".$row['weather'];
                        }else{
                             $daily_trans = array("Wind strength","Temperature","Weather","Daily forecast");
                             while (($line = fgets($handle)) !== false) {
                                foreach ($daily_trans as $key) {
                                    if(strpos($line, $key) !== false){
                                        $data = explode("=>", $line);
                                        $result[] =  $data[1];
                                    }
                                }
                             }
                            $menuVal = "$divisions, $day, $result[3]\n$result[0]".$row["strength"]."\n$result[1]".$row['mean_temp'].".C\n$result[2]".$row['weather']."";
                        }
                        
                        
                    }
                }
            }else if($data_for == "Dekadal Forecast no advisory"){ 
                // The dakadal with no advisory
                // You may label the query variable a different name
                $queryProc = "";


            }else if($data_for == "Seasonal Forecast no advisory"){
                $seasonal = "SELECT area_seasonal_forecast.onset_period as onset_period,area_seasonal_forecast.onsetdesc as onsetdesc,area_seasonal_forecast.peakdesc as peakdesc,area_seasonal_forecast.expected_peak as expected_peak,area_seasonal_forecast.enddesc as enddesc,area_seasonal_forecast.end_period as end_period, seasonal_forecast.year as year, season_months.abbreviation as abbreviation FROM division LEFT OUTER JOIN region on division.region_id = region.id LEFT OUTER JOIN area_seasonal_forecast on area_seasonal_forecast.region_id =  division.region_id LEFT OUTER JOIN sub_region on area_seasonal_forecast.subregion_id = sub_region.id LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions'  LIMIT 1";

                $query = $conn->query($seasonal);
                if ($query->num_rows > 0) {
                    while ($row = $query->fetch_assoc()) {
                         if(date('Y') == $row["year"] && (($row["abbreviation"]) == $season)){

                         	 if($_SESSION['language'] == "English"){
	                             $menuVal = "$divisions:\nThe rains shall start in ".$row["onsetdesc"]." ".$row["onset_period"].", peak will be in ".$row["peakdesc"]." ".$row["expected_peak"]." and end in ".$row["enddesc"]." ".$row["end_period"];
	                        }else{
	                        	$rec2 = array("The rains shall start in","peak will be in","and end in","Late","Mid","Early","Seasonal forecast");
	                        	while (($line = fgets($handle)) !== false) {
	                                foreach ($rec2 as $key) {
	                                    if(strpos($line, $key) !== false){
	                                        $data = explode("=>", $line);
	                                        $result[] =  $data[1];
	                                    }
	                                }
	                            }

	                            // if($row['peakdesc']  == "Early") $row['peakdesc'] = $result[2];
	                            // else if($row["peakdesc"] == "Mid") $row["peakdesc"] = $result[1];
	                            // else $row["peakdesc"] = $result[0];

	                            // if($row['onsetdesc']  == "Early") $row['onsetdesc'] = $result[2];
	                            // else if($row["onsetdesc"] == "Mid") $row["onsetdesc"] = $result[1];
	                            // else $row["onsetdesc"] = $result[0];

	                            // if($row['enddesc']  == "Early") $row['enddesc'] = $result[2];
	                            // else if($row["enddesc"] == "Mid") $row["enddesc"] = $result[1];
	                            // else $row["enddesc"] = $result[0];

	                            $menuVal = "$result[6] $divisions:\n$result[3]".$row["onsetdesc"].$row['onset_period'].", $result[4]".$row["peakdesc"].$row["expected_peak"]." $result[5]".$row["enddesc"].$row["end_period"];
	                        
	                        }

                            
                        }
                    }
                }
                
            }else if($data_for == "Daily Forecast"){
                // Daily forecast with advisory
                // date_default_timezone_set('Africa/Nairobi');
               

                $daily_and_advisory="SELECT daily_advisory.message_summary as summary,weather_category.cat_name as weather_desc, minor_sector.minor_name as sector, daily_forecast.time as currently FROM daily_advisory LEFT OUTER JOIN minor_sector on minor_sector.id=daily_advisory.sector LEFT OUTER JOIN daily_forecast on daily_forecast.id=daily_advisory.forecast_id LEFT OUTER JOIN daily_forecast_data on daily_forecast_data.forecast_id = daily_advisory.forecast_id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN division on division.region_id = daily_forecast_data.region_id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$today' AND daily_forecast.language_id = '$identity'";


                if(isset($ad)){
                    $daily_and_advisory .=" AND minor_sector.minor_name = '$ad'";
                }
                // $daily_and_advisory .=" LIMIT 1";
                $sectors_data = array();
                $query = $conn->query($daily_and_advisory);
                if ($query->num_rows > 0) {
                    while ($row = $query->fetch_assoc()) {

                     $daily_data = "SELECT daily_forecast_data.mean_temp as mean_temp, daily_forecast_data.wind_strength as strength, daily_forecast.weather as weather FROM daily_forecast LEFT OUTER JOIN daily_forecast_data ON daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN region ON region.id = daily_forecast_data.region_id LEFT OUTER JOIN division on division.region_id = region.id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$today' LIMIT 1";

                     $qy = $conn->query($daily_data);
                     $strength = "";
                     $mean_temp = "";
                     $weather = "";
                    
                
                    while ($rows = $qy->fetch_assoc()) {
                        $strength = $rows['strength'];
                        $mean_temp = $rows['mean_temp'];
                        $weather = $rows['weather'];
                       
                    }

                        if($_SESSION['language'] == "English"){
                            $menuVal = "$divisions, $day, $data_for\nWind Strength: ".$strength."\nTemperature: ".$mean_temp.".C\nWeather: ".$row['weather_desc']."\nSummary: ".$weather."\n".$row['sector']." advisory: ".$row['summary'];
                        }else{
                             $daily_trans = array("Wind strength","Temperature","Weather","Advisory");
                             while (($line = fgets($handle)) !== false) {
                                foreach ($daily_trans as $key) {
                                    if(strpos($line, $key) !== false){
                                        $data = explode("=>", $line);
                                        $result[] =  $data[1];
                                    }
                                }
                             }
                             $menuVal = "$divisions, $day, $data_for$divisions\n".$result[0].": ".$strength."\n".$result[1].": ".$mean_temp.".C\n".$result[2].": ".$weather."\n".$result[3]." ".$row['sector'].": ".$row['summary'];
                        }



                        
                        $sectors_data[] = $row['sector'];
                    }
                    $_SESSION['sectors'] = $sectors_data;
                }
            }else if($data_for == "Dekadal Forecast"){
                // Dekadal forecast with advisory
                // You may label the query variable a different name
                $queryProc = "";

                
            }else if($data_for == "Seasonal Forecast"){
                // write a query that joins the forecast to the advisory but for the where clause of advisory sector
                // it should be in the if statement
                $seasonal_and_advisory = "SELECT advisory.message_summary as summary, season_months.abbreviation as abbreviation, seasonal_forecast.year as year, minor_sector.minor_name as sector FROM advisory LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id LEFT OUTER JOIN seasonal_forecast  on  advisory.forecast_id = seasonal_forecast.id LEFT OUTER JOIN area_seasonal_forecast  on  advisory.forecast_id = area_seasonal_forecast.forecast_id LEFT OUTER JOIN division  on  area_seasonal_forecast.region_id = division.region_id LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions'";
                if(isset($ad)){
                    $seasonal_and_advisory .=" AND minor_sector.minor_name = '$ad'";
                }
                $seasonal_and_advisory .=" GROUP BY advisory.id";

                $sectors_data = array();
                $query = $conn->query($seasonal_and_advisory);
                if ($query->num_rows > 0) {
                    while ($row = $query->fetch_assoc()) {
                        $data_qry = "SELECT area_seasonal_forecast.onsetdesc as onsetdesc, area_seasonal_forecast.onset_period as onset_period, area_seasonal_forecast.peakdesc as peakdesc, area_seasonal_forecast.expected_peak as expected_peak, area_seasonal_forecast.enddesc as enddesc, area_seasonal_forecast.end_period as end_period FROM division LEFT OUTER JOIN area_seasonal_forecast on area_seasonal_forecast.region_id =  division.region_id LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id WHERE division.division_name = '$divisions' LIMIT 1";
                        
                        $querying = $conn->query($data_qry);
                        $onsetdesc = "";
                        $onset_period = "";
                        $peakdesc = "";
                        $expected_peak = "";
                        $enddesc = "";
                        $end_period = "";


                        while ($rows = $querying->fetch_assoc()) {
                            $onsetdesc = $rows["onsetdesc"];
                            $onset_period = $rows["onset_period"];
                            $peakdesc = $rows["peakdesc"];
                            $expected_peak = $rows["expected_peak"];
                            $enddesc = $rows["enddesc"];
                            $end_period = $rows["end_period"];
                        }

                        if(date('Y') == $row["year"] && (($row["abbreviation"]) == $season)){

                            $menuVal = $data_for." for $divisions:\nThe rains shall start in ".$onsetdesc." ".$onset_period.", peak will be in ".$peakdesc." ".$expected_peak." and end in ".$enddesc." ".$end_period."\n".$row['sector']." advisory: ".$row['summary'];

                            $sectors_data[] = $row['sector'];
                        }
                    }
                    $_SESSION['sectors'] = $sectors_data;
                }
                
            }else{

            }
             // The menuVal variable in the loop holds the data from the database that shall be send via the sms, when is starts working
           //changed the format(john)
            
            

         }catch(Exception $e){
         }
         return $menuVal;
     }

     public function sendMessage($phoneNumber, $message)
     {
         $msg=str_replace("<br/>", "\n", $message);
         $resp = "";
         try {
             $textmessage = urlencode($msg);
             $url = 'http://simplysms.com/getapi.php';
             $urlfinal = $url.'?'.'email'.'='.'rc4wids@yahoo.com'.'&'.'password'.'='.'VBsd9A2'.'&'.'sender'.'='.'8777'.'&'.'message'.'='.$textmessage.'&'.'recipients'.'='.$phoneNumber;
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL, $urlfinal);
             curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER =>1,CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

             $resp = curl_exec($ch);

             curl_close($ch);
         } catch (Exception $e) {
         }
         return $resp;
     }

     public function Insertion_UpdateQuerys($sql)
     {
            try {
                $conn = $this->connectionStr->ConnectionFc();
        
        
             if ($conn->query($sql) === true) {
                 
             } else {
                 $action = "end";
                 $strToDisp = "APPLICATION CONNECTION ERROR";
                 $this->smsApi->ussdResponseSender(trim($strToDisp), $action);
             }
      
             //$conn->close();
         } catch (Exception $ex) {
         }
     }
    
     public function Insertion_UpdateQuerysMainSession($sql)
     {
         $status = "";
         try {
             $conn = $this->connectionStr->ConnectionFc();
        
             if ($conn == "") {
                 $status = "1" ;
             } else {
                 if ($conn->query($sql) === true) {
                     //
                     $status = "0" ;
                 } else {
                     $status = "1" ;
                 }
             }
        
      
             //$conn->close();
         } catch (Exception $ex) {
         }
    
         return $status;
     }
    public function triggerCall($number){
       
        try{
            $this->makeCall($number);
        }catch(Exception $e){
            // echo "Encountered an error while making the call";
            echo $e;
        }
    }
    function makeCall($number){
                // Set your app credentials

             $username = "personalization";
             $apiKey   = "bfc0a4fef4dcbd28c2010e042b701fc96aa5e3efca0a3f49abd0f735584653b2";
    
            // // Initialize the SDK
            $AT       = new AfricasTalking($username, $apiKey);
    
            // Get the voice service
            $voice    = $AT->voice();
    
            // // Set your Africa's Talking phone number in international format
             $from     = "+256312319888";
    
            // // Set the numbers you want to call to in a comma-separated list
             $to       = "+".$number;
    
            try {
            //         // Make the call
                    $results = $voice->call([
                        'from' => $from,
                       'to'   => $to
                    ]);
    
                    // print_r($results);
                  
                    return $results;
             } 
            catch (Exception $e) {
                return $e;
                echo "Error: ".$e->getMessage();
             } 
    }
 }

?>
