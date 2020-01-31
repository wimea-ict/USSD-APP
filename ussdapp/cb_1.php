<?php
//receive AT Posts
//require_once('dbConnector.php');
include_once 'ussdlog.php';

ini_set('error_log', 'ussd-app-error.log');


$recordingUrl = $_POST['sessionId'];
$isActive  = $_POST['isActive'];
$direction = $_POST['direction'];
$callerNumber = $_POST['callerNumber'];
$destinationNumber = $_POST['destinationNumber'];
$durationInSeconds  = $_POST['durationInSeconds'];
$currencyCode  = $_POST['currencyCode'];
$amount  = $_POST['amount'];

$language = "";
$field="";

if ($isActive == 1) {
       //make db connection and get language to return
    try {
        $conn = $this->connectionStr->ConnectionFc();
        $sql = "SELECT Level8 FROM ussdtransaction WHERE Msisdn = $callerNumber ORDER BY TranId DESC LIMIT 1 ";
        $query = $conn->query($sql);
        if($query->num_rows > 0){
          $field= $query->fetch_assoc();
          $language = $field['Level8'];
        }
        else{
          logFile("No such record for ".$callerNumber);
        }
          
        
    }catch(Exception $e){

    }
    
    $response  = '<?xml version="1.0" encoding="UTF-8"?>';
    $response .= '<Response>';
    $response .= '<Say>Please listen to our awesome record</Say>';
    $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/CHOPE_MAM19.mp3"/>';
    $response .= '</Response>';
    // Print the response onto the page so that our gateway can read it
    header('Content-type: apllication/xml');
    echo $response;
    
}else {
  // You can then store this information in the database for your records
  $durationInSeconds  = $_POST['durationInSeconds'];
  $currencyCode  = $_POST['currencyCode'];
  $amount  = $_POST['amount'];
  echo ' call back';
}