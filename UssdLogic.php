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

		if($menuName == "End" || $menuName == "Cancel" || $menuName == "voicecall" || $menuName == "feedbackrep" || $menuName == "complete_subscription" || $menuName == "complete_unsubscription"){
			$action ="end";
		}else{
			$action ="request";
		}
		if($menuName == "complete_subscription"){
			$this->DBQueryFunctions->Messages("You have successfully subscribed for the ".$_SESSION['subscription_product_name']." to be recieved ". $_SESSION['subscription_period_name'].". Thank you\nFor more, Visit http://wids.mak.ac.ug/wids or ",$_SESSION['phone_no']);
		}

		if($menuName == "complete_unsubscription"){
			$this->DBQueryFunctions->Messages("You have unsubscribed from all weather updates\nFor more, Visit http://wids.mak.ac.ug/wids or ",$_SESSION['phone_no']);
		}else if($menuName == "sector"){

			$responseMsg = $_SESSION['sector_lable']."-";
			$checkSector = $this->DBQueryFunctions->getSectors($_SESSION['forecast_for']);
			if($checkSector == 1){
				$ct = 0;
				$responseMsg .=$this->DBQueryFunctions->loadUssdMenu("sects", $menu_table);
				foreach ($_SESSION['sectors'] as $sects) { $ct++;
					$responseMsg .="-".$ct.". ".$sects;
				}
			}
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
			$num++;
			$responseMsg.="-".$num.". ".$this->DBQueryFunctions->loadUssdMenu('feedback', $menu_table);
			$num++;
			$responseMsg.="-".$num.". ".$this->DBQueryFunctions->loadUssdMenu('subscribe', $menu_table);
			$_SESSION['real_forecast_availbale'] = $real_available;
			$_SESSION['forecast_availbale'] = $available;

			if($num < 1){
				$menuName = "no_data";
                //Query menu options by the menuname in the database
				$responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table);
			}else{
				$responseMsg .= "-0. ".$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);
			}

		}else if($menuName == "landing_site"){
			$responseMsg = $this->DBQueryFunctions->loadUssdMenu("landing_site", $menu_table);

			$num = 0;
			for ($i=0; $i < sizeof($_SESSION['siteDetails']); $i++) { 
				$responseMsg .= "-".($i+1).". ".$_SESSION['siteDetails'][$i];
				$num++;
			}
			$responseMsg.="-".($num+1).". ".$this->DBQueryFunctions->loadUssdMenu('feedback', $menu_table);
			$responseMsg .= "-0. ".$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);

		}else if($menuName == "language"){
			$responseMsg = "Welcome to WIDS-Please select language-1. English";
			$ct = 1;
			$ty = $this->DBQueryFunctions->Languages_got();
			foreach ($_SESSION['languages'] as $langs) { $ct++;
				$responseMsg .="-".$ct.". ".$langs;
			}
		}else if($menuName == "Submission-opt"){
			if($_SESSION['forecast_for'] == "landing_site"){
				$responseMsg = strtoupper($_SESSION['d_landing_site'])." Landing Site, ".$_SESSION["period_selected"]."-";
			}else{
				$responseMsg = strtoupper($_SESSION['district']).", ".$_SESSION["forecast_selected"]."-";
			}

			$responseMsg .= "1. ".$this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table)."-2. ".$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);
		}else if($menuName == "district"){
			$responseMsg = "";

			if(strlen($_SESSION['invaliddistrict']) > 5){
				$responseMsg .= $this->DBQueryFunctions->loadUssdMenu("invaliddistrict", $menu_table);
				$_SESSION['invaliddistrict'] = "";
			}
			$responseMsg .= $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table);
			$responseMsg .="-0. ".$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);

		}else if($menuName == "repeat"){
			$responseMsg = "";
			$responseMsg .= $this->DBQueryFunctions->loadUssdMenu("repeat", $menu_table).' '.strtolower($this->DBQueryFunctions->loadUssdMenu($_SESSION['visited_data']['value'], $menu_table)).' '.$this->DBQueryFunctions->loadUssdMenu("for", $menu_table).' '.$_SESSION['visited_data']['district'].' '.$this->DBQueryFunctions->loadUssdMenu("in", $menu_table).' '.$_SESSION['visited_data']['language'];
			$responseMsg .= '-1. '.$this->DBQueryFunctions->loadUssdMenu("yes", $menu_table).'-2. '.$this->DBQueryFunctions->loadUssdMenu("no", $menu_table).'-0. '.$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);
            // $menuName = 'repeat';
		}else if($menuName == "subscription"){
			$responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table); 
			if($this->DBQueryFunctions->checkSubscriber($_SESSION['phone_no'])){
				$responseMsg .= "-3. ".$this->DBQueryFunctions->loadUssdMenu("unsubscribe", $menu_table); 
			}
			$responseMsg .= "-0. ".$this->DBQueryFunctions->loadUssdMenu("back", $menu_table);

		}else{
            //Query menu options by the menuname in the database
			$responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table); 


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
}


}  


