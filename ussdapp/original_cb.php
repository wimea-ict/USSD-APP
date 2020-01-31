<?php
//receive AT Posts
//require_once('dbConnector.php');

$recordingUrl = $_POST['sessionId'];
$isActive  = $_POST['isActive'];
$direction = $_POST['direction'];
$callerNumber = $_POST['callerNumber'];
$destinationNumber = $_POST['destinationNumber'];
$dtmfDigits  = $_POST['dtmfDigits'];
$durationInSeconds  = $_POST['durationInSeconds'];
$currencyCode  = $_POST['currencyCode'];
$amount  = $_POST['amount'];

if ($isActive == 1 && !isset($dtmfDigits) ) {
    $text = "Testing this 1 yes or 2 No.";
    
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
      $response .= '<GetDigits  timeout="30" finishOnKey="#">';
      $response .= '<Say>'.$text.'</Say>';
      $response .= '</GetDigits>';
      $response .= '</Response>';
       
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
}
elseif ($dtmfDigits == 1) {
    $text = "what is your sex 1 Male or 2 Female";

    $response  = '<?xml version="1.0" encoding="UTF-8"?>';
    $response .= '<Response>';
    $response .= '<GetDigits timeout="5" >';
    $response .= '<Say>'.$text.'</Say>';
    $response .= '</GetDigits>';
    $response .= '</Response>';

    // Print the response onto the page so that our gateway can read it
    header('Content-type: text/plain');
    echo $response;
}
elseif ($dtmfDigits == 2) {
    $text = "what is your Town 1 Kal or 2 jinja";

    $response  = '<?xml version="1.0" encoding="UTF-8"?>';
    $response .= '<Response>';
    $response .= '<GetDigits timeout="5" >';
    $response .= '<Say>'.$text.'</Say>';
    $response .= '</GetDigits>';
    $response .= '</Response>';

    // Print the response onto the page so that our gateway can read it
    header('Content-type: text/plain');
    echo $response;

} else {
  // You can then store this information in the database for your records
  $durationInSeconds  = $_POST['durationInSeconds'];
  $currencyCode  = $_POST['currencyCode'];
  $amount  = $_POST['amount'];
}