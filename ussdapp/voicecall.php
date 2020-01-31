<?php
require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
class voicecall{

public function makeCall($number){

    logFile("call");

            // Set your app credentials
        $username = "personalization";
        $apiKey   = "bfc0a4fef4dcbd28c2010e042b701fc96aa5e3efca0a3f49abd0f735584653b2";

        // Initialize the SDK
        $AT       = new AfricasTalking($username, $apiKey);

        // Get the voice service
        $voice    = $AT->voice();

        // Set your Africa's Talking phone number in international format
        $from     = "+256312319888";

        // Set the numbers you want to call to in a comma-separated list
        $to       = "+".$number;

        try {
                // Make the call
                $results = $voice->call([
                    'from' => $from,
                    'to'   => $to
                ]);

                //print_r($results); 
                return $results;
        } 
            catch (Exception $e) {
                echo "Error: ".$e->getMessage();
        } 
        }

        
    
}//end class
?>