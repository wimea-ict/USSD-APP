<?php
ob_start();
include_once 'connectionStr.php';
include_once 'smsApi.php';
include_once 'ussdresponsesender.php';
include_once 'ussdlog.php';


class SelectAdvisory{

    public function __construct(){
        $this->connectionStr = new connectionStr;
        $this->smsApi = new smsApi;
        $this->ussdresponsesender = new ussdresponsesender;
        
    }
    
    public function SelectAdvisory($count){
	$counter=$count;
        try{
	//$counter++;
	 $confirm="1";
	 $max=5;
	 $bit=1;
      $queryStr = "Select * from  ussdtransaction  WHERE Retries < '$max' AND Level7='$confirm' AND MessageSent = '$bit' ORDER BY RecordDate DESC LIMIT 1";
			
	$result = $this->SelectQuerys($queryStr,$counter);
      
      }catch(Exception $ex){    
          }
  }

public function SelectQuerys($sql,$count){
	$counter=$count;
    
    try{
   
    $conn = $this->connectionStr->ConnectionFc();
     
     $query = $conn->query($sql);
      
   if($query->num_rows > 0)
  {
	  while($row = $query->fetch_assoc()){
		$TransId = $row['TranId'];
		$mainCategory = $row["Level0"];  
		$subCategory  = $row["Level1"];
		$language=$row["Level6"];
		$Location = $row["Location"];
		$PhoneNumber = $row["Msisdn"]; 	
       		 $SessionId = $row["SessionId"];
		$Advisory = "The ".$mainCategory.", Subcategory ".$subCategory." for ".$PhoneNumber." is Test Advisory ".$TransId;
		logFile($Advisory);

		// var_dump($Advisory);
		// exit;
	
  switch ($mainCategory){
	  case  "weather-forecast":
      $sqlStr = "";
							switch ($subCategory){
							
							case "Seasonal":
									$strType = "Seasonal";
									$sqlStr = $this->selectforecastQueryStrings($strType,$SessionId,$PhoneNumber,$language);
									break;
							case "Dekadal":
									$strType = "Dekadal";
									$sqlStr = $this->selectforecastQueryStrings($strType,$SessionId,$PhoneNumber,$language);
									break;
							case  "Daily":
									$strType = "Daily";
									$sqlStr = $this->selectforecastQueryStrings($strType,$SessionId,$PhoneNumber,$language);
									break;
							
      }
      
                    // sql script for 
                    //seasonal forecast
                    
		
  $this->getAdvisoryAndLogMessage($conn,$sqlStr,$PhoneNumber,$SessionId,$language,$counter);
       
  break;
                
   case "agriculture-and-food-security":
	  	if($language=="english"){
			$queryStr = "SELECT advisory.message as description, advisory.record_id, advisory.advice, advice.id_advice, advice.advice_name, "
			." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
			." ussdregions.name as regionname, "
 ." ussdsubregions.subregionname as subregionname FROM advisory LEFT OUTER JOIN ussdtransaction on advisory.region = ussdtransaction.regionid "
 ." AND advisory.subregionid = ussdtransaction.subregionid LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
 ." LEFT OUTER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
 ." LEFT OUTER JOIN ussdsubregions ON ussdtransaction.subregionid = ussdsubregions.subregionid WHERE " 
 ." ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
 ."  AND advisory.advice = '$subCategory' AND advisory.message IS NOT NULL group by advisory.message,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
 ." ussdtransaction.regionid ,advisory.record_id , ussdtransaction.subregionid, ussdregions.name , ussdsubregions.subregionname "
 
 ." order BY advisory.record_id DESC LIMIT 1";
	$this->getAdvisoryAndLogMessage($conn,$queryStr,$PhoneNumber,$SessionId,$language,$counter);	
	}else if($language=="luganda"){
		$queryStr = "SELECT advisory.messageLuganda as description, advisory.record_id, advisory.advice, advice.id_advice, advice.advice_name, "
			." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
			." ussdregions.name as regionname, "
 ." ussdsubregions.subregionname as subregionname FROM advisory LEFT OUTER JOIN ussdtransaction on advisory.region = ussdtransaction.regionid "
 ." AND advisory.subregionid = ussdtransaction.subregionid LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
 ." LEFT OUTER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
 ." LEFT OUTER JOIN ussdsubregions ON ussdtransaction.subregionid = ussdsubregions.subregionid WHERE " 
 ." ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
 ."  AND advisory.advice = '$subCategory' AND advisory.messageLuganda IS NOT NULL group by advisory.messageLuganda,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
 ." ussdtransaction.regionid ,advisory.record_id , ussdtransaction.subregionid, ussdregions.name , ussdsubregions.subregionname "
 
 ." order BY advisory.record_id DESC LIMIT 1";
// 		$queryStr = "SELECT advisory.messageLuganda as description, advisory.record_id, advisory.advice, advice.id_advice, advice.advice_name, "
// 		." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
// 		." ussdregions.name as regionname, "
// ." ussdsubregions.subregionname as subregionname FROM advisory LEFT OUTER JOIN ussdtransaction on advisory.regionid = ussdtransaction.regionid "
// ." AND advisory.subregionid = ussdtransaction.subregionid LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
// ." LEFT OUTER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
// ." LEFT OUTER JOIN ussdsubregions ON ussdsubregions.subregionid = ussdtransaction.subregionid WHERE " 
// ." ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
// ."  AND advisory.advice = '$subCategory' AND advisory.messageLuganda IS NOT NULL group by advisory.messageLuganda,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
// ." ussdtransaction.regionid ,advisory.record_id , ussdtransaction.subregionid, ussdregions.name , ussdsubregions.subregionname "

// ." order BY advisory.record_id DESC LIMIT 1";
logFile($queryStr);
		   $this->getAdvisoryAndLogMessage($conn,$queryStr,$PhoneNumber,$SessionId,$language,$counter);

	}       
                    
   break;
                
	 case "disaster-advisory":
	 	if($language=="english"){
			$queryStr = "SELECT advisory.message as description, advisory.advice, advice.id_advice, advice.advice_name, "
			." ussdtransaction.Msisdn as telephone, advisory.record_id,ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
			."ussdregions.name as regionname, "
 ."ussdsubregions.subregionname as subregionname FROM advisory INNER JOIN ussdtransaction on advisory.region = ussdtransaction.regionid "
 ." AND advisory.subregionid = ussdtransaction.subregionid INNER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
 ." INNER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
 ." INNER JOIN ussdsubregions ON ussdsubregions.subregionid = ussdtransaction.subregionid WHERE ussdtransaction.SessionId = '$SessionId' "
 ." AND ussdtransaction.Msisdn = '$PhoneNumber' AND advisory.advice = '$subCategory' AND advisory.message IS NOT NULL group by advisory.message,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
 ."ussdtransaction.regionid , ussdtransaction.subregionid,advisory.record_id, ussdregions.name , ussdsubregions.subregionname "
 
 ." order BY advisory.record_id DESC LIMIT 1";
	 $this->getAdvisoryAndLogMessage($conn,$queryStr,$PhoneNumber,$SessionId,$language,$counter); 
		 }else if($language=="luganda"){

			$queryStr = "SELECT advisory.messageLuganda as description, advisory.advice, advice.id_advice, advice.advice_name, "
			." ussdtransaction.Msisdn as telephone, advisory.record_id,ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
			."ussdregions.name as regionname, "
 ."ussdsubregions.subregionname as subregionname FROM advisory INNER JOIN ussdtransaction on advisory.region = ussdtransaction.regionid "
 ." AND advisory.subregionid = ussdtransaction.subregionid INNER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
 ." INNER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
 ." INNER JOIN ussdsubregions ON ussdsubregions.subregionid = ussdtransaction.subregionid WHERE ussdtransaction.SessionId = '$SessionId' "
 ." AND ussdtransaction.Msisdn = '$PhoneNumber' AND advisory.advice = '$subCategory' AND advisory.message IS NOT NULL group by advisory.message,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
 ."ussdtransaction.regionid , ussdtransaction.subregionid,advisory.record_id, ussdregions.name , ussdsubregions.subregionname "
 
 ." order BY advisory.record_id DESC LIMIT 1";

// 			$queryStr = "SELECT advisory.messageLuganda as description, advisory.advice, advice.id_advice, advice.advice_name, "
// 			." ussdtransaction.Msisdn as telephone, advisory.record_id,ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
// 			."ussdregions.name as regionname, "
//  ."ussdsubregions.subregionname as subregionname FROM advisory INNER JOIN ussdtransaction on advisory.regionid = ussdtransaction.regionid "
//  ." AND advisory.subregionid = ussdtransaction.subregionid INNER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
//  ." INNER JOIN advice ON ussdtransaction.Level1 = advice.id_advice "
//  ." INNER JOIN ussdsubregions ON ussdsubregions.subregionid = ussdtransaction.subregionid WHERE ussdtransaction.SessionId = '$SessionId' "
//  ." AND ussdtransaction.Msisdn = '$PhoneNumber' AND advisory.advice = '$subCategory' AND advisory.messageLuganda IS NOT NULL group by advisory.messageLuganda,advisory.advice, advice.id_advice, advice.advice_name, ussdtransaction.Msisdn , "
//  ."ussdtransaction.regionid , ussdtransaction.subregionid,advisory.record_id, ussdregions.name , ussdsubregions.subregionname "
 
//  ." order BY advisory.record_id DESC LIMIT 1";
			$this->getAdvisoryAndLogMessage($conn,$queryStr,$PhoneNumber,$SessionId,$language,$counter);
		 }

	break;
	default:
	     		
		$sql="UPDATE ussdtransaction SET Retries	='$counter' WHERE Msisdn = '$PhoneNumber' AND SessionId = '$SessionId'";
		$conn->query($sql);

		try{
			if($counter >= 5){
				if($language == "english"){
					logFile($counter);
					$strToDisp = "No Advisory";
					$this->sendMessage($PhoneNumber,$strToDisp);
				 logFile($strToDisp);

				}else if($language == "luganda"){
					$strToDisp = "Tetusobodde kufuna kyosabye akasera kano, gezako eddako.";						
					$this->sendMessage($PhoneNumber,$strToDisp);
					logFile($strToDisp);
					
				}
				
			}
		}catch(Exception $e){
			$msg="Currently NOt working";
			logFile($msg);
		}
	break;
            
	  }
          }
    
  }else{
	  
	 // echo "No Information found for advisory. Please try again later.";
	 //$message="Nothing to send really";
	 //$this->logToFile($message);
  }

$conn->close();
        
        }catch(Exception $ex){}
    
 }
 
