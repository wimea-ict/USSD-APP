<?php

include_once 'MoUssdReceiver.php';
include_once 'ussdlog.php';
include_once 'weatherParams.php';
include_once 'DBQueryFunctions.php';
include_once 'UssdLogic.php';
include_once 'smsApi.php';
ini_set('error_log', 'ussd-app-error.log');


$receiver = new MoUssdReceiver(); // Create the Receiver object
$weatherparams = new weatherParams();
$dbFunctions = new DBQueryFunctions();
$logic = new UssdLogic();
$date = new DateTime();
$smsApiFunctions = new smsApi();
 
$receiverSessionId = $receiver->getSessionId();//.$date->getTimestamp();
session_id($receiverSessionId); //Use received session id to create a unique session
session_start();


$message = $receiver->getInput(); // get the message content
$sessionId = $receiver->getSessionId(); // get the session ID;
$msisdn = $receiver->getMSISDN(); // get the phone number
//$serviceCode = $receiver->getMSC(); // get the service code
 $menuName = null;
 $_SESSION['global-Table'] = "UssdTransaction";

//logFile("[ content=$message, sessionId=$sessionId, phonenumber=$msisdn, servicecode=$serviceCode]");

//logic here
if($receiver->getInput() !=null){

	//logFile("main app ".$receiver->getInput()."errror");	
	if (!(isset($_SESSION['menu-Opt']))) { //Send the main menu....if menu not set

   
            $_SESSION['menu-Opt'] = "language";      
 
 	}
 }

 //$menuName = "main";
if ( $receiver->getInput() != "") {
    
	 $menuName = null;
	 
    switch ($_SESSION['menu-Opt']) {
       
      case "season_lang":
      
      $_SESSION['menu-Opt'] = $logic->Call_sub($receiver->getInput(), $sessionId);
      
      break;
		 
      case    "language":
      
            $_SESSION['menu-Opt'] = $logic->ProcessLanguage($receiver->getInput(), $sessionId, $msisdn);
            break;

      case "district":
            $_SESSION['menu-Opt'] = $logic->ProcessDistrict($receiver->getInput(), $sessionId, $msisdn);
            break;
            // Follow this thread to the dekadal function in the switch statement
      case "product":
         $_SESSION['menu-Opt'] = $logic->ProcessProduct($receiver->getInput(), $sessionId, $msisdn);
        break;

      case "response_format":
        $_SESSION['menu-Opt'] = $logic->ProcessResponse($receiver->getInput(), $sessionId, $msisdn);
        break;
      case "no_data":
        $_SESSION['menu-Opt'] = $logic->NoData($receiver->getInput(), $sessionId, $msisdn);
        break;
      case "sector":
        $_SESSION['menu-Opt'] = $logic->ProcessSector($receiver->getInput(), $sessionId, $msisdn);
        break;
      case "invaliddistrict":
          $_SESSION['menu-Opt'] =   $logic->invaliddistrict($receiver->getInput(), $sessionId, $msisdn);  
               break;
      case "Submission-opt":
         $_SESSION['menu-Opt'] = $logic->SubmissionOpt($receiver->getInput(),$sessionId,$msisdn);
            break;    
      case "invalidinput":
          $_SESSION['menu-Opt'] =  $logic->invalidinput($receiver->getInput());
            break;
      case "feedbackdisp":
            $_SESSION['menu-Opt'] = $logic->ProcessFeedback($receiver->getInput(), $sessionId, $msisdn);
            break;
            
      }

}else{
	//trying to check ussd availability
	echo "<h2>Welcome to WIDS</h2>";
$action = "end";
$strToDisp = "Error Occured";
$this->smsApi->ussdResponseSender($strToDisp,$action);
//session_destroy();

}

?>
