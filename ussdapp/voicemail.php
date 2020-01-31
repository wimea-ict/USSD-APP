<?php

include_once 'connectionStr.php';

function __construct()
{
    
    $this->connectionStr = new connectionStr;
    
}

function db_details($number,$district){

    $conn = $this->connectionStr->ConnectionFc();
   
    $sql = "SELECT * FROM ussdtransaction WHERE Msisdn = $number && districtid=$district";

    $query = $conn->query($sql);
}

// Save this code in voicemail.php. Configure the callback URL for your phone number
// to point to the location of this script on the web
// e.g http://www.myawesomesite.com/voicemail.php

// First read in a couple of POST variables passed in with the request

// This is a unique ID generated for this call
$sessionId = $_POST['sessionId'];

// Check to see whether this call is active
$isActive  = $_POST['isActive'];

if ($isActive == 1)  {
    // Get the location of previously recorded voicemail and play back the file. Make 
    // sure its a valid web address that starts with http
    $response  = '<?xml version="1.0" encoding="UTF-8"?>';
    $response .= '<Response>';
    $response .= '<Say>Please listen to our awesome record</Say>';
    $response .= '<Play url="https://api.africastalking.com/test/voice"/>';//wids.mak.ac.ug/Dissemination/uploads/lugandaMAM19
    $response .= '</Response>';
    // Print the response onto the page so that our gateway can read it
    header('Content-type: apllication/xml');
    echo $response;
} else {
    // Read in call details (duration, cost). This flag is set once the call is completed.
    // Note that the gateway does not expect a response in thie case

    $duration     = $_POST['durationInSeconds'];
    $currencyCode = $_POST['currencyCode'];
    $amount       = $_POST['amount'];

    // You can then store this information in the database for your records
}