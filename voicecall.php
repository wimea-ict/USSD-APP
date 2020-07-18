<?php  

    $dbname = '';
    $dbuser = '';
    $dbpass = '';
    //$dbpass = '';
    $dbhost = 'localhost';



    $conn = new mysqli($dbhost, $dbuser,$dbpass,$dbname);

    $callerNumber = $_POST['callerNumber'];
    $voice_req = "SELECT * FROM voice_requests WHERE phone = $callerNumber ORDER BY id DESC LIMIT 1";
    $query = $conn->query($voice_req);
    // $languages = array();
    $lang = "";
    while ($row = $query->fetch_assoc()){
        $lang = $row['language_id'];
    }

    $season = "unknown";
      if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
      else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
      else $season = 'SOND';

    $name = $lang."_".$season."_".date('Y').".mp3";

    
    $sessionId = $_POST['sessionId'];

    // Check to see whether this call is active
    $isActive  = $_POST['isActive'];
    // $url_default = "Dissemination/uploads/LUGANDA_MAM19.mp3";

    if ($isActive == 1)  {
        // Get the location of previously recorded voicemail and play back the file. Make 
        // sure its a valid web address that starts with http
        
        $response  = '<?xml version="1.0" encoding="UTF-8"?>';
        $response .= '<Response>';
        $response .= '<Play url="http://wids.mak.ac.ug/wids/assets/audio/'.$name.'"/>';//wids.mak.ac.ug/Dissemination/uploads/lugandaMAM19
        $response .= '</Response>';
        header('Content-type: text/plain');
        echo $response;
    }else{
        $duration = $_POST['durationInSeconds'];
        $currency = $_POST['currencyCode'];
        $amount = $_POST['amount'];
    }
    

    

?>