<?php 
ob_start();
include_once 'ussdlog.php';
include_once 'connectionStr.php';
echo "eor";

// $conn = $this->connectionStr->ConnectionFc();
// $w = mysql_error($conn);
// var_dump($w);
// echo "dsds".$conn;
$dbname = 'dissemination';
$dbuser = 'root';
$dbpass = 'karanzi';
//$dbpass = '';
$dbhost = 'localhost';
$conn = new mysqli($dbhost, $dbuser,$dbpass,$dbname);
$sql = "SELECT Level8 FROM ussdtransaction WHERE Msisdn = 256701586693 ORDER BY TranId DESC LIMIT 1 ";
            $query = $conn->query($sql);
            if($query->num_rows > 0){
              $field= $query->fetch_assoc();
              $language = $field['Level8'];
              echo "this is ".$language;
            }
            else{
                echo "this is ";
              //logFile("No such record for ".$callerNumber);
            }


// class Dd{

//     //  function Dd(){
//     //      echo "eor";
        
//     //     echo "after";
//     // } 
//     public function __construct()
//      {
        
//          $this->connectionStr = new connectionStr;
//          //$this->db();
         
//      }

//     function db(){
//         echo "this is ";
//     $conn = $this->connectionStr->ConnectionFc();
//     if($conn =""){
//         echo " not ";
//     }else
//     echo " postv";
    
//     echo "jump"; 
//             $sql = "SELECT Level8 FROM ussdtransaction WHERE Msisdn = 0701586693 ORDER BY TranId DESC LIMIT 1 ";
//             $query = $conn->query($sql);
//             if($query->num_rows > 0){
//               $field= $query->fetch_assoc();
//               $language = $field['Level8'];
//               echo "this is ".$language;
//             }
//             else{
//                 echo "this is ";
//               //logFile("No such record for ".$callerNumber);
//             }
//     }
    
// }


        ?>