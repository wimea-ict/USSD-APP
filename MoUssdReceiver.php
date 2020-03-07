<?php


class MoUssdReceiver{

    //private $sourceAddress; // Define required parameters to receive response
//    private $message;
//    private $requestId;
//    private $applicationId;
//    private $encoding;
//    private $version;
//    private $sessionId;
//    private $ussdOperation;
//    private $vlrAddress;

    private $MSISDN;
    //private $password;
    private $userid;
    private $INPUT;
   // private $MSC;
    private $SESSIONID;
    private $SHORTCODE;
    private $TYPE;
 


    /*
        decode the json data and get them to an array
        Get data from Json objects
        check the validity of the response
    **/

    public function __construct(){
        $this->MSISDN = $_GET['msisdn'];
       // $this->password = $_GET['password'];
        $this->SHORTCODE = $_GET['ussdServiceCode'];
        $this->INPUT = $_GET['ussdRequestString'];
       // $this->MSC = $_GET['MSC'];
        $this->SESSIONID =$_GET['transactionId'];
        $this->TYPE = "0";//$_GET['TYPE'];


   }

    /*
        Define getters to return receive data
    **/
    
    public function getMSISDN(){
        return $this->MSISDN;
    }
    public function getSessionId(){

        return $this->SESSIONID;
    }
    public function  getShortCode(){


        return $this->SHORTCODE;
    }

    public function getInput(){
        return $this->INPUT;
    }
    public function getType(){

        return $this->TYPE;
    }

}

?>