 public function getAdvisoryAndLogMessage($conn,$queryString,$PhoneNumber,$SessionId,$lang,$count){
	//var_dump($PhoneNumber);
	//exit;
	$counter=$count;
	$successCode="";
	$time="";
	logFile("This is query ".$queryString);
       try{
           
					 $query = $conn->query($queryString);
					 logFile(mysqli_error($conn));
			if($query->num_rows > 0){
			while($row = $query->fetch_assoc()){

			$advisory=$row['description'];
		//	$time=$row['time'];
			$combine=$time."\n".$advisory;
//			var_dump($advisory);
//			exit;
			$message = str_replace("<br/>","\n",$combine);
		//logFile("This is advisory ".$message);
			
			$sms_api_result = $this->sendMessage($PhoneNumber, $message);
		//	var_dump($sms_api_result);
		//	exit;
		//	$this->logToFile($sms_api_result);
			$result=explode('|',$sms_api_result);
			//$this->logToFile($result[0]);
			$successCode=$result[0];
			break;
			
			}
			//$this->logToFile($successCode);
			//continue;
		
			if($successCode="1701"){
			$status=0;
			$this->updateMessageStatus($conn,$status,$PhoneNumber,$SessionId);
			}else if($successCode="1711"){
			$msg="Message Sending Failed. Maybe Wrong Phone Number";
			logFile($msg);
			} 
	//		  $this->updateMessageStatus($conn,$status=0,$PhoneNumber,$SessionId);
          }else{
//                    $sms_api_result = $this->smsApi->sendMessage($number, "No Advisory Found");
			
			$sql="UPDATE ussdtransaction SET Retries='$counter' WHERE Msisdn = '$PhoneNumber' AND SessionId = '$SessionId'";
			$conn->query($sql);

			try{
				if($counter >= 5){
					if($lang == "english"){
						$strToDisp = "No Advisory Found.";
						$this->sendMessage($PhoneNumber,$strToDisp);
						logFile($strToDisp);
		
					}else if($lang == "luganda"){
						$strToDisp = "Tetusobodde kufuna kyosabye akasera kano, gezako eddako.";						
						$this->sendMessage($PhoneNumber,$strToDisp);
						logFile($strToDisp);
						
					}
					 
				}
			}catch(Exception $e){
				//$msg="Currently NOt working";
				//$this->logToFile($msg);
			}
       
    }
           
           
} catch (Exception $ex){
	$msg="No rows found matching the criteria";
	$this->logToFile($msg);
}
       
}
public function selectforecastQueryStrings($strType,$SessionId, $PhoneNumber,$language){
    $queryStr = "";
    switch ($strType){
        
		case    "Seasonal":
			if($language=="english"){
				$queryStr = " SELECT seasonal_forecast.season as season,seasonal_forecast.sea_id, seasonal_forecast.description as description, seasonal_forecast.impact as impact, "
				." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, ussdregions.name as regionname, "
				." ussdsubregions.subregionname as subregionname FROM seasonal_forecast LEFT OUTER JOIN ussdtransaction on seasonal_forecast.region = ussdtransaction.regionid AND seasonal_forecast.subregionid = ussdtransaction.subregionid "
				." LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
				." LEFT JOIN ussdsubregions ON ussdtransaction.subregionid = ussdsubregions.subregionid" 
				." WHERE ussdtransaction.SessionId = '$SessionId' AND "
				." ussdtransaction.Msisdn = '$PhoneNumber' "
				."AND seasonal_forecast.description IS NOT NULL "
				." group by seasonal_forecast.season, seasonal_forecast.description, "
				."seasonal_forecast.impact,ussdtransaction.Msisdn,ussdtransaction.regionid, "
				."ussdtransaction.subregionid,ussdregions.name,ussdsubregions.subregionname,seasonal_forecast.sea_id "

				." order BY seasonal_forecast.sea_id DESC  LIMIT 1" ;

				
			}else if($language=="luganda"){

				$queryStr = " SELECT seasonal_forecast.season as season,seasonal_forecast.sea_id, seasonal_forecast.descriptionLuganda as description, seasonal_forecast.impactLuganda as impact, "
				." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, ussdregions.name as regionname, "
				." ussdsubregions.subregionname as subregionname FROM seasonal_forecast LEFT OUTER JOIN ussdtransaction on seasonal_forecast.region = ussdtransaction.regionid AND seasonal_forecast.subregionid = ussdtransaction.subregionid "
				." LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
				." LEFT JOIN ussdsubregions ON ussdtransaction.subregionid = ussdsubregions.subregionid" 
				." WHERE ussdtransaction.SessionId = '$SessionId' AND "
				." ussdtransaction.Msisdn = '$PhoneNumber' "
				."AND seasonal_forecast.descriptionLuganda IS NOT NULL "
				." group by seasonal_forecast.season, seasonal_forecast.descriptionLuganda, "
				."seasonal_forecast.impactLuganda,ussdtransaction.Msisdn,ussdtransaction.regionid, "
				."ussdtransaction.subregionid,ussdregions.name,ussdsubregions.subregionname,seasonal_forecast.sea_id "

				." order BY seasonal_forecast.sea_id DESC  LIMIT 1" ;
logFile($queryStr);
			// 	$queryStr = " SELECT seasonal_forecast.season as season,seasonal_forecast.sea_id,seasonal_forecast.descriptionLuganda as description, seasonal_forecast.impactLuganda as impact, "
			// 	." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, ussdregions.name as regionname, "
			// 	." ussdsubregions.subregionname as subregionname FROM seasonal_forecast LEFT OUTER JOIN ussdtransaction on seasonal_forecast.region = ussdtransaction.regionid AND seasonal_forecast.subregionid = ussdtransaction.subregionid "
			// 	." LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
			// 	." LEFT JOIN ussdsubregions ON ussdtransaction.subregionid = ussdsubregions.subregionid" 
			// 	." WHERE ussdtransaction.SessionId = '$SessionId' AND "
			// 	." ussdtransaction.Msisdn = '$PhoneNumber' "
			// 	."AND seasonal_forecast.descriptionLuganda IS NOT NULL"
			// 	." group by seasonal_forecast.season, seasonal_forecast.description, "
			// 	."seasonal_forecast.impactLuganda,ussdtransaction.Msisdn,ussdtransaction.regionid, "
			// 	."ussdtransaction.subregionid,ussdregions.name,ussdsubregions.subregionname,seasonal_forecast.sea_id "

			// 	." order BY seasonal_forecast.sea_id DESC  LIMIT 1" ;	
			// 
		}

		break;
		case    "Dekadal":
		
			if($language=="english"){
				$queryStr = " SELECT decadal.decadal_id,decadal.advisory as description, decadal.date_from AS datefrom, decadal.date_to AS dateto, decadal.issuetime as issuedate, "
				." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, ussdregions.name as regionname, "
				." ussdsubregions.subregionname as subregionname "

				." FROM "

				." decadal "
				." LEFT OUTER JOIN "
				." ussdtransaction "

				." on decadal.region = ussdtransaction.regionid AND decadal.subregionid = ussdtransaction.subregionid "
				." LEFT OUTER JOIN "
				." ussdregions on ussdtransaction.regionid = ussdregions.regionid "
				." LEFT OUTER JOIN "

				." ussdsubregions "
				." ON ussdtransaction.subregionid = ussdsubregions.subregionid "

				." WHERE ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
				."AND decadal.advisory IS NOT NULL "
				." group by " 
				." decadal.decadal_id,decadal.advisory, decadal.date_from,decadal.date_to, decadal.issuetime, "
				." ussdtransaction.Msisdn,ussdtransaction.regionid,ussdtransaction.subregionid,ussdregions.name,ussdsubregions.subregionname "

				." ORDER BY decadal.decadal_id DESC  LIMIT 1";
			
			}else if($language=="luganda"){
				$queryStr = " SELECT decadal.advisoryLuganda as description, decadal.date_from AS datefrom, decadal.date_to AS dateto, decadal.issuetime as issuedate, "
				." ussdtransaction.Msisdn as telephone,decadal.decadal_id, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, ussdregions.name as regionname, "
				." ussdsubregions.subregionname as subregionname "

				." FROM "

				." decadal "
				." LEFT OUTER JOIN "
				." ussdtransaction "

				." on decadal.region = ussdtransaction.regionid AND decadal.subregionid = ussdtransaction.subregionid "
				." LEFT OUTER JOIN "
				." ussdregions on ussdtransaction.regionid = ussdregions.regionid "
				." LEFT OUTER JOIN "

				." ussdsubregions "
				." ON ussdsubregions.subregionid = ussdtransaction.subregionid "

				." WHERE ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
				."AND decadal.advisoryLuganda IS NOT NULL "
				." group by " 
				." decadal.decadal_id,decadal.advisory,decadal.decadal_id, decadal.date_from,decadal.date_to, decadal.issuetime "
				." ,ussdtransaction.Msisdn,ussdtransaction.regionid,ussdtransaction.subregionid,ussdregions.name,ussdsubregions.subregionname "

				." ORDER BY decadal.decadal_id DESC  LIMIT 1";	
			}

			break;
		case    "Daily":
			$time_now = date('H:m:s');
			  log($time_now);
		if($language=="english"){
			$queryStr = " SELECT daily_forecast.weather as description,daily_forecast.id,daily_forecast.time as time, daily_forecast.date, daily_forecast.time, "
			." ussdtransaction.Msisdn as telephone, ussdtransaction.districtid as districtid, "
			."dailyforecast_region.regionname as regionname FROM "

			." ussddistricts LEFT OUTER JOIN ussdtransaction on ussddistricts.districtid = ussdtransaction.districtid"
			." LEFT OUTER JOIN daily_forecast on ussddistricts.DRid = daily_forecast.region "
			." LEFT OUTER JOIN dailyforecast_region ON dailyforecast_region.DRid = ussddistricts.DRid "

			." WHERE ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
			."AND daily_forecast.weather IS NOT NULL "
			."group by daily_forecast.id,daily_forecast.weather, daily_forecast.date, daily_forecast.time "
			." ,ussdtransaction.Msisdn,ussdtransaction.districtid,dailyforecast_region.regionname "

			." ORDER BY  daily_forecast.id  DESC  LIMIT 1 ";	

			logFile("This is daily query ".$queryStr);
		}else if($language=="luganda"){
			$queryStr = " SELECT daily_forecast.weatherLuganda as descriptionLuganda,daily_forecast.id,daily_forecast.time as time, daily_forecast.advisory, daily_forecast.date, daily_forecast.time, "
			." ussdtransaction.Msisdn as telephone, ussdtransaction.regionid as regionid, ussdtransaction.subregionid as subRegion, "
			."ussdregions.name as regionname, ussdsubregions.subregionname as subregionname FROM "

			." daily_forecast LEFT OUTER JOIN ussdtransaction on daily_forecast.regionid = ussdtransaction.regionid AND daily_forecast.subregionid = ussdtransaction.subregionid "
			." LEFT OUTER JOIN ussdregions on ussdtransaction.regionid = ussdregions.regionid "
			." LEFT OUTER JOIN ussdsubregions ON ussdsubregions.subregionid = ussdtransaction.subregionid "

			." WHERE ussdtransaction.SessionId = '$SessionId' AND ussdtransaction.Msisdn = '$PhoneNumber' "
			." AND daily_forecast.weatherLuganda IS NOT NULL"
			."group by daily_forecast.id,daily_forecast.weather, daily_forecast.advisory, daily_forecast.date, daily_forecast.time "
			." ,ussdtransaction.Msisdn,ussdtransaction.regionid,ussdtransaction.subregionid,ussdregions.name,ussdsubregions.subregionname "

			."ORDER BY  daily_forecast.id  DESC  LIMIT 1 ";
		}
						
	break;
  }
			  
	return $queryStr;
    
}


