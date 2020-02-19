<?php
	require_once('AfricasTalkingGateway.php');
	
 	$username = "personalization";
    $apiKey= "bfc0a4fef4dcbd28c2010e042b701fc96aa5e3efca0a3f49abd0f735584653b2";
    // $username = "sandbox";
    // $apiKey = "935be59cf17c2a67cbd4c55402531c3b914f46cc4e2f817caae810e497907fef";
    // Set your Africa's Talking phone number in international format
    $from     = "+256312319888";
    // $from     = "+256705531898";
    
    $to = "+256786304982";

    $gateway = new AfricasTalkingGateway($username, $apiKey);
    try{
    	$gateway->call($from, $to);
    	echo "Calls have been inititaited";
    }catch( AfricasTalkingGatewayException $e){
    	// echo "Encountered an error while making the call";
        echo $e;
    }

?>