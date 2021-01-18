<?php 
ob_start();
include_once 'ussdlog.php';
include_once 'connectionStr.php';
echo "eor";


$dbname = '';
$dbuser = '';
$dbpass = '';
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
}


?>