function ProcessLanguage($input,$sessionId,$msisdn){
	$_SESSION['phone_no'] = $msisdn;
	$delta = "";

	if($input == 1){
		$_SESSION['language'] = "English";
		$_SESSION['lang_id'] = 1;
		$menuName = "district";
		$this->DBQueryFunctions->getUserLastSession($msisdn); 
		if($_SESSION['visited']){
			$menuName = "repeat"; 
		}
		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, "Language", $menuName );

	}
	else if((($input-1) <= count($_SESSION['languages'])) && ($input-1) > 0){
		$ct = 0;
		foreach ($_SESSION['languages'] as $s) {
			if(($input-2) == $ct){
				$_SESSION['language'] = $s;
				$_SESSION['lang_id'] = $_SESSION['ids'][$ct];
			}
			$ct++;
		}
		$this->DBQueryFunctions->getUserLastSession($msisdn);
		$menuName = "district"; 
		if($_SESSION['visited']){
			$menuName = "repeat"; 
		}
	}else{
        // This will be for the invalid input
		$_SESSION['language'] = "";
		$menuName = "language";
	}

	if(strlen($_SESSION['language'])> 2){

		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], NULL, NULL, NULL, NULL, NULL, NULL);

	}

	$this->Display($menuName);
	return $menuName;
}

