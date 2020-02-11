<?php  

    // require_once('AfricasTalkingGateway.php');
    // Save this code in voicemail.php. Configure the callback URL for your phone number
    // to point to the location of this script on the web
    // e.g http://www.myawesomesite.com/voicemail.php

    // First read in a couple of POST variables passed in with the request

    // This is a unique ID generated for this call

    

    $dbname = 'wids';
    $dbuser = 'root';
    $dbpass = 'Rc4@dm1n';
    //$dbpass = '';
    $dbhost = 'localhost';

    $conn = new mysqli($dbhost, $dbuser,$dbpass,$dbname);


    $lang_qry = "SELECT * FROM ussdmenulanguage";
    $query = $conn->query($lang_qry);
    $languages = array();

    while ($row = $query->fetch_assoc()){
        $languages[] = $row['language'];
    }
    $lang  = "";

    $fd = 0;
    $callerNumber = $_POST['callerNumber'];
    $sql = "SELECT menuvalue FROM ussdtransaction_new WHERE phone = $callerNumber ORDER BY id DESC";
    $query = $conn->query($sql);
    if($query->num_rows > 0){
        while ($row = $query->fetch_assoc()){
            foreach ($languages as $k) {
                if($row['menuvalue'] == $k){
                    $lang = $k;
                    $fd = 1;
                }
            }
            if($fd == 1){
                break;
            }
        }
    }

    $sql2 = "SELECT location FROM voice WHERE language = '$lang' ORDER BY id DESC LIMIT 1";
    $querying = $conn->query($sql2);
    $url_default = "";
    while ($row = $querying->fetch_assoc()){
        $url_default = $row['location'];
    }

    
    $sessionId = $_POST['sessionId'];

    // Check to see whether this call is active
    $isActive  = $_POST['isActive'];
    // $url_default = "Dissemination/uploads/LUGANDA_MAM19.mp3";

    if ($isActive == 1)  {
        // Get the location of previously recorded voicemail and play back the file. Make 
        // sure its a valid web address that starts with http
        
        $response  = '<?xml version="1.0" encoding="UTF-8"?>';
        $response .= '<Response>';
        $response .= '<Play url="http://wids.mak.ac.ug/'.$url_default.'"/>';//wids.mak.ac.ug/Dissemination/uploads/lugandaMAM19
        $response .= '</Response>';
        header('Content-type: text/plain');
        echo $response;
    }else{
        $duration = $_POST['durationInSeconds'];
        $currency = $_POST['currencyCode'];
        $amount = $_POST['amount'];
    }
    

    

?>