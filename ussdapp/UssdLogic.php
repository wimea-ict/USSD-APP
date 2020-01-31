<?php 
ob_start();
include_once 'MoUssdReceiver.php';
include_once 'ussdlog.php';
include_once 'weatherParams.php';
include_once 'DBQueryFunctions.php';
include_once 'UssdAppMain.php';
include_once 'smsApi.php';
include_once 'voicecall.php';//voice
 ?> 
<?php
$receiver = new MoUssdReceiver(); // Create the Receiver object
$weatherparams = new weatherParams();
$dbFunctions = new DBQueryFunctions();
$smsApiFunctions = new smsApi();
$call = new voicecall();

$message = $receiver->getInput(); // get the message content
$sessionId = $receiver->getMSISDN(); // get the session ID;
$msisdn = $receiver->getMSISDN(); // get the phone number
//$serviceCode = $receiver->getMSC(); // get the service code

class UssdLogic extends DBQueryFunctions{ 



public function __construct(){

$this->DBQueryFunctions = new DBQueryFunctions;
$this->MoUssdReceiver = new MoUssdReceiver;
$this->smsApi = new smsApi;

$message = $this->MoUssdReceiver->getInput(); // get the message content
$sessionId = $this->MoUssdReceiver->getSessionId(); // get the session ID;
$msisdn = $this->MoUssdReceiver->getMSISDN(); // get the phone number
//$serviceCode = $this->MoUssdReceiver->getMSC(); // get the service code

$date = new DateTime();


}

// Retrieve and display menu
function Display($menuName){
$responseMsg = "";//$this->menu();
$regionDetails = "";
$action = "";
$strToDisp = "";

 try{
    $menuItemArray = null;
    
    if($menuName != null){
       

        
        if($menuName == "End" || $menuName == "Cancel"){


                    $action ="end";

            }else{

                //continue
                     $action ="request";

            }

            if($menuName == "regions"){

                        //display District entry request prompt
                    $menuName = "district";
                    $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);
            
            }
            else if($menuName == "season_lang"){
                //logFile("check seasonal ".$this->MoUssdReceiver->getInput());
                $responseMsg1 = $this->DBQueryFunctions->getLanguage($this->MoUssdReceiver->getInput());
                $responseMsg = implode("-",$responseMsg1);
               // logFile($responseMsg);
                               

            }else if($menuName == "Submission-opt"){
                $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);
            }
//////////////////////////// // AMoko/////////////////////////
            else if($menuName == "sector"){
                $for = $_SESSION['forecast_for'];
                if($_SESSION['language'] == 'english'){
                    $responseMsg = $for." with Advisory-Select a Sector";
                }else if($_SESSION['language'] == 'luganda'){
                    $responseMsg ="Entebereza y'obudde okuli okuwabulwa-Londa Ku ky'okuwabulwa";
                }
                
                $ct = 0;
                foreach ($_SESSION['sectors'] as $sects) { $ct++;
                    $responseMsg .="-".$ct." ".$sects;
                }


                if($_SESSION['language'] == 'english'){
                    $responseMsg .="-0. Back";
                }else if($_SESSION['language'] == 'luganda'){
                    $responseMsg .="-0. Ddayo emabega";
                }
                
            }else if($menuName == "language"){
                $responseMsg = "Please select language-1. English";
                $ct = 1;
                $ty = $this->DBQueryFunctions->Languages_got();
                foreach ($_SESSION['languages'] as $langs) { $ct++;
                    $responseMsg .="-".$ct.". ".$langs;
                }
            }
//////////////////////////// // AMoko/////////////////////////
             else {
                $menu_table = $this->DBQueryFunctions->Get_Menu();
                //Query menu options by the menuname
                    $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table);
                // $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);
            
            }
            
            $menuItemArray =explode("-",$responseMsg);
            // logFile($menuItemArray);
                foreach($menuItemArray as $item)
                {
            
            $strToDisp.=$item .  "\n";

                }
                // Call ussdResponseSender function from smsApi clss
                //to send a json response to the requesting client
                $this->smsApi->ussdResponseSender($strToDisp,$action);

}else{

            // end the session if menu is null and display error
            $action = "end";
            $responseMsg = $this->DBQueryFunctions->loadUssdMenu("Cancel");
            $menuItemArray =explode("-",$responseMsg);
            foreach($menuItemArray as $item)
            {

            $strToDisp = $item."\n";

            } 
            $this->smsApi->ussdResponseSender($strToDisp,$action);
            
}
    }catch (Exception $ex){
$action = "end";
$strToDisp = "Exception Error In Application";
$this->smsApi->ussdResponseSender($strToDisp,$action);
        
       // echo "Exception Error In Application";
    }


}  

