<?php //FILE: sms_api.php 

class smsApi{
   public function __construct(){
//$message = $this->sendMessage('test message2','256716007696');
//echo $message;
}
function sendMessage($message,$phoneNumber){ 

$resp = "";
try{


$ch = curl_init();
curl_setopt_array($ch,array(
CURLOPT_RETURNTRANSFER =>1,   
 CURLOPT_URL =>'http://smsproviderurl.com/getapi.php?email=useremail&password=password&sender=sendernumber&message=$message&recipients=$phoneNumber',
CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

$resp = curl_exec($ch);

curl_close($ch);
	
}catch(Exception $e){}
return $resp;

}

function ussdResponseSender($responseStringVal,$actionVal){ 
try{
    
 $ussdRequest = json_decode(@file_get_contents('php://input'));  
$ussdResponse = new stdclass;

$ussdResponse->responseString =$responseStringVal;
$ussdResponse->action = $actionVal;

header('Content-type: application/json; charset=utf-8');
echo json_encode($ussdResponse);


    
} catch (Exception $e){
    
    
    
}
}

}


