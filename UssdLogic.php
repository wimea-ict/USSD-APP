<?php 

ob_start();
include_once 'MoUssdReceiver.php';
include_once 'weatherParams.php';
include_once 'DBQueryFunctions.php';
include_once 'UssdAppMain.php';
include_once 'smsApi.php';
// include_once 'voicecall.php';//voice
 ?> 
<?php
$receiver = new MoUssdReceiver(); // Create the Receiver object
$weatherparams = new weatherParams();
$dbFunctions = new DBQueryFunctions();
$smsApiFunctions = new smsApi();
// $call = new voicecall();

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
    $menu_table = $this->DBQueryFunctions->Get_Menu();
    if($menuName != null){
        if($menuName == "End" || $menuName == "Cancel" || $menuName == "voicecall"){
            $action ="end";
        }else{
           $action ="request";
        }
        if($menuName == "sector"){
            //Query menu options by the menuname in the database
            
            $responseMsg = $_SESSION['sector_lable']."-";
            $checkSector = $this->DBQueryFunctions->getSectors($_SESSION['forecast_for']);
            if($checkSector == 1){
                $ct = 0;
                $responseMsg .=$this->DBQueryFunctions->loadUssdMenu("sects", $menu_table);
                $responseMsg .="-";
                foreach ($_SESSION['sectors'] as $sects) { $ct++;
                    $responseMsg .=$ct.". ".$sects."-";
                }
            }
            $responseMsg .= "9. ";
            $responseMsg .= $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table);
            $responseMsg .= "-0. ";
            $responseMsg .= $this->DBQueryFunctions->loadUssdMenu("back", $menu_table);
                
        }else if($menuName == "product"){
            $available = array();
            //handles processing string beyond products
            $real_available = array();   
            $forecasts = array($this->DBQueryFunctions->loadUssdMenu('daily', $menu_table),$this->DBQueryFunctions->loadUssdMenu('dekadal', $menu_table),$this->DBQueryFunctions->loadUssdMenu('Seasonal', $menu_table));
            $real = array('Daily Forecast','Dekadal Forecast','Seasonal Forecast');

            $num = 0; $ct = 0;
            $responseMsg = $this->DBQueryFunctions->loadUssdMenu('prod', $menu_table);
            foreach ($forecasts as $key) {
                $records = $this->DBQueryFunctions->loaded_data($real[$ct]." no advisory");
                if(strlen($records)>3){ $num++;
                    $available[] = $key;
                    $real_available[] = $real[$ct];
                    $responseMsg .= "-".$num.". ".$key;
                }
                $ct++;
            }
            $_SESSION['real_forecast_availbale'] = $real_available;
            $_SESSION['forecast_availbale'] = $available;

///////////////////////////// Amoko/////////////////
            if($num < 1){
                $menuName = "no_data";
                //Query menu options by the menuname in the database
                $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table);
            }else{
                $responseMsg .= "-0. ".$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);
            }



            
        }else if($menuName == "language"){
            $responseMsg = "Welcome to WIDS-Please select language-1. English";
                $ct = 1;
                $ty = $this->DBQueryFunctions->Languages_got();
                foreach ($_SESSION['languages'] as $langs) { $ct++;
                    $responseMsg .="-".$ct.". ".$langs;
                }
        }else{
            //Query menu options by the menuname in the database
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

function ProcessDistrict($input,$sessionId,$msisdn){
    $menuName = "";
    $menuVar = "District";
    if($input == ""){
        $menuName = "district";
        $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, $menuName);
        
    }else{
        if($input == '0'){
            $menuName = "language";
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
    if($input == 0){
        $menuName = 'district';
        $_SESSION['forecast_for'] = "Back";
        $_SESSION['menu_on'] = "qwwwertyuiytre";

    }else if((($input-1) <= count($_SESSION['forecast_availbale'])) && ($input-1) >= 0){
        $ct = 0;
        foreach ($_SESSION['real_forecast_availbale'] as $s) {
            if(($input-1) == $ct){
                $_SESSION['forecast_for'] = $s;
                $_SESSION['sector_lable'] = $_SESSION['forecast_availbale'][$ct];
                $with_ad = "true";
                break;
            }
            $ct++;
        }
    }else{

        $_SESSION['forecast_for'] = "invaliddistrict";
        $_SESSION['menu_on'] = "product";

        // $this->Display("invalidinput");
        $menuName = "invalidinput";
    }

    // For valiidity checks
    
    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $_SESSION['forecast_for']);
    $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']." no advisory");
    $_SESSION['final_message'] = $responseMsg;
    if(strlen($_SESSION['menu_on']) > 3){
    }else{
        $menuName = $this->availability($msisdn,$sessionId,$responseMsg, $with_ad);
    }
    
    $this->Display($menuName);
    return $menuName;
}

public function availability($msisdn,$sessionId,$entry_data, $with_ad){
    $menuName = "";
    if(strlen($entry_data)>5){
        if($with_ad == "true"){
            $menuName = "sector";
        }else{
            if($_SESSION['forecast_for'] == "Seasonal Forecast"){
                $menuName = "response_format";
            }else{
                $menuName = "Submission-opt";
            }
        }
    }else{
        $menuName = "no_data";
        $menuVar = "Product";
        $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $menuName);
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

            if(substr($_SESSION['forecast_for'], -11)=="no advisory"){
                $menuName = "End";
$mess = $_SESSION['final_message']."\nFor more, please dial *255*85";
                $msg = $this->DBQueryFunctions->Messages($mess,$msisdn);
            }else{
                $menuName = "Submission-opt";
            }


            $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']);
            $_SESSION['final_message'] = $responseMsg;
            break;
        case '2':
        	$menuName = "voicecall";
        	$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, 'Voice');
            $voicemail = $this->DBQueryFunctions->triggerCall($msisdn);
        	break;
        case '0':
            $menuName = "sector";


            $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, 'Back');
       // $voicemail = $this->DBQueryFunctions->triggerCall($msisdn);
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
                $menuName = "Submission-opt";
                if($_SESSION['forecast_for'] == "Seasonal Forecast"){
                    $audio = $this->DBQueryFunctions->checkAudio();
                    if($audio == 1){
                        $menuName = "response_format";
                    }  
                }
                $_SESSION['final_message'] = $responseMsg;
            }
            $ct++;
        }
        
    }else if($input == '0'){
        $menuName = 'product';
    }else if($input == '9'){
        $menuName = "Submission-opt";
        $menuVar = "sector";
        if($_SESSION['forecast_for'] == "Daily Forecast"){
            $_SESSION['forecast_for'] = "Daily Forecast no advisory";
        }else if($_SESSION['forecast_for'] == "Dekadal Forecast"){
            $_SESSION['forecast_for'] = "Dekadal Forecast no advisory";
        }else{
            $_SESSION['forecast_for'] = "Seasonal Forecast no advisory";
            $audio = $this->DBQueryFunctions->checkAudio();
            if($audio == 1){
                $menuName = "response_format";
            }  
        }
        $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $_SESSION['forecast_for']);
        $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']);
        $_SESSION['final_message'] = $responseMsg;

    }else{
        $_SESSION['menu_on'] = "sector";
        $menuName = "sector";
    }
    $this->Display($menuName);
    return $menuName;

}


function SubmissionOpt($menuOpt,$sessionId,$msisdn) {
    $menuName = null;
    
 switch ($menuOpt) {
                case "1" :
                    $menuName = "End";
$mess = $_SESSION['final_message']."\nFor more, please dial *255*85";
                    $msg = $this->DBQueryFunctions->Messages($mess,$msisdn);
                    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", "Confirmed");
                    break;
                    
                case "2" :
                    if($_SESSION['forecast_for'] == "Seasonal Forecast"){
                        // $menuName = "response_format";
                        $menuName = "sector"; 
                        // check availability of the audio clip

                        $audio = $this->DBQueryFunctions->checkAudio();
                        if($audio == 1){
                            $menuName = "response_format";
                        }
                       
                    }else{
                        $menuName = "sector";
                    }
                    $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", "Back");
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




}
?>