////////////////////////// Amoko/////////////////////////
function ProcessDistrict($input,$sessionId,$msisdn){
    $menuName = "";
    $menuVar = "District";
    if($input == ""){
        $menuName = "district";
        $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, $menuName);
        
    }else{
        $regionDetails = $this->DBQueryFunctions->getDistrictDetails($input);
        if ($regionDetails == "") {
                 //header('Content-Type: application/x-www-form-urlencoded');
                 // header('Flow-Control: end');
                     
            $menuName = "invaliddistrict"; 
            // $this->Display("invaliddistrict");
             // phone, sessionid, district, menuvariable, menuValue
            $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, $menuName);
        }else{
            // Store district in sessions
            $_SESSION['district'] = $input;
            $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $input, $menuVar, $input);
            $menuName = "product";
            
        }
    }
    $this->Display($menuName);
    return $menuName;

}

// Don't make any changes here, this one is now fine
// Changes are to be made in the DBQueryFunctions.php file which holds the queries for the data

function ProcessProduct($input,$sessionId,$msisdn){
    $menuName = "";
    $menuVar = "Product";
    $with_ad = "";
    $_SESSION['menu_on'] = "";
    // $conn = $this->connectionStr->ConnectionFc();
    switch ($input) {
        case '1':
            $_SESSION['forecast_for'] = "Daily Forecast";
            $with_ad = "true";
            break;
        case '2':
            $_SESSION['forecast_for'] = "Dekadal Forecast";
            $with_ad = "true";
            break;
        case '3':
            $_SESSION['forecast_for'] =  "Seasonal Forecast";
            $with_ad = "true";
            break;
        case '4':
             $_SESSION['forecast_for'] = "Daily Forecast no advisory";
            break;
        case '5':
            $_SESSION['forecast_for'] = "Dekadal Forecast no advisory";
            break;
        case '6':
            $_SESSION['forecast_for'] = "Seasonal Forecast no advisory";
            break;
        
        default:
            $_SESSION['forecast_for'] = "invaliddistrict";

            $_SESSION['menu_on'] = "product";

            // $this->Display("invalidinput");
            $menuName = "invalidinput";
            break;
    }
    // For valiidity checks
    
    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $_SESSION['forecast_for']);
    $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']);
    $_SESSION['final_message'] = $responseMsg;
    if(strlen($_SESSION['menu_on']) > 3){
    }else{
        $menuName = $this->availability($responseMsg, $with_ad);
    }
    
    $this->Display($menuName);
    return $menuName;
}

public function availability($entry_data, $with_ad){
    $menuName = "";
    if(strlen($entry_data)>3){
        if($with_ad == "true"){
            $menuName = "sector";
        }else{
            $menuName = "response_format";
        }
    }else{
        $menuName = "no_data";
    }
    return $menuName;
}

// The response for either text or audio
public function ProcessResponse($input,$sessionId,$msisdn){
    $menuName = "";
    $responseMsg = "";
    $menuVar = "Response format";
    switch ($input) {
        case '1':
            $menuName = "text";
            //Must put the sms sennding function here
            

            $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $menuName);
            $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']);
            $_SESSION['final_message'] = $responseMsg;
            $menuName = "Submission-opt";
            

            break;
        
        default:
            $_SESSION['menu_on'] = "response_format";
            $menuName = "invalidinput";
            break;
    }
    
    $this->Display($menuName);
    return $menuName;

}
// When there is no forecast data
public function NoData($input,$sessionId,$msisdn){
    $menuName = "no_data";
    $menuVar = "Product";
    switch ($input) {
        case '9':
            $menuName = "product";
            break;
        case '0':
            $menuName = "district";
            break;
        default:
            $menuName = "no_data";
            break;
    }
    $this->Display($menuName);
    return $menuName;
}


public function ProcessSector($input,$sessionId,$msisdn){
    $delta = "";
    if(($input <= count($_SESSION['sectors'])) && $input > 0){
        $ct = 0;
        foreach ($_SESSION['sectors'] as $s) {
            if(($input-1) == $ct){

                $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Sector", $s );

                $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for'],$s);
                $_SESSION['final_message'] = $responseMsg;
            }
            $ct++;
        }
        $menuName = "response_format";
    }else if($input == 0){
        $this->Display('product');
    }else{
        $_SESSION['menu_on'] = "sector";
        $menuName = "sector";
    }
    $this->Display($menuName);
    return $menuName;

}