function ProcessLandingSite($input,$sessionId,$msisdn){
	$menuName = "";
	$menuVar = "Landing Site";

	if($input == ""){
		$menuName = "landing_site";
		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, $menuName);

		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $menuName, NULL, NULL, NULL, NULL, NULL);

	}else if($input == "0"){
		$menuName = 'district';

	}else if($input == (count($_SESSION['siteDetails'])+1)){
		$menuName = 'feedbackdisp';
		$_SESSION['forecast_for']="Feedback";
		$_SESSION['menu_on'] = "landing_site";

	}else if((($input-1) <= count($_SESSION['siteDetails'])) && ($input-1) >= 0){
		$ct = 0;
		foreach ($_SESSION['siteTimes'] as $s) {
			if(($input-1) == $ct){
				$_SESSION['forecast_for'] = "landing_site";
				$_SESSION["period_selected"] = $_SESSION["siteDetails"][$ct];
				$_SESSION['final_message'] = $this->DBQueryFunctions->getLandingSite($_SESSION["siteTimes"][$ct]);
				$menuName = 'Submission-opt';

				$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['d_landing_site'], "Landing Site Period", $_SESSION["period_selected"]);

				$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['d_landing_site'], NULL, NULL, NULL, NULL, NULL);
                // $mess ="WIDS Weather forecast\n".$_SESSION['final_message']."For more, please dial *255*85# or Visit http://wids.mak.ac.ug/wids\n";
                // $this->DBQueryFunctions->Messages($_SESSION['final_message']."\nDial *255*85#",$msisdn);
				break;
			}
			$ct++;
		}
	}else{
		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, 'invalid input');


		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], 'invalid input', NULL, NULL, NULL, NULL, NULL);

		$menuName = "landing_site";
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
		}
		else{
			$regionDetails = $this->DBQueryFunctions->getDistrictDetails($input);
			$siteDetails = $this->DBQueryFunctions->getSiteDetails($input,1);

			if (($regionDetails == "") && ($siteDetails == "")) { 
				$_SESSION['invaliddistrict'] = "invaliddistrict";  
				$menuName = "district";
				$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, NULL, $menuVar, "invaliddistrict");

				$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], 'invaliddistrict', NULL, NULL, NULL, NULL, NULL);
			}else{
				$both = array();
				if(strlen($siteDetails)>5){
                    // Store district in sessions
					$menuVar = "Landing Site";
					$_SESSION['landing_site'] = $input;
					$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $input, $menuVar, ucwords($input));

					$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], ucwords($input), NULL, NULL, NULL, NULL, NULL);

					$_SESSION['site_name'] = $input;

					$menuName = "landing_site"; 
					$both[] = 1;
				}
				if(sizeof($regionDetails)>2){
                    // Store district in sessions
					$_SESSION['district'] = $input;
					$_SESSION['district_id'] = $regionDetails[1];

					$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $input, $menuVar, ucwords($input));

					$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], ucwords($input), NULL, NULL, NULL, NULL, NULL);


					$menuName = "product"; 
					$both[] = 2;
				}


                // Incase the name applies to both districts and landing sites

                // if(sizeof($both)>1){
                //     $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $input, "District or Landing Site", ucwords($input));
                // }

			}
		}

	}
	$this->Display($menuName);
	return $menuName;

}

// Don't make any changes here, this one is now fine
// Changes are to be made in the DBQueryFunctions.php file which holds the queries for the data
function ProcessFeedback($input,$sessionId,$msisdn){
	$menuVar = "Feedback";
	$menu_table = $this->DBQueryFunctions->Get_Menu();
	if($input == "0"){
		$menuName = $_SESSION['menu_on'];
		$this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar,"back");

	}else{
		$menuName = "feedbackrep";

		$this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $input);
		$this->DBQueryFunctions->saveFeedback($msisdn,$_SESSION['district'], $input);       
		$this->DBQueryFunctions->Messages($this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table)."\nFor more, Dial *255*85# or Visit http://wids.mak.ac.ug/wids",$msisdn);
	}


	$this->Display($menuName);
}


function ProcessRepeated($input,$sessionId,$msisdn){
	$menuVar = "CallBack Forecast";
	$menu_table = $this->DBQueryFunctions->Get_Menu();

	$menuName = "repeat";
    // $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar,"repeated");

	if($input == "0"){
		$menuName = 'language';
		$this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar,"back");
	}else if($input == "1"){
        // $menuName = 'language';

		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'], NULL, NULL, NULL, NULL);

		$responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']." no advisory");
		$_SESSION['final_message'] = $responseMsg;
		if(strlen($_SESSION['menu_on']) > 3){
		}else{
			$menuName = $this->availability($msisdn,$sessionId,$responseMsg, $with_ad);
		}
		$this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar,$_SESSION['forecast_for']);

	}else if($input == "2"){
		$menuName = 'district';
		$_SESSION['visited'] = false;
		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, 'No');
        // $Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'], NULL, NULL, NULL, NULL);

	}


        // $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $input);
        // $this->DBQueryFunctions->saveFeedback($msisdn,$_SESSION['district'], $input);       
        // $this->DBQueryFunctions->Messages($this->DBQueryFunctions->loadUssdMenu($menuName, $menu_table)."\nFor more, Dial *255*85# or Visit http://wids.mak.ac.ug/wids",$msisdn);

	$this->Display($menuName);
	return $menuName;
}

