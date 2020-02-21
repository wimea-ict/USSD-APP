<?php  

  // require_once('AfricasTalkingGateway.php');
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
      $response .= '<Play url="http://wids.mak.ac.ug/ussdapp/audio/clip.mp3"/>';//wids.mak.ac.ug/Dissemination/uploads/lugandaMAM19
      $response .= '</Response>';
      // Print the response onto the page so that our gateway can read it
      header('Content-type: apllication/xml');
      echo $response;
  }else{
    $duration = $_POST['durationInSeconds'];
    $currency = $_POST['currencyCode'];
    $amount = $_POST['amount'];
  }

?>