////////////////////////// Amoko/////////////////////////


function ProcessMainMenu($input,$sessionId,$msisdn) {
    $menuName = null;
    
     switch ($input) {
                case "1":
                    $menuName = "agriculture-and-food-security";
                   
                    break;
                case "2":
                  
                    $menuName = "disaster-advisory";
                  
                    break;
                case "3":
                    $menuName = "weather-forecast";
                    
                    break;
                case "4":
                    $menuName = "give-feedback";
                    
                                       
                    break;
                
                default:
                    $menuName = "main";
                    
                    break;

            }
            
                
                if($menuName != "main"){
                
            // $this->DBQueryFunctions->LogUssdTran($msisdn,$sessionId,$input,$menuName);
                $this->Display($menuName);
                                        }else{
                                            
                                   
                }               
                return $menuName;
}
####################################################################################
//process preference
// function ProcessPreference($input,$sessionId,$msisdn){
//     $menuName = null;    
//         switch ($input) {
//                     case "1":
//                         $_SESSION['preference'] = "SMS";
//                         $menuName = "Allcat";
//                     //$this->Display($menuName);
//                     break;
//                     case "2":
//                          $_SESSION['preference'] = "Audio";
//                         $menuName = "Allcat";
//                        // $this->Display($menuName);
//                     break;
//                     default:
                        
//                          $_SESSION['preference'] = "";
//                         $menuName = "preference";
                        
//                         break;
                        
//          }
             
//               $resultFn = $this->DBQueryFunctions->LogUssdmaintran($msisdn,$sessionId);
                    
//                     if($resultFn != "0"){
//                        $action = "end";
//                        $strToDisp = "APPLICATION ERROR";
//                        $this->smsApi->ussdResponseSender($strToDisp,$action);
                       
//                     }else{
//                         $this->Display($menuName);
//                     }
                       
             
             
             
//              return $menuName;
        
//     }
########################################################################################################  
 

function ProcessLanguage($input,$sessionId,$msisdn){
    $delta = "";
    if($input == 1){
        $_SESSION['language'] = "English";
        $menuName = "district"; 
    }
    else if((($input-1) <= count($_SESSION['languages'])) && ($input-1) > 0){
        $ct = 0;
        foreach ($_SESSION['languages'] as $s) {
            if(($input-2) == $ct){
                $_SESSION['language'] = $s;
            }
            $ct++;
        }
        $menuName = "district"; 
    }else{

        // This will be for the invalid input
        $_SESSION['language'] = "";
        $menuName = "language";
    }

    if(strlen($_SESSION['language'])> 2){
        $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, "Language", $_SESSION['language'] );
    }
        
    $this->Display($menuName);
    return $menuName;
}

function InitialiseGlobalTable($menuOpt) {
    
    $tableName = null;

                switch($menuOpt) {
                    
                    case "agricultural-advisory":
                        $tableName = "AgriculturalAdvisoryRequests";
                        break;
                    case "food-advisory":
                        $tableName = "FoodAdvisory";
                        break;
                    case "weather-forecast":
                        $tableName = "WeatherForecast";
                        break;
                    case "give-feedback":
                        $tableName = "FeedBack";
                        break;
                    case "Default":
                    $tableName = "AgriculturalAdvisoryRequests";
                        break;
}
return $tableName;


}

function ProcessAgriculAdvisory($menuOpt){
$menuName = null;
$menuOptVal= null;

 switch ($menuOpt) {
                case "1" || "2" || "3" ||"00" :
                    $menuName = "regions";
                    switch ($menuOpt){
                        case "1":
                            //$menuOptVal = "PlantingAdvice";
                            $menuOptVal = "5";
                            break;
                        case "2":
                           // $menuOptVal = "HarvestingAdvice";
                            $menuOptVal = "6";
                            break;
                        case "3":
                            //$menuOptVal = "PestsAndDiseases";
                            $menuOptVal = "7";
                            break;
                        case "00":
                            //$menuOptVal = "back";
                             $menuName = "main";
                            $menuOptVal = "";
                            break;
                          default:
                             $menuName = "invalidinput";
                             $menuOptVal = "";
                    
                    break;
                        


                    }
                    $this->Display($menuName);
                    //$dbFunctions->UpdateUssdTran($msisdn,$sessionId,$globalTable,"Level2",$menuOpt);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                    break;
               
              
            }
        return $menuName;



}