	function sendMessage($phoneNumber,$message){

		$msg=str_replace("<br>","\n",$message);
		$messg=str_replace("?","\'",$msg);

		$resp = "";
		try{
		$textmessage = urlencode($messg);	
		$url = 'http://simplysms.com/getapi.php';
		$urlfinal = $url.'?'.'email'.'='.'rc4wids@yahoo.com'.'&'.'password'.'='.'VBsd9A2'.'&'.'sender'.'='.'8777'.'&'.'message'.'='.$textmessage.'&'.'recipients'.'='.$phoneNumber;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlfinal);
		curl_setopt_array($ch,array(
		CURLOPT_RETURNTRANSFER =>1,   
		//CURLOPT_URL =>$urlfinal,
		CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

		$resp = curl_exec($ch);

		curl_close($ch);
			
		}catch(Exception $e){}
		return $resp;
		//echo  $phoneNumber;
	}
	public function logToFile($msg){
		$myfile = fopen("newotherfile.txt", "w") or die("Unable to open file!");
		$txt = $msg."\n";
		fwrite($myfile, $txt);
		fclose($myfile);
	}
	
	public function updateMessageStatus($conn,$statusCode,$PhoneNumber,$SessionId){
		$msg=$statusCode." ".$PhoneNumber." ".$SessionId;
	//	$this->logToFile($msg);
		$sql="UPDATE ussdtransaction SET MessageSent='$statusCode' WHERE Msisdn = '$PhoneNumber' AND SessionId = '$SessionId'";

		//$sql="UPDATE ussdtransaction SET MessageSent =$statusCode WHERE TranId IS NOT NULL";
		$query = $conn->query($sql);
		if(!$query){
			$msg="Failed Totally";
		//	$this->logToFile($msg);
		}else{
			$msg="Will gget you working today";
		//	$this->logToFile($msg);
		}
				
	}

}


?>
