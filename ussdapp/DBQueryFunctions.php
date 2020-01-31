<?php 
ob_start();
include_once 'connectionStr.php';
include_once 'smsApi.php';
include_once 'ussdresponsesender.php';
include_once 'ussdlog.php';
require 'vendor/autoload.php';
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
     public function queryMenu($id)
     {
         $queryStr = "Select Menus.menuDescription 
   from Menus where menuId= '".mysql_real_escape_string($id)."'";
         $result = mysql_query($queryStr);
         $result_array = array();
         if ($result !="") {
             while ($row = mysql_fetch_assoc($result)) {
                 $result_array[] = $row;
                 $stringToPrint = implode(",", $result_array);
                 return $stringToPrint;
             }
         }
    
         //mysql_close($connect);
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

     public function LogUssdTran($msisdn, $sessionID, $levelVal, $InstVal)
     {
         try {
             $queryStr = "UPDATE  ussdtransaction SET Level0 = '$InstVal' WHERE Msisdn = '$msisdn' AND SessionId = '$sessionID' ";
        
             // }
        
             $result = $this->Insertion_UpdateQuerys($queryStr);
             //echo $result;
         } catch (Exception $ex) {
         }
     }
    
     public function LogUssdmaintran($msisdn, $sessionID)
     {
         $queryStr = "";
         $resultFn = "";
    
         try {
             $queryStr1 = "SELECT * FROM ussdtransaction WHERE Msisdn = '$msisdn' AND SessionId = '$sessionID'";

             $status = $this->checkIfSessionAlreadyLogged($queryStr1);
             logFile($status);
        
             if ($status == "1") {
                 $queryStr = "INSERT INTO  ussdtransaction (Msisdn,SessionId) VALUES ('$msisdn','$sessionID')";
                 $result = $this->Insertion_UpdateQuerysMainSession($queryStr);
           
                 if ($result == "0") {
                     $resultFn = "0";
                 } else {
                     $resultFn = "1";
                 }
             } else {
                 $resultFn = "0";
             }
         } catch (Exception $ex) {
            
           // echo $ex;
         }
 
         return $resultFn;
     }

//////////////////////// AMoko/////////////////////////

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
            $qry = $conn->query($lang_qry);
            if ($qry->num_rows > 0) {
                $ct = 0;
                while ($row = $qry->fetch_assoc()) { $ct++;
                    if($ct == 1){}
                    else{
                        $lang_data[] = $row['language'];
                    }
                }
                $_SESSION['languages'] = $lang_data;
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
//////////////////////// AMoko/////////////////////////

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
             curl_setopt_array($ch, array(
CURLOPT_RETURNTRANSFER =>1,
//CURLOPT_URL =>$urlfinal,
CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

             $resp = curl_exec($ch);

             curl_close($ch);
         } catch (Exception $e) {
         }
         return $resp;
         //echo  $phoneNumber;
     }

    
     public function UpdateUssdTran($msisdn, $sessionID, $Level, $levelVal)
     {
         try {
             $queryStr = "UPDATE  ussdtransaction SET $Level = '$levelVal' WHERE Msisdn = $msisdn and SessionId = $sessionID";
        
             $result = $this->Insertion_UpdateQuerys($queryStr);
             // echo $result;
         } catch (Exception $ex) {
         }
     }
    
     public function UpdateUssdTranRegionIds($msisdn, $sessionID, $districtid, $regionid, $subregionid)
     {
         try {
             $queryStr = "UPDATE  ussdtransaction SET districtid = '$districtid', regionid = '$regionid' , subregionid = '$subregionid' WHERE Msisdn = $msisdn and SessionId = $sessionID";
        
             $result = $this->Insertion_UpdateQuerys($queryStr);
             //echo $result;
         } catch (Exception $ex) {
         }
     }
    
     public function SelectAdvisory($msisdn, $sessionID)
     {
         try {
             $queryStr = "Select * from  ussdtransaction  WHERE Msisdn = $msisdn and SessionId = $sessionID";
        
             $result = $this->SelectQuerys($queryStr);
         } catch (Exception $ex) {
         }
     }
   


     public function getAdvisoryAndLogMessage($queryString, $conn, $PhoneNumber)
     {
         try {
             $query = $conn->query($queryString);
             if ($query->num_rows > 0) {
                 while ($row = $query->fetch_assoc()) {
                     if ($_SESSION['language'] == "english") {
                         $advisory = $row["description"];
                     } elseif ($_SESSION['language'] == "luganda") {
                         $advisory = $row["descriptionLuganda"];
                     }
                
                     $number = $PhoneNumber;
                     $text = $advisory;
                     $message=str_replace("<br/>", "\n", $text);
                     
                     $sms_api_result = $this->sendMessage($number, $message);


                     //$this->ussdresponsesender->endResponse();

//send end transaction params
                 }
             } else {
                   
//                    $sms_api_result = $this->smsApi->sendMessage($number, "No Advisory Found");
                 if ($_SESSION['language'] == "english") {
                     $action = "end";
                     $strToDisp = "No Advisory Found.";
                     logFile($strToDisp);
                     $this->smsApi->ussdResponseSender(trim($strToDisp), $action);
                 } elseif ($_SESSION['language'] == "luganda") {
                     $action = "end";
                     $strToDisp = "Tetusobodde kufuna kyosabye akasera kano, gezako eddako.";
                     $this->smsApi->ussdResponseSender(trim($strToDisp), $action);
                 } else {
                     $action = "end";
                     $strToDisp = "No Advisory Found.";
                     $this->smsApi->ussdResponseSender(trim($strToDisp), $action);
                 }
             }
         } catch (Exception $ex) {
         }
     }
     
//////////////////////// AMoko////////////////////////////
     public function loaded_data($data_for, $ad=NULL){
        $menuVal = "";
        $queryProc = "";
         try {
            $conn = $this->connectionStr->ConnectionFc();
            $divisions = $_SESSION['district'];
            $today = date('Y-m-d');
            $season_date = date('Y-m-d');
            $queryProc = "";

            $times = array('00:00','6:00','12:00','18:00');
            // $actual_time = date('g:ia');

           
            $timeframe = "";
            if (time() >= strtotime($times[0]) && time() < strtotime($times[1])) {
                $timeframe = "Early Morning";
            }else if (time() >= strtotime($times[1]) && time() < strtotime($times[2])) {
                $timeframe = "Late Morning";
            }else if (time() >= strtotime($times[2]) && time() < strtotime($times[3])) {
                $timeframe = "Afternoon";
            }else if (time() >= strtotime($times[3]) && time() < strtotime($times[0])) {
                $timeframe = "Late Evening";
            }

            // checking for the month and getting the current season used to query the data
            $season = "unknown";
                // if((date('m') == 1) || (date('m') == 2) ) $season = 'JF';
              if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
              else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
              else $season = 'SOND';

            if($data_for == "Daily Forecast no advisory"){
                $queryProc = "SELECT daily_forecast_data.mean_temp as mean_temp, daily_forecast_data.wind_strength as strength, daily_forecast.time as currently, daily_forecast.weather as weather FROM daily_forecast_data LEFT OUTER JOIN division on division.id = daily_forecast_data.division_id LEFT OUTER JOIN daily_forecast on daily_forecast.id = daily_forecast_data.forecast_id WHERE division.division_name = '$division' AND daily_forecast.date = '$today'";
                $query = $conn->query($queryProc);
                if ($query->num_rows > 0) {
                     while ($row = $query->fetch_assoc()) {
                        // if($timeframe == $row['currently']){
                             $menuVal = "Daily Forecast for $divisions:\nWind Strength: ".$row["strength"]."\nTemperature: ".$row['mean_temp']."\nWeather: ".$row['weather']."";
                        // }
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
                            $menuVal = "Seasonal Forecast for $divisions:\nThe rains shall start in ".$row["onsetdesc"]." ".$row["onset_period"].", peak will be in ".$row["peakdesc"]." ".$row["expected_peak"]." and end in ".$row["enddesc"]." ".$row["end_period"];
                        }
                    }
                }
                
            }else if($data_for == "Daily Forecast"){
                // Daily forecast with advisory
                // date_default_timezone_set('Africa/Nairobi');
               

                $daily_and_advisory="SELECT daily_advisory.message_summary as summary, minor_sector.minor_name as sector, daily_forecast.time as currently FROM daily_advisory LEFT OUTER JOIN minor_sector on daily_advisory.sector = minor_sector.id LEFT OUTER JOIN daily_forecast_data on daily_advisory.forecast_id = daily_forecast_data.forecast_id LEFT OUTER JOIN daily_forecast on daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN division on daily_forecast_data.division_id = division.id WHERE division.division_name ='$divisions' AND daily_forecast.date = '$today'";


                if(isset($ad)){
                    $daily_and_advisory .=" AND minor_sector.minor_name = '$ad'";
                }
                // $daily_and_advisory .=" LIMIT 1";
                $sectors_data = array();
                $query = $conn->query($daily_and_advisory);
                if ($query->num_rows > 0) {
                    while ($row = $query->fetch_assoc()) {

                     $daily_data = "SELECT daily_forecast_data.wind_strength as strength, daily_forecast_data.mean_temp as mean_temp, daily_forecast.time as currently, daily_forecast.weather as weather FROM daily_forecast_data LEFT OUTER JOIN division on division.id = daily_forecast_data.division_id LEFT OUTER JOIN daily_forecast on daily_forecast.id = daily_forecast_data.forecast_id WHERE division.division_name = '$division' AND daily_forecast.date = '$today' LIMIT 1";

                     $qy = $conn->query($daily_data);
                     $strength = "";
                     $mean_temp = "";
                     $weather = "";
                
                    while ($rows = $qy->fetch_assoc()) {
                        $strength = $rows['strength'];
                        $mean_temp = $rows['mean_temp'];
                        $weather = $rows['weather'];
                    }


                        $menuVal = $data_for." for $divisions:\nWind Strength: ".$strength."\nTemperature: ".$mean_temp."\nWeather: ".$weather."\n".$row['sector']." advisory: ".$row['summary'];
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
            


            $queries = "";
            if(strlen($queryProc)>5){
                
            } 

            // New code



            // else if(strlen($sql_query)>5){
            //     $queries = $sql_query;
            //     $query = $conn->query($queries);
            //     if ($query->num_rows > 0) {
            //         $count = 0;
            //          while ($row = $query->fetch_assoc()) { $count++;
            //             if($count == 1){
            //                 $menuVal = "WIDS\nDaily Forecast for $divisions:\nWind Strength: ".$row["strength"]."\nTemperature: ".$row['mean_temp']."\nWeather: ".$row['weather'];
            //             }else{
            //                 $menuVal .= "\nSector: ".$row['sector']."\nAdvice: ".$row['summary'];
            //             }
            //         }
            //     }
            // }
            

         }catch(Exception $e){
         }
         return $menuVal;
     }
   //////////////////////// AMoko////////////////////////////



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
    
     public function checkIfSessionAlreadyLogged($sql)
     {
         $status = "";
         try {
             $conn = $this->connectionStr->ConnectionFc();
        
        
             $query = $conn->query($sql);
            
             if ($query->num_rows > 0) {
                 $status = "0";
             } else {
                 $status = "1";
             }
         } catch (Exception $ex) {
         }
    
         return $status;
     }



   public function selectforecastQueryStrings($strType, $district)
     {
        $queryStr = "";
        switch ($strType) {
            case  "Seasonal":
                $queryStr = "select daily_forecast_data.mean_temp, daily_forecast_data.wind_strength, daily_forecast.weather from daily_forecast_data left join division on division.id = daily_forecast_data.division_id left join daily_forecast on daily_forecast.id = daily_forecast_data.forecast_id where division.division_name = '$district'" ;
                logFile($queryStr);
                break;
            case  "Dekadal":
                $queryStr = "";
                break;
            case  "Daily":
                
                $queryStr = "select daily_forecast_data.mean_temp, daily_forecast_data.wind_strength, daily_forecast.weather from daily_forecast_data left join division on division.id = daily_forecast_data.division_id left join daily_forecast on daily_forecast.id = daily_forecast_data.forecast_id where division.division_name = '$district'";
                
                break;
       }
  
         return $queryStr;
     }

//check whether  the request is a seasonal request
     public function checkIfSeasonal($sessionId) {
      
        try {
            $conn = $this->connectionStr->ConnectionFc();
            $sql="select Level1 from ussdtransaction where Sessionid='$sessionId'";
            $query = $conn->query($sql);
            while ($row = $query->fetch_assoc()) {
                if ($row["Level1"] == "Seasonal Audio" || $row["Level1"] == "Seasonal SMS") {
                    $response = "Yes";
                } else {
                    $response = "No";
                }
            }
        }catch(Exception $e){}
            return $response;


     }
     //trigger call if audio requested
     public function triggerCall($number,$sessionId){
       
         try{
            $conn = $this->connectionStr->ConnectionFc();
             $sql = "SELECT * from ussdtransaction WHERE Level1 ='Seasonal Audio' AND SessionId = $sessionId";
             $query = $conn->query($sql);
             
             if ($query->num_rows > 0){
                 $this->makeCall($number);
                 $sql="UPDATE ussdtransaction SET MessageSent=0 WHERE Msisdn = '$number' AND SessionId = '$sessionId'";
                            //$conn->query($sql);
                            $query = $conn->query($sql);
        
             }
             

         }catch(Exception $e){

         }
     }
     //insert the language for seasonal forecast
     public function enterLanguage($sessionid,$language){
           
        try {
            $queryStr = "UPDATE  ussdtransaction SET Level8 = '$language' WHERE SessionId = $sessionid";
       
            $result = $this->Insertion_UpdateQuerys($queryStr);
            //echo $result;
        } catch (Exception $ex) {
        }


     }

     //get the languages in given district
     public function getLanguage($district){
         
         $districtName = strtoupper($district);
         $districtlang = [];
         $districtlang[1]="Please Select a language for ".$districtName;
         $counter = 2;
         $count =1;
              try{
                $conn = $this->connectionStr->ConnectionFc();
                
                
                $sql = "select ussddistricts.districtname as name, ussddistricts.districtid as districtid,"
                       ."language_district.language_id,language.id,language.description as language"
                       ." from ussddistricts LEFT OUTER JOIN language_district on ussddistricts.districtid ="
                       ."language_district.district_id LEFT OUTER JOIN language on language_district.language_id=language.id"
                       ." where ussddistricts.districtname='$districtName'"
                       ." ORDER by language.id ASC";
                       

                       $query = $conn->query($sql);
                       
                       if ($query->num_rows > 0) {
                        while ($row = $query->fetch_assoc()) {
                            
                                    $districtlang[$counter] = $count.". ".$row['language'];
                                    
                                    $counter++;
                                    $count++;
                                    
                                }
                       }
                       else{
                        logFile("Emptyy lang");
                       }
                       
                      }catch(Exception $e){}
                        return $districtlang;


     }

      //get the languages in given district
      public function getLanguageValue($district){
         
        $districtName = strtoupper($district);
        $districtlang = [];
        $counter =1;
       
             try{
               $conn = $this->connectionStr->ConnectionFc();

               
               $sql = "select ussddistricts.districtname as name, ussddistricts.districtid as districtid,"
                      ."language_district.language_id,language.id,language.description as language"
                      ." from ussddistricts LEFT OUTER JOIN language_district on ussddistricts.districtid ="
                      ."language_district.district_id LEFT OUTER JOIN language on language_district.language_id=language.id"
                      ." where ussddistricts.districtname='$districtName'"
                      ." ORDER by language.id ASC";
                      

                      $query = $conn->query($sql);
                      
                      if ($query->num_rows > 0) {
                       while ($row = $query->fetch_assoc()) {
                           
                                   $districtlang[$counter] = $row['language'];
                                   
                                   $counter++;
                                   
                                   
                               }
                      }
                      else{
                       logFile("No language Found");
                      }
                      
                     }catch(Exception $e){}
                       return $districtlang;


    }
    ################################################################################################
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




    
     #################################################################################################
     public function loadnextMenu($menuname)
     {
         $menuVal = "";
         $queryProc = "";
         
         try {
             $conn = $this->connectionStr->ConnectionFc();
       
             if ($_SESSION['preference']== "SMS") {
                 $queryProc = "SELECT * FROM ussdmenu WHERE menuname = '$menuname'";
             } elseif ($_SESSION['preference']== "Audio") {
                 $queryProc = "SELECT * FROM ussdmenu WHERE menuname = '$menuname'";
             } elseif ($_SESSION['language']== "") {
                 $queryProc = "SELECT * FROM ussdmenulanguage";
             }
      
             $query = $conn->query($queryProc);
     
             if ($query->num_rows > 0) {
                 while ($row = $query->fetch_assoc()) {
                     $menuVal = $row["menudescription"];
                 }
             }
         } catch (Exception $e) {
         }
      
         return $menuVal;
     }

     //get district by name
     public function getDistrictId($sessionid){
         $name="";
         $query="";
         
         try{
            $conn = $this->connectionStr->ConnectionFc();
    
            $sql = "SELECT "
            ."ussddistricts.districtname from ussdtransaction INNER JOIN ussddistricts ON"
            ." ussddistricts.districtid = ussdtransaction.districtid WHERE SessionId='$sessionid'";
            $query = $conn->query($sql);
           $field= $query->fetch_assoc();
           $name = $field['districtname'];

         }catch(Exception $e){

         }
         return $name;
     }
     ###################################################################################################

      ###########################################################################################

      function makeCall($number){

        
    
                // Set your app credentials
            $username = "personalization";
            $apiKey   = "bfc0a4fef4dcbd28c2010e042b701fc96aa5e3efca0a3f49abd0f735584653b2";
    
            // Initialize the SDK
            $AT       = new AfricasTalking($username, $apiKey);
    
            // Get the voice service
            $voice    = $AT->voice();
    
            // Set your Africa's Talking phone number in international format
            $from     = "+256312319888";
    
            // Set the numbers you want to call to in a comma-separated list
            $to       = "+".$number;
    
            try {
                    // Make the call
                    $results = $voice->call([
                        'from' => $from,
                        'to'   => $to
                    ]);
    
                    //print_r($results);
                  
                    return $results;
            } 
                catch (Exception $e) {
                    echo "Error: ".$e->getMessage();
            } 
            }
    
    
      ###########################################################################################
      
     public function SelectQuerys($sql)
     {
         try {
             $conn = $this->connectionStr->ConnectionFc();
     
             $query = $conn->query($sql);
      
             if ($query->num_rows > 0) {
                 while ($row = $query->fetch_assoc()) {
                     $mainCategory = $row["Level0"];
                     $subCategory  = $row["Level1"];
                     $Location = $row["Location"];
                     $PhoneNumber = $row["Msisdn"];
                     $SessionId = $row["SessionId"];
                     $Advisory = "The".$mainCategory.", Subcategory".$subCategory."for ".$Location." is Test Advisory";
        
                     switch ($mainCategory) {
  case  "weather-forecast":
      $sqlStr = "";
      switch ($subCategory) {
      
      case "Seasonal":
          $strType = "Seasonal";
          $sqlStr = $this->selectforecastQueryStrings($strType, $SessionId, $PhoneNumber);
          break;
      case "Dekadal":
          $strType = "Dekadal";
           $sqlStr = $this->selectforecastQueryStrings($strType, $SessionId, $PhoneNumber);
          break;
      case  "Daily":
          $strType = "Daily";
           $sqlStr = $this->selectforecastQueryStrings($strType, $SessionId, $PhoneNumber);
          break;
      
      }
      
                    // sql script for
                    //seasonal forecast
                    
        
  $this->getAdvisoryAndLogMessage($sqlStr, $conn, $PhoneNumber);
       
  break;
                
  case "agriculture-and-food-security":
       
       
  $queryStr = "SELECT advisory.message as description, advisory.record_id, advisory.advice, advice.id_advice, advice.advice_name, "
           ." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
           ." ussdregions.name as regionname, "
." ussdsubregions.subregionname as subregionname FROM advisory LEFT OUTER JOIN ussdtransaction on advisory.region = ussdtransaction.regionid "
." AND advisory.subregionid = ussdtransaction.subregionid LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
." LEFT OUTER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
." LEFT OUTER JOIN ussdsubregions ON ussdtransaction.subregionid = ussdsubregions.subregionid WHERE "
." ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
."  AND advisory.advice = '$subCategory' AND advisory.message IS NOT NULL group by advisory.message,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
." ussdtransaction.regionid ,advisory.record_id , ussdtransaction.subregionid, ussdregions.name , ussdsubregions.subregionname "

." order BY advisory.record_id DESC LIMIT 1";;
  $this->getAdvisoryAndLogMessage($queryStr, $conn, $PhoneNumber);
                   
  break;
               
  case "disaster-advisory":
 
     $queryStr = "SELECT advisory.message as description, advisory.advice, advice.id_advice, advice.advice_name, "
     ." ussdtransaction.Msisdn as telephone, advisory.record_id,ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
     ."ussdregions.name as regionname, "
."ussdsubregions.subregionname as subregionname FROM advisory INNER JOIN ussdtransaction on advisory.region = ussdtransaction.regionid "
." AND advisory.subregionid = ussdtransaction.subregionid INNER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
." INNER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
." INNER JOIN ussdsubregions ON ussdsubregions.subregionid = ussdtransaction.subregionid WHERE ussdtransaction.SessionId = '$SessionId' "
." AND ussdtransaction.Msisdn = '$PhoneNumber' AND advisory.advice = '$subCategory' AND advisory.message IS NOT NULL group by advisory.message,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
."ussdtransaction.regionid , ussdtransaction.subregionid,advisory.record_id, ussdregions.name , ussdsubregions.subregionname "

." order BY advisory.record_id DESC LIMIT 1";
                   $this->getAdvisoryAndLogMessage($queryStr, $conn, $PhoneNumber);
               break;
               
        
      }
                 }
             } else {
      
     // echo "No Information found for advisory. Please try again later.";
             }

             $conn->close();
         } catch (Exception $ex) {
         }
     }
 }

?>