function SubmissionOpt($menuOpt,$sessionId,$msisdn) {
    $menuName = null;
    
 switch ($menuOpt) {
                case "1" :
                    $menuName = "End";

                    $msg = $this->DBQueryFunctions->Messages($_SESSION['final_message'],$msisdn);
                    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", "Confirmed");
                    break;
                    
                case "2" :
                    $menuName = "Cancel";
                    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", $menuName);
                    break;
               
                default:
                    $menuName = "invalidinput";

                    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", $menuName);
                    $menuOptVal = "";
                    break;
            }
            $this->Display($menuName);
           
    return $menuName;
}

function FoodAdvisory($menuOpt) {


 switch ($menuOpt) {
                case "1" || "2" || "3" ||  "00": 
                    $menuName = "regions";
                    switch ($menuOpt){
                         case "1":
                           // $menuOptVal = "FoodSecurityTips";
                            $menuOptVal = "1";
                            break;
                        case "2":
                           // $menuOptVal = "HungerForecast";
                            $menuOptVal = "3";
                            break;
                        case "3":
                           //$menuOptVal = "FoodStorageTips";
                            $menuOptVal = "4";
                            break;
                         case "00":
                           //$menuOptVal = "FoodStorageTips";
                            $menuName = "main";
                            $menuOptVal = "";
                            break;
                         default:
                    $menuName = "invalidinput";
                   $menuOptVal = "";
                    break;
                         

                    }


                    
                   $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                    break;
               
            }
            
return $menuName;
}

function WeatherForecast($menuOpt) {

$menuName = null;
$menuOptVal= null;
  switch ($menuOpt) {
                case "1" || "2" || "3" || "4" || "00":
                    $menuName = "regions";
                    switch ($menuOpt){

                        case "1":
                            $menuOptVal = "Daily";
                            break;
                        case "2":
                            $menuOptVal = "Dekadal";
                            break;
                        case "3":
                            $menuOptVal = "Seasonal Audio";
                            break;
                         case "4":
                            $menuOptVal = "Seasonal SMS";
                            break;
                        case "00":
                            $menuName = "regions";
                            $menuOptVal = "";
                            break;
                        default:
                            $menuName = "invalidinput";
                            $menuOptVal = "";
                   
                    break;
                        
                    }
                    
                   $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                    break;
             
            }
          


return $menuName;
}

function Feedback($menuOpt) {
    
    $menuName = null;
     switch ($menuOpt) {
                case "1":
                    $menuName = "advise-impact";
                    $menuOptVal = "ImpactOfForecast";
                    $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1", $menuOptVal);
                    break;
               
                      case "2":
                     $menuName = "indigenous-contribution";
                    $menuOptVal = "IndigenousContribution";
            $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                  
                   break;
                case "00":
                     $menuName = "main";
                    $menuOptVal = "";
            $this->Display($menuName);
                   // $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                  
               
                default:
                    $menuName = "invalidinput";
                     $menuOptVal = "";
                  $this->Display($menuName);
                    break;
            }  
                   
           return $menuName;
        }
function AdviseImpact($menuOpt) {        
         $menuName = null;
                     
         switch ($menuOpt) {
                case "1" || "2" || "3" || "4" || "0":
                    $menuName = "regions";
                    switch ($menuOpt){
                        case "1":
                           // $menuOptVal = "LakeVictoriaBasin";
                           $menuOptVal = "3";
                           $selectOpt = "Helpful and accurate";
                            break;
                        case "2":
                           // $menuOptVal = "Western";
                            $menuOptVal = "5";
                            $selectOpt = "Accurate but not helpful";
                            break;
                        case "3":
                           // $menuOptVal = "Central";
                            $menuOptVal ="4";
                            $selectOpt = "Helpful but not accurate";
                            break;
                        case "4":
                            //$menuOptVal = "Northern";
                            $menuOptVal ="7";
                            $selectOpt = "Not helpful and not accurate";
                            break;
                        case "0":
                            //$menuOptVal = "Northern";
                             $menuName = "advise-impact";
                            
                            $menuOptVal ="";
                            $selectOpt = "";
                            break;
                        default:
                    $menuName = "invalidinput";
                   $selectOpt = "";
                    break;
                       
                    }
                   $this->Display($menuName);
                     $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level2",$selectOpt);
                    break;
                
            }
         return $menuName;
        
     }
     

     function IndiginousContribution($menuOpt){
     
     
      switch ($menuOpt) {
                case "1" || "2" || "3" || "4" || "0":
                    $menuName = "regions";
                    switch ($menuOpt){
                        case "1":
                            $menuOptVal = "Thick mist,very warm nights,birds from west to east";
                            break;
                        case "2":
                            $menuOptVal = "leaves shedoff,dry winds,misty scanty rains";
                            break;
                        case  "3":
                            $menuOptVal = "Time to plant and plough land";
                            break;
                        case "4":
                            $menuOptVal = "Time to harvest,clear clouds";
                            
                             default:
                    $menuName = "invalidinput";
                      $menuOptVal = "";
                    
                    break;

                    }
                    $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level2",$menuOptVal);
                    break;
               
            }
          
      return $menuName;
     
     }
     
  
     function invaliddistrict($menuOpt){
        $menuName = "";
        $menuVar = "District";
        if($menuOpt == ""){
            $menuName = "district";
            $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, $menuName);
            
        }else{
            $regionDetails = $this->DBQueryFunctions->getDistrictDetails($menuOpt);
            if ($regionDetails == "") {
                     //header('Content-Type: application/x-www-form-urlencoded');
                     // header('Flow-Control: end');
                         
                $menuName = "invaliddistrict"; 
                // $this->Display("invaliddistrict");
                 // phone, sessionid, district, menuvariable, menuValue
                $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, "invaliddistrict", $menuOpt);
            }else{
                // Store district in sessions 
                $_SESSION['district'] = $menuOpt;
                $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $menuOpt);
                $menuName = "product";
                
            }
        }
        $this->Display($menuName);
        return $menuName;
     }
    
   function invalidinput($menuOpt){
        $menuName = "";
        if($menuOpt== "0"){
            if($_SESSION['menu_on'] == "product"){
                $menuName = "product";
            }else if($_SESSION['menu_on'] == "sector"){
                $menuName = "sector";
            }else if($_SESSION['menu_on'] == "response_format"){
                $menuName = "response_format";
            }else{
                $menuName = "product";
            }
            
            //  $this->Display($menuName);
        } else {
            $_SESSION['menu_on'] = "invalidinput"; 
            $menuName = "invalidinput";
//              $this->Display($menuName);
        }
         
        $this->Display($menuName);  
        return $menuName;
     }
       
     
   function Regions($menuOpt,$sessionid){
    // logFile("in regions bit ".$menuOpt);
     
     $menuName = "";
     //$dbFunctions = new DBQueryFunctions();
     if($menuOpt == "0"){
         
        $menuName = "main";
           $this->Display($menuName);
     }else{
         $regionDetails = $this->DBQueryFunctions->getDistrictDetails($menuOpt);
         if ($regionDetails == "") {
             //header('Content-Type: application/x-www-form-urlencoded');
             // header('Flow-Control: end');
                 
             $menuName = "invaliddistrict";
             $this->Display($menuName);
         } else {
         
         $check = $this->DBQueryFunctions->checkIfSeasonal($sessionid);
         //logFile("did you choose seasonal ".$check);
                if ($check == "Yes") {
                    $menuName = "season_lang";
                    $this->Display($menuName);
                    
                } else {
                    $_SESSION["regionparams"] = "";
                    $menuName = "Submission-opt";
                    //
                    $_SESSION["regionparams"] = $regionDetails;
                    $this->Display($menuName);
                }
     }
                        $this->DBQueryFunctions->UpdateUssdTranRegionIds($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),$regionDetails[1],$regionDetails[2],$regionDetails[3]);
                        
                        
                    
     }
     
          return $menuName;  
     }

     //gets district selected language
     function Call_sub($input,$sessionid){
        //  logFile("call_sub".$input);
         
         $menuVal="";
         //uisng session get district id
         $district_name = $this->DBQueryFunctions->getDistrictId($sessionid); 
         
         //get the languages
         $lang_array = $this->DBQueryFunctions->getLanguageValue($district_name );
         $count=1;
         $num= count($lang_array);
         for($i=1;$i<=$num;$i++){
             //if match get value and leave loop else next iteration
             if($input ==$i ){
                 $menuVal=$lang_array[$count];
                 break;
             }else{
                $count++;
                continue;
             }
             
             
         }
         $menuVal = strtoupper($menuVal);
         logFile($menuVal);
         $regionDetails = $this->DBQueryFunctions->getDistrictDetails($district_name);
         if ($regionDetails == "") {
             
             $menuName = "invaliddistrict";
             $this->Display($menuName);
         } else {
          $_SESSION["regionparams"] = "";
         $menuName = "Submission-opt";
         //
         $_SESSION["regionparams"] = $regionDetails;
         $this->Display($menuName);
         }
         $this->DBQueryFunctions->enterLanguage($sessionid,$menuVal);

         return $menuName;
        
     }

}
?>