function ProcessSubscriptionProduct($input,$sessionId,$msisdn){
	if($input == "0"){
		$menuName = 'product';
		$_SESSION['forecast_for'] = "Back";
		$_SESSION['menu_on'] = "qwwwertyuiytre";
	}else if(($input == "1") || ($input == "2")){
		$menuName = 'complete_subscription';
		$products = array("Daily Forecast", "Seasonal Forecast");
		$_SESSION['subscription_product'] = $input;
		$_SESSION['subscription_product_name'] = $products[($input-1)];

		$this->DBQueryFunctions->subscribeUser($sessionId, $msisdn, $_SESSION['subscription_product'], $_SESSION['district_id'], $_SESSION['lang_id']);

        // $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "subscription", $_SESSION['forecast_for']);

	}else if($input == "3"){
		$menuName = 'complete_unsubscription';

		$this->DBQueryFunctions->unsubscribeUser($msisdn);

	}else{
		$menuName = 'subscription';
	}

	$this->Display($menuName);
	return $menuName;
}

function ProcessSubscriptionPeriod($input,$sessionId,$msisdn){
	if($input == "0"){
		$menuName = 'subscription';
		$_SESSION['forecast_for'] = "Back";
		$_SESSION['menu_on'] = "qwwwertyuiytre";
	}else if(($input == "1") || ($input == "2") || ($input == "3")){
		$menuName = 'complete_subscription';
		$periods = array("Daily", "Weekly", "Monthly");
		$_SESSION['subscription_period'] = $input;
		$_SESSION['subscription_period_name'] = $periods[($input-1)];
	}else{
		$menuName = 'period';
	}

    // Log the subscription
	$this->DBQueryFunctions->subscribeUser($sessionId, $msisdn, $_SESSION['subscription_product'], $_SESSION['district_id'], $_SESSION['lang_id']);
	$this->Display($menuName);
	return $menuName;
}

