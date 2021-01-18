<?php
//receive AT Posts
//require_once('dbConnector.php');


$direction = $_POST['direction'];
$callerNumber = $_POST['callerNumber'];
$destinationNumber = $_POST['destinationNumber'];
$durationInSeconds  = $_POST['durationInSeconds'];
$currencyCode  = $_POST['currencyCode'];
$amount  = $_POST['amount'];
$language = "";


$recordingUrl = $_POST['sessionId'];
$isActive  = $_POST['isActive'];
if ($isActive == 1) {
    // $text = "Please select language.";
    
     // if ($language == "English") {
    
      // Compose the response
         $response  = '<?xml version="1.0" encoding="UTF-8"?>';
         $response .= '<Response>';
     
         $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
         //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
     
         $response .= '</Response>';
       
         // Print the response onto the page so that our gateway can read it
         header('Content-type: text/plain');
         echo $response
}else {
  // You can then store this information in the database for your records
  $durationInSeconds  = $_POST['durationInSeconds'];
  $currencyCode  = $_POST['currencyCode'];
  $amount  = $_POST['amount'];
  echo ' call back';
  //echo ' call back'
}
?>