function ProcessProduct($input,$sessionId,$msisdn){
	$menuName = "";
	$menuVar = "Product";
	$with_ad = "";
	$_SESSION['menu_on'] = "";
    // $conn = $this->connectionStr->ConnectionFc();
	if($input == "0"){
		$menuName = 'district';
		$_SESSION['forecast_for'] = "Back";
		$_SESSION['menu_on'] = "qwwwertyuiytre";

	}else if($input == (count($_SESSION['forecast_availbale'])+1)){
		$menuName = 'feedbackdisp';
		$_SESSION['forecast_for']="Feedback";
		$_SESSION['menu_on'] = "product";

	}else if($input == (count($_SESSION['forecast_availbale'])+2)){
		$menuName = 'subscription';
		$_SESSION['forecast_for']="Subscription";
		$_SESSION['menu_on'] = "subscription";

	}else if((($input-1) <= count($_SESSION['forecast_availbale'])) && ($input-1) >= 0){
		$ct = 0;
		foreach ($_SESSION['real_forecast_availbale'] as $s) {
			if(($input-1) == $ct){
				$_SESSION['forecast_for'] = $s;
				$_SESSION["forecast_selected"] = $_SESSION["forecast_availbale"][$ct];
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
		$menuName = "product";
	}

    // For valiidity checks

	$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $_SESSION['forecast_for']);

	$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'], NULL, NULL, NULL, NULL);

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
	if(strlen($entry_data)>9){
		$menuName = "sector";
            // Checks for sectors available for that forecast thats correspond to the selected district
		$counted = $this->DBQueryFunctions->available_sectors($_SESSION['forecast_for']);
		if($counted < 1){
			$_SESSION['forecast_forty'] = 'true';
			$menuName = "Submission-opt";
			$audio = $this->DBQueryFunctions->checkAudio();
			if($audio == 1){
				$menuName = "response_format";
			}

		}
	}else{
		$menuName = "no_data";
		$menuVar = "Product";
		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $menuName);

		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], 'No data', NULL, NULL, NULL, NULL);


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

		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'], NULL, NULL, $menuName,  NULL);

		if(substr($_SESSION['forecast_for'], -11)=="no advisory"){
			$menuName = "End";
               // $rr = "#";
			$mess ="WIDS Weather forecast\n".$_SESSION['final_message']."\nFor more, Visit http://wids.mak.ac.ug/wids";
			$msg = $this->DBQueryFunctions->Messages($mess,$msisdn);
		}else{
			$menuName = "Submission-opt";
		}
		break;
		case '2':
		$menuName = "voicecall";
		$LogCall = $this->DBQueryFunctions->LogCall_Requests($msisdn,$sessionId);
		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, 'Voice');

		$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'], NULL,NULL, 'Voice', NULL, NULL);

		$voicemail = $this->DBQueryFunctions->triggerCall($msisdn);
		break;
		case '0':
		$menuName = "sector";


		$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, 'Back');

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
				$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Sector", $s);

				$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'],NULL, $s, NULL, NULL);

				$responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for'], $_SESSION['sectors_id'][$ct]);
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
	}
    // Without advisory option
    // else if($input == '9'){
    //     $menuName = "Submission-opt";
    //     $menuVar = "sector";
    //     if($_SESSION['forecast_for'] == "Daily Forecast"){
    //         $_SESSION['forecast_for'] = "Daily Forecast no advisory";
    //     }else if($_SESSION['forecast_for'] == "Dekadal Forecast"){
    //         $_SESSION['forecast_for'] = "Dekadal Forecast no advisory";
    //     }else{
    //         $_SESSION['forecast_for'] = "Seasonal Forecast no advisory";
    //         $audio = $this->DBQueryFunctions->checkAudio();
    //         if($audio == 1){
    //             $menuName = "response_format";
    //         }  
    //     }
    //     $Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $_SESSION['forecast_for']);
    //     $responseMsg = $this->DBQueryFunctions->loaded_data($_SESSION['forecast_for']);
    //     $_SESSION['final_message'] = $responseMsg;

    // }

	else{
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
		if($_SESSION['forecast_for'] == "landing_site") $mess =$_SESSION['final_message']."\nFor more,  Visit http://wids.mak.ac.ug/wids";
	else $mess ="WIDS Weather forecast\n". $_SESSION['final_message']."\nFor more, Visit http://wids.mak.ac.ug/wids";
	$msg = $this->DBQueryFunctions->Messages($mess,$msisdn);
	$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", "Confirmed");

	$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'],NULL,NULL, NULL, 'Confirmed');

	break;

	case "2" :
	if($_SESSION['forecast_for'] == "Seasonal Forecast"){
                        // $menuName = "response_format";
		$menuName = "sector"; 
                        // check availability of the audio clip

                        // $audio = $this->DBQueryFunctions->checkAudio();
                        // if($audio == 1){
        // $menuName = "response_format";
                        // }

	}else if($_SESSION['forecast_for'] == "landing_site"){
		$menuName = "landing_site";
	}else{
		$menuName = "product";
	}
	$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], "Confirmation", "Back");

	$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], $_SESSION['forecast_for'],NULL,NULL, NULL, 'Back');
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


function invaliddistrict($menuOpt, $sessionId, $msisdn ){
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

			$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], 'invalid district', NULL,NULL,NULL, NULL, 'Confirmed');
		}else{
                // Store district in sessions 
			$_SESSION['district'] = $menuOpt;
			$Log = $this->DBQueryFunctions->LogUpdates($msisdn,$sessionId, $_SESSION['district'], $menuVar, $menuOpt);

			$Log2  = $this->DBQueryFunctions->LogSessions($msisdn, $sessionId, $_SESSION['language'], $_SESSION['district'], NULL,NULL,NULL, NULL, 'Confirmed');
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

