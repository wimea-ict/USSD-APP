<?php 
ob_start();
include_once 'connectionStr.php';
include_once 'smsApi.php';
include_once 'ussdresponsesender.php';
include_once 'ussdlog.php';
require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;

class DBQueryFunctions
{

	public function subscribeUser($session_id, $phone, $forecast, $district, $language_id){
		try{
			$conn = $this->connectionStr->ConnectionFc();  
			$queryProc = "SELECT * FROM `ussd_subscriptions` WHERE phone = '".$phone."'";
			$query = $conn->query($queryProc);
			if ($query->num_rows > 0 ) {
				$queryProc = "UPDATE `ussd_subscriptions` SET `session_id`='".$session_id."',`forecast`=".$forecast.",`district`='".$district."',`language_id`=".$language_id." WHERE `phone`='".$phone."'";
			}else{

				$queryProc = "INSERT INTO `ussd_subscriptions`(`session_id`, `phone`, `forecast`, `district`, `language_id`) VALUES ('".$session_id."', '".$phone."', ".$forecast.", ".$district.", ".$language_id.")";
			}
			$conn->query($queryProc);
		}catch(Exception $ex){ }

	}

	public function getUserLastSession($phone){
		try{
			$menu_table = $this->Get_Menu();
			$conn = $this->connectionStr->ConnectionFc();  
			$lange = $_SESSION['language'];
			$_SESSION['visited'] = false;
			$queryProc = "SELECT * FROM `ussd_transactions_2021` WHERE phone = '".$phone."' AND language = '".$lange."' ORDER BY id DESC";
			$query = $conn->query($queryProc);
			if ($query->num_rows > 0 ) {

				while ($row = $query->fetch_assoc()) {
					if(strlen($row['product'])> 5){
						$condition = false;
						$value = '';
						if($row['product'] == "Daily Forecast"){
							$condition = true;
							$value = "daily";
						}else if($row == "Seasonal Forecast"){
							$condition = true;
							$value = "seasonal";
						}
						if($condition){
							$_SESSION['district'] = $row['district'];
							$_SESSION['forecast_for'] = $row['product'];
							$_SESSION['visited'] = true;

							$_SESSION["forecast_selected"] = $this->loadUssdMenu($value, $menu_table);

              // $this->DBQueryFunctions->loadUssdMenu($_SESSION['visited_data']['value']


							$_SESSION['visited_data'] = $row;
							$_SESSION['visited_data']['value'] = $value;
						}

						break;
					}
				}
			}
		}catch(Exception $ex){ }

	}

	public function unsubscribeUser($phone){
		try{
			$conn = $this->connectionStr->ConnectionFc();  
			$queryProc = "DELETE FROM `ussd_subscriptions` WHERE phone = '".$phone."'";
			$conn->query($queryProc);
		}catch(Exception $ex){ }

	}

	public function checkSubscriber($phone){
		try{
			$conn = $this->connectionStr->ConnectionFc();  
			$queryProc = "SELECT * FROM `ussd_subscriptions` WHERE phone = '".$phone."'";
			$query = $conn->query($queryProc);
			if ($query->num_rows > 0 ) {
				return true;
			}else{
				return false;
			}
		}catch(Exception $ex){ }

	}


	public function __construct()
	{
		$this->smsApi = new smsApi;
		$this->connectionStr = new connectionStr;
		$this->ussdresponsesender = new ussdresponsesender;
		date_default_timezone_set('Africa/Nairobi');


		$time_slots = array(4,1,2,3);

	}

	public function get_seasom(){

		$season = "unknown";
		if((date('m') == 1) || (date('m') == 2) ) $season = 'MAM';
		else  if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
		else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
		else $season = 'SOND';

		return $season;
	}

	public function LogUpdates($msisdn, $sessionID, $district, $menuvariable, $menuvalue)
	{

		if($district != NULL){
			$sql= "INSERT INTO `ussdtransaction_new`(`phone`, `sessionId`,`districtId`,`menuvariable`,`menuvalue`) VALUES ('$msisdn', '$sessionID','$district','$menuvariable','$menuvalue')";
			$result = $this->Insertion_UpdateQuerysMainSession($sql);
             // echo $result;
		}else{
			$sql= "INSERT INTO `ussdtransaction_new`(`phone`, `sessionId`,`menuvariable`,`menuvalue`) VALUES ('$msisdn', '$sessionID','$menuvariable','$menuvalue')";
			$result = $this->Insertion_UpdateQuerysMainSession($sql);

		}


		return $result;
	}

//Added new logging structure (Since 2021)
	public function LogSessions($msisdn, $sessionID,$language, $district, $product,$period_selected, $advisory, $msg_type, $confirmation)
	{


		try{
			$conn = $this->connectionStr->ConnectionFc();  
			$queryProc = "SELECT * FROM `ussd_transactions_2021` WHERE phone = '".$msisdn."' AND sessionId = '".$sessionID."' ";
			$query = $conn->query($queryProc);

			if ($query->num_rows > 0 ) {
				
				$queryProc = "UPDATE `ussd_transactions_2021` SET `language`='".$language."',`district` = '".$district."',`product`='".$product."',`period_selected`='".$period_selected."',`advisory`='".$advisory."',`msg_type`='".$msg_type."',`confirmation`='".$confirmation."' WHERE `phone`='".$msisdn."' AND `sessionId`='".$sessionID."'";
			}else{

				$queryProc = "INSERT INTO `ussd_transactions_2021`(`phone`, `sessionId`, `language`, `district`, `product`,`period_selected`, `advisory`, `msg_type`, `confirmation`) VALUES ('$msisdn', '$sessionID','$language','$district','$product','$period_selected','$advisory','$msg_type','$confirmation')";
			}
			$result = $conn->query($queryProc);
		}catch(Exception $ex){ }

      return $result;
	}

	public function saveFeedback($msisdn, $district, $feedback)
	{
		$sql= "INSERT INTO `feedback`(`phone`, `district`, `feedback`) VALUES ('$msisdn','$district','$feedback')";
		$result = $this->Insertion_UpdateQuerysMainSession($sql);

	}
	public function checkData($id){
		$exists = 0;
		try{
			$conn = $this->connectionStr->ConnectionFc();
			$today = date('Y-m-d');

			$ses= $this->get_seasom();
			$queryProc = "SELECT * FROM division LEFT OUTER JOIN region on division.main_region = region.id LEFT OUTER JOIN area_seasonal_forecast on area_seasonal_forecast.region_id =  division.main_region LEFT OUTER JOIN sub_region on area_seasonal_forecast.subregion_id = sub_region.id LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE area_seasonal_forecast.language_id='$id' AND  season_months.abbreviation = '$ses' LIMIT 1";

			$rest = $this->loaded_data("Seasonal Forecast no advisory");

			$query = $conn->query($queryProc);
			if (($query->num_rows > 0 ) || (strlen($rest)>10)) {
				return 1;
			}else{
				return 0;
			}

		}catch(Exception $ex){ }

	}


	public function saveSubscription($msisdn){
		$exists = 0;
		try{
			$conn = $this->connectionStr->ConnectionFc();
			$today = date('Y-m-d');

			$ses= $this->get_seasom();


			$insert = "INSERT INTO ussd_subscriptions VALUES ()";


			$queryProc = "SELECT * FROM division LEFT OUTER JOIN region on division.main_region = region.id LEFT OUTER JOIN area_seasonal_forecast on area_seasonal_forecast.region_id =  division.main_region LEFT OUTER JOIN sub_region on area_seasonal_forecast.subregion_id = sub_region.id LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE area_seasonal_forecast.language_id='$id' AND  season_months.abbreviation = '$ses' LIMIT 1";

			$rest = $this->loaded_data("Seasonal Forecast no advisory");

			$query = $conn->query($queryProc);
			if (($query->num_rows > 0 ) || (strlen($rest)>10)) {
				return 1;
			}else{
				return 0;
			}

		}catch(Exception $ex){ }

	}

	public function getSiteDetails($site, $checker = null){
		$siteDetails = "";
		$_SESSION['siteDetails'] = array();
		$_SESSION['siteTimes'] = array();
		try {
			$conn = $this->connectionStr->ConnectionFc();


			$lange = $_SESSION['language'];
			$qProc = "SELECT id, daily FROM ussdmenulanguage WHERE language = '$lange'";
			$identity = "";

			$query = $conn->query($qProc);
			while ($row = $query->fetch_assoc()) {
				$identity = $row['id'];  
			}


			$districtName = strtoupper($site);
			$sited = strtoupper($site);

			$checked = 1;
			for ($i=0; $i < 3; $i++) { 
				$dated = date('Y-m-d',strtotime(' +'.$i.' day'));
                // for ($m=1; $m < 5; $m++) { 
				$sql_site = "SELECT victoria_area_data.id as id, victoria_districts.name AS district_nme, victoria_periods.name as periods, time_date, time_frame FROM victoria_area_data
				LEFT OUTER JOIN victoria_area ON victoria_area.id = victoria_area_data.vic_area
				LEFT OUTER JOIN victoria_districts ON victoria_area.id = victoria_districts.zone_id
				LEFT OUTER JOIN landing_site ON landing_site.district_id = victoria_districts.id
				LEFT OUTER JOIN victoria_data ON victoria_data.id = victoria_area_data.forecast_id
				LEFT OUTER JOIN victoria_periods ON victoria_periods.id = victoria_area_data.time_frame  
				WHERE victoria_area_data.time_date = '$dated' AND landing_site.site_name = '$sited' AND victoria_data.language_id = '$identity' ORDER BY victoria_area_data.id ASC";

				$querying = $conn->query($sql_site);

				if ($querying->num_rows > 0) {
					$_SESSION['d_landing_site'] = $sited;

					if($checked <= 4){
						while ($rows = $querying->fetch_assoc()) {
							$datedd = ($rows['time_date']==date('Y-m-d'))? "Today ":"Tomorrow ";
							$_SESSION['siteDetails'][] = $datedd.$rows['periods'];
							$_SESSION['siteTimes'][] = $rows['id'];
						}
					}

					$checked++;
				}

                // }

			}

			if($checker !=null){
				$sql_site = "SELECT victoria_area_data.id as id, victoria_districts.name AS district_nme, victoria_periods.name as periods, time_date, time_frame FROM victoria_area_data
				LEFT OUTER JOIN victoria_area ON victoria_area.id = victoria_area_data.vic_area
				LEFT OUTER JOIN victoria_districts ON victoria_area.id = victoria_districts.zone_id
				LEFT OUTER JOIN landing_site ON landing_site.district_id = victoria_districts.id
				LEFT OUTER JOIN victoria_data ON victoria_data.id = victoria_area_data.forecast_id
				LEFT OUTER JOIN victoria_periods ON victoria_periods.id = victoria_area_data.time_frame  
				WHERE landing_site.site_name = '$sited' AND victoria_data.language_id = '$identity' ORDER BY victoria_area_data.id ASC";

				$querying = $conn->query($sql_site);

				if ($querying->num_rows > 0) {
					$siteDetails = "Data is available"; 
				}
			}
		}catch (Exception $e) {}
		return $siteDetails;
	}


	public function getDistrictDetails($district)
	{
		$regionDetails = "";
		try {
			$conn = $this->connectionStr->ConnectionFc();
			$districtName = strtoupper($district);

			$sql = "SELECT division.id as districtid, division.division_name as districtname, 
			region.region_name AS region, 
			region.id as regionid, division.region_id, 
			division.sub_region_id from division 
			INNER JOIN region ON division.region_id = region.id "
			. "WHERE division.division_name = '$districtName' ";
			$query = $conn->query($sql);

			if ($query->num_rows > 0) {
				while ($row = $query->fetch_assoc()) {

					$districtid = $row["districtid"];
					$regionid = $row["regionid"];
					$subregionid = $row["subregionid"];
					$regionDetails = array($regionDetails,$districtid,$regionid,$subregionid);

                     // echo $regionDetails;
				}
			} else {
				$regionDetails = "";
			}



		} catch (Exception $e) {}
		return $regionDetails;
	}




	public function getLandingSite($id)
	{
		$records = "";
		try {
			$conn = $this->connectionStr->ConnectionFc();
			$lange = $_SESSION['language'];
			$qProc = "SELECT id, daily FROM ussdmenulanguage WHERE language = '$lange'";
			$identity = "";

			$query = $conn->query($qProc);
			while ($row = $query->fetch_assoc()) {
				$identity = $row['id'];  
			}


			$sql = "SELECT victoria_data.advice as advice, victoria_area.name as area_name, highlights, wind_strength.name as wind_strength, wind_direction.name as wind_direction, wave_height.name as wave_height, weather_cond.name as cat_name, rainfall_dist.name as rainfall_dist, visibility.name as visibility, harzard
			FROM victoria_area_data
			LEFT OUTER JOIN wind_strength ON wind_strength.id = victoria_area_data.wind_strength
			LEFT OUTER JOIN wind_direction ON wind_direction.id = victoria_area_data.wind_direction
			LEFT OUTER JOIN wave_height ON wave_height.id = victoria_area_data.wave_height
			LEFT OUTER JOIN weather_cond ON weather_cond.id = victoria_area_data.weather
			LEFT OUTER JOIN rainfall_dist ON rainfall_dist.id = victoria_area_data.rainfall_dist
			LEFT OUTER JOIN visibility ON visibility.id= victoria_area_data.visibility
			LEFT OUTER JOIN victoria_area ON victoria_area.id = victoria_area_data.vic_area
			LEFT OUTER JOIN victoria_data ON victoria_data.id = victoria_area_data.forecast_id
			WHERE victoria_area_data.id = '$id' AND victoria_data.language_id = '$identity'";

			$query = $conn->query($sql);

			if ($query->num_rows > 0) {
				while ($row = $query->fetch_assoc()) { 
					switch ($row['harzard']) {
						case '#0F0':
						$harzard = "No severe weather is expected";
						break;

						case '#FFA500':
						$harzard = "Potentially dangerous weather is expected. Be prepared.";
						break;

						case '#F00':
						$harzard = "Dangerous and potentially life-threatening weather conditions are expected. Take immediate action to ensure your safety";
						break;

						default:
                            # code...
						break;
					}
                    // $menuVal = $_SESSION['d_landing_site']." Landing Site, ".$_SESSION["period_selected"]."\nHighlight: ".$row['highlights'];

					$menuVal = $_SESSION['d_landing_site']." Landing Site, ".$_SESSION["period_selected"];
					$menuVal .= "\nHarzard: ".$harzard."\nWind Strengh: ".$row['wind_strength']."\nWind Direction: ".$row['wind_direction']."\nWave Height: ".$row['wave_height']." waves\nWeather: ".$row['cat_name']."\nRainfall Distribution: ".$row['rainfall_dist']." places\nVisibility: ".$row['visibility']."\n";
				}
			}
		} catch (Exception $e) {
		}
		return $menuVal;
	}


	public function loadUssdMenu($menuname, $menu_table = NULL)
	{
		$menuVal = "";
		$queryProc = "";
		try {
			$conn = $this->connectionStr->ConnectionFc();

			$queryProc = "SELECT * FROM $menu_table WHERE menuname = '$menuname'";
			$query = $conn->query($queryProc);
			if ($query->num_rows > 0) {
				while ($row = $query->fetch_assoc()) {
					$menuVal = $row["menudescription"];
				}
			}



		} catch (Exception $e) { }

		return $menuVal;
	}

	public function Get_Menu(){

		$queryProc = "";
		$menu_table = "";
		try {
			$lang = $_SESSION['language'];
			$conn = $this->connectionStr->ConnectionFc();

			$lang_qry = "SELECT language_text_table FROM ussdmenulanguage WHERE language = '$lang'";
			$qry = $conn->query($lang_qry);
			if ($qry->num_rows > 0) {
				while ($row = $qry->fetch_assoc()) {
					$menu_table = $row['language_text_table'];
				}   
			}

		}catch(Exception $ex){}
		return $menu_table;
	}
	public function Languages_got(){
		$lang_qry = "SELECT * FROM ussdmenulanguage";
		try {
			$conn = $this->connectionStr->ConnectionFc();
			$lang_data = array();
			$ids = array();
			$qry = $conn->query($lang_qry);
			if ($qry->num_rows > 0) {
				$ct = 0;
				while ($row = $qry->fetch_assoc()) { $ct++;
                    // Must be changed to enable luganda and other language   from ">=" to "="
					if($ct == 1){}
						else{
							$info = $this->checkData($row['id']);
							if($info == 1){
								$lang_data[] = $row['language'];
								$ids[] = $row['id'];
							}

						}
					}
					$_SESSION['languages'] = $lang_data;
					$_SESSION['ids'] = $ids;
				}

			} catch (Exception $e) {
			}

			return "fd";
		}
		function Messages($message,$phoneNumber){ 

			$resp = "";
			try{
				$textmessage = urlencode($message."\nDial *255*85#");
				$ch = curl_init();
				curl_setopt_array($ch,array(
					CURLOPT_RETURNTRANSFER =>1, 
					CURLOPT_URL =>'',
					CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

				$resp = curl_exec($ch);

				curl_close($ch);

			}catch(Exception $e){}
			return $resp;

		}


    // Loads the sectors
		public function available_sectors($data_for){
			$sect_count=0;
			try {
				$conn = $this->connectionStr->ConnectionFc();
				$today = date('Y-m-d');

				$yesterday = date('Y-m-d', strtotime( $today . ' -1 day' ) );
				$the_date = $today;

// Now with time
            // strtotime(date('Y-m-d g:ia')



				$divisions = strtoupper($_SESSION['district']);
				$lange = $_SESSION['language'];
				$qProc = "SELECT id, daily FROM ussdmenulanguage WHERE language = '$lange'";
				$identity = "";

				$query = $conn->query($qProc);
				while ($row = $query->fetch_assoc()) {
					$identity = $row['id'];  
				}
				$ses= $this->get_seasom();


				if($data_for == "Daily Forecast"){
					if(strtotime(date('Y-m-d g:ia')) < strtotime(date('Y-m-d').' 6:00pm') ){
						$the_date = $yesterday;
					}
					$daily_and_advisory="SELECT daily_advisory.message_summary as summary,weather_category.cat_name as weather_desc, minor_sector.minor_name as sector, daily_forecast.time as currently FROM daily_advisory LEFT OUTER JOIN minor_sector on minor_sector.id=daily_advisory.sector LEFT OUTER JOIN daily_forecast on daily_forecast.id=daily_advisory.forecast_id LEFT OUTER JOIN daily_forecast_data on daily_forecast_data.forecast_id = daily_advisory.forecast_id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN division on division.region_id = daily_forecast_data.region_id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$the_date' AND daily_forecast.language_id = '$identity'";


					$query = $conn->query($daily_and_advisory);


					if ($query->num_rows > 0) { $sect_count++;
						while ($row = $query->fetch_assoc()) {
						}
					}
				}else if($data_for == "Seasonal Forecast"){


					$seasonal_and_advisory = "SELECT advisory.sector as ident, advisory.region_id as sub_reg, minor_sector.minor_name as sector FROM advisory 
					LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id 
					LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id 
					LEFT OUTER JOIN seasonal_forecast  on  advisory.forecast_id = seasonal_forecast.id 
					LEFT OUTER JOIN area_seasonal_forecast  on  advisory.forecast_id = area_seasonal_forecast.forecast_id 
					LEFT OUTER JOIN division  on  area_seasonal_forecast.region_id = division.main_region 
					LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions' AND season_months.abbreviation  = '$ses' AND major_sector.language_id='$identity' AND advisory.region_id=1 GROUP BY advisory.id
					UNION 
					SELECT advisory.sector as ident, advisory.region_id as sub_reg, minor_sector.minor_name as sector from advisory 
					LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id 
					LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id 
					LEFT OUTER JOIN ussdmenulanguage on ussdmenulanguage.id = major_sector.language_id
					LEFT OUTER JOIN seasonal_forecast on advisory.forecast_id = seasonal_forecast.id 
					LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id 
					LEFT OUTER JOIN area_seasonal_forecast on advisory.forecast_id = area_seasonal_forecast.forecast_id 
					LEFT OUTER JOIN sub_region on sub_region.id = advisory.region_id 
					LEFT OUTER JOIN division on division.sub_region_id = sub_region.id where  major_sector.language_id = '$identity' and season_months.abbreviation = '$ses' AND division.division_name = '$divisions' GROUP BY advisory.id";



//                 $seasonal_and_advisory = "SELECT advisory.message_summary as summary, season_months.abbreviation as abbreviation, seasonal_forecast.year as year, minor_sector.minor_name as sector FROM advisory 
// LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id 
// LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id 
// LEFT OUTER JOIN ussdmenulanguage on ussdmenulanguage.id = major_sector.language_id 
// LEFT OUTER JOIN seasonal_forecast  on  advisory.forecast_id = seasonal_forecast.id 
// LEFT OUTER JOIN area_seasonal_forecast  on  advisory.forecast_id = area_seasonal_forecast.forecast_id 
// LEFT OUTER JOIN division  on  advisory.region_id = division.sub_region_id 
// LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions' AND ussdmenulanguage.id='$identity' AND season_months.abbreviation='$ses' GROUP BY advisory.id";

					$query = $conn->query($seasonal_and_advisory);
					if ($query->num_rows > 0) {
						while ($row = $query->fetch_assoc()) { $sect_count++;
						}
					}
				}else if($data_for == "Dekadal Forecast"){
                //$menuVal = "sdfghjkljhgfdsdfghjilo;yutrefstryuilfg";
				}

			}catch(Exception $ex){}
			return $sect_count;
		}






		public function getSectors($data_for){
			$menuVal = 0;
			$queryProc = "";
			$sectors_data = array();
			$sectors_data_ids = array();
			try {
				$conn = $this->connectionStr->ConnectionFc();
				$today = date('Y-m-d');
				$the_date = $today;
				$yesterday = date( 'Y-m-d', strtotime( $today . ' -1 day' ) );



				$divisions = strtoupper($_SESSION['district']);
				$lange = $_SESSION['language'];
				$qProc = "SELECT id, daily FROM ussdmenulanguage WHERE language = '$lange'";
				$seasonal_table = "";
				$identity = "";
				$ses= $this->get_seasom();
				$query = $conn->query($qProc);
				while ($row = $query->fetch_assoc()) {
					$identity = $row['id'];  
				}

				if($data_for == "Daily Forecast"){
					if(strtotime(date('Y-m-d g:ia')) < strtotime(date('Y-m-d').' 6:00pm') ){
						$the_date = $yesterday;
					}
					$daily_and_advisory="SELECT daily_advisory.sector as ident, daily_advisory.message_summary as summary,weather_category.cat_name as weather_desc, minor_sector.minor_name as sector, daily_forecast.time as currently FROM daily_advisory LEFT OUTER JOIN minor_sector on minor_sector.id=daily_advisory.sector LEFT OUTER JOIN daily_forecast on daily_forecast.id=daily_advisory.forecast_id LEFT OUTER JOIN daily_forecast_data on daily_forecast_data.forecast_id = daily_advisory.forecast_id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN division on division.region_id = daily_forecast_data.region_id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$the_date' AND daily_forecast.language_id = '$identity'";

					$query = $conn->query($daily_and_advisory);
					if ($query->num_rows > 0) {
						while ($row = $query->fetch_assoc()) {
							$sectors_data[] = $row['sector'];
							$sectors_data_ids[] = $row['ident'];
							$menuVal = 1;
						}
						$_SESSION['sectors'] = $sectors_data;
						$_SESSION['sectors_id'] = $sectors_data_ids;
					}
				}else if($data_for == "Seasonal Forecast"){

//             	$seasonal_and_advisory = "SELECT advisory.sector as ident, minor_sector.minor_name as sector FROM advisory LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id LEFT OUTER JOIN ussdmenulanguage on ussdmenulanguage.id = major_sector.language_id LEFT OUTER JOIN seasonal_forecast  on  advisory.forecast_id = seasonal_forecast.id LEFT OUTER JOIN area_seasonal_forecast  on  advisory.forecast_id = area_seasonal_forecast.forecast_id LEFT OUTER JOIN division  on  area_seasonal_forecast.region_id = division.main_region LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions' AND abbreviation  = '$ses' AND ussdmenulanguage.id='$identity' AND advisory.region_id=1 GROUP BY advisory.id UNION SELECT advisory.sector as ident, minor_sector.minor_name as sector from advisory LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id 
// LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id 

// LEFT OUTER JOIN ussdmenulanguage on ussdmenulanguage.id = major_sector.language_id

// LEFT OUTER JOIN seasonal_forecast on advisory.forecast_id = seasonal_forecast.id 
// LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id 
// LEFT OUTER JOIN area_seasonal_forecast on advisory.forecast_id = area_seasonal_forecast.forecast_id 
// LEFT OUTER JOIN sub_region on sub_region.id = advisory.region_id 
// LEFT OUTER JOIN division on division.sub_region_id = sub_region.id where  major_sector.language_id = '$identity' and season_months.abbreviation = '$ses' AND division.division_name = '$divisions' GROUP BY advisory.id";

					$seasonal_and_advisory = "SELECT advisory.sector as ident, advisory.region_id as sub_reg, minor_sector.minor_name as sector FROM advisory 
					LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id 
					LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id 
					LEFT OUTER JOIN seasonal_forecast  on  advisory.forecast_id = seasonal_forecast.id 
					LEFT OUTER JOIN area_seasonal_forecast  on  advisory.forecast_id = area_seasonal_forecast.forecast_id 
					LEFT OUTER JOIN division  on  area_seasonal_forecast.region_id = division.main_region 
					LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions' AND season_months.abbreviation  = '$ses' AND major_sector.language_id='$identity' AND advisory.region_id=1 GROUP BY advisory.id
					UNION 
					SELECT advisory.sector as ident, advisory.region_id as sub_reg, minor_sector.minor_name as sector from advisory 
					LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id 
					LEFT OUTER JOIN major_sector on major_sector.id = minor_sector.major_id 
					LEFT OUTER JOIN ussdmenulanguage on ussdmenulanguage.id = major_sector.language_id
					LEFT OUTER JOIN seasonal_forecast on advisory.forecast_id = seasonal_forecast.id 
					LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id 
					LEFT OUTER JOIN area_seasonal_forecast on advisory.forecast_id = area_seasonal_forecast.forecast_id 
					LEFT OUTER JOIN sub_region on sub_region.id = advisory.region_id 
					LEFT OUTER JOIN division on division.sub_region_id = sub_region.id where  major_sector.language_id = '$identity' and season_months.abbreviation = '$ses' AND division.division_name = '$divisions' GROUP BY advisory.id";

					$query = $conn->query($seasonal_and_advisory);
					if ($query->num_rows > 0) {
						while ($row = $query->fetch_assoc()) {
							$sectors_data[] = $row['sector'];
							$sectors_data_ids[] = $row['ident'];
							$menuVal = 1;
						}
						$_SESSION['sectors'] = $sectors_data;
						$_SESSION['sectors_id'] = $sectors_data_ids;
					}

				}else if($data_for == "Dekadal Forecast"){
                //$menuVal = "sdfghjkljhgfdsdfghjilo;yutrefstryuilfg";
				}
			}catch(Exception $ex){}
			return $menuVal;
		}

		function translate($record){
			$handle = fopen("Translation_files/".strtolower($langes).".txt", "r");
			while (($line = fgets($handle)) !== false) {
				if(strpos($line, $record) !== false){
					$data = explode("=>", $line);
					return $data[1];
				}

			}
			return $record;
			fclose($handle);
		}

		public function loaded_data($data_for, $ad=NULL){
			$menuVal = "";
			$queryProc = "";
			$menu_table = $this->Get_Menu();
			try {
				$conn = $this->connectionStr->ConnectionFc();
				$divisions = strtoupper($_SESSION['district']);
				$today = date('Y-m-d');

				$the_date = $today;

				$yesterday = date( 'Y-m-d', strtotime( $today . ' -1 day' ) );
				$tomorrow = date( 'Y-m-d', strtotime( $today . ' +1 day' ) );



				$days = date("l", strtotime(date("Y-m-d")));

				$day_tmr = date("l", strtotime($tomorrow));



				$season_date = date('Y-m-d');
				$queryProc = "";


            // Determine table to get the final resulst from
				$langes = $_SESSION['language'];
				$qProc = "SELECT id FROM ussdmenulanguage WHERE language = '$langes'";
				$seasonal_table = "";
				$identity = "";

				$query = $conn->query($qProc);
				while ($row = $query->fetch_assoc()) {
					$identity = $row['id'];  
				}
           // date_default_timezone_set('Africa/Nairobi');

				$times = array('0:00','6:00','12:00','18:00');
				$actual_time = date('g:ia');


            // checking for the month and getting the current season used to query the data

				$forecast_s = $_SESSION["forecast_selected"];


            //Read file translations
            // $forecast = "Daily forecast";
				$forecast = $this->loadUssdMenu('daily', $menu_table);
				$wind = $this->loadUssdMenu('wind', $menu_table);
				$temp = $this->loadUssdMenu('temp', $menu_table);
				$wet = $this->loadUssdMenu('wet', $menu_table);
				$sum = $this->loadUssdMenu('sum', $menu_table);
				$advice = $this->loadUssdMenu('advise', $menu_table);


            // Seasonal
				$start = $this->loadUssdMenu('start', $menu_table);
				$late = $this->loadUssdMenu('late', $menu_table);
				$mid = $this->loadUssdMenu('mid', $menu_table);
				$early = $this->loadUssdMenu('early', $menu_table);
				$peak = $this->loadUssdMenu('peak', $menu_table);
				$ends = $this->loadUssdMenu('ends', $menu_table);
				$checking = array('early','mid','late');



            // $time_skips = 0;
				$ses= $this->get_seasom();

				if($data_for == "Daily Forecast no advisory"){
					if(strtotime(date('Y-m-d g:ia')) < strtotime(date('Y-m-d').' 6:00pm') ){
						$the_date = $yesterday;


					}
					$queryProc = "SELECT daily_forecast.date as forecasted, forecast_time.period_name as period, forecast_time.from_time, forecast_time.to_time, daily_forecast_data.mean_temp as mean_temp,daily_forecast_data.period as periodic, daily_forecast_data.wind_strength as strength, weather_category.cat_name as weather_desc, daily_forecast.weather as weather FROM daily_forecast LEFT OUTER JOIN daily_forecast_data ON daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN region ON region.id = daily_forecast_data.region_id LEFT OUTER JOIN division on division.region_id = region.id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN forecast_time on daily_forecast_data.period = forecast_time.id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$the_date' AND daily_forecast.language_id = '$identity' AND daily_forecast.time = 5 ORDER BY daily_forecast_data.id ASC";

					$query = $conn->query($queryProc);
					if ($query->num_rows > 0) { $once = 0;
						$menuVal ="$divisions, $forecast_s\n";
						while ($row = $query->fetch_assoc()) {
							$from_time = $row["from_time"];
							$to_time = $row["to_time"];
							$days = date("l", strtotime($row["forecasted"]));
							$tomorrow = date( 'Y-m-d', strtotime($row["forecasted"] . ' +1 day' ) );
							$day_tmr = date("l", strtotime($tomorrow));

							if($once<1){
								$menuVal .="\n$sum: ".$row['weather']."\n";
								$day = $days;
							}else{
								$day = $day_tmr;
							}

                        // if($once < ($time_skips)){}else{                            
							$menuVal .= "$day, ".ucwords($row["period"])."\n($from_time - $to_time)"."\n$wind: ".$row["strength"]."\n$temp: ".$row['mean_temp'].".C\n$wet: ".ucwords($row['weather_desc'])."\n\n";

                        // }
							$once++;
						}
					}else{
						$queryProc = "SELECT daily_forecast.date as forecasted, forecast_time.period_name as period, forecast_time.from_time, forecast_time.to_time, daily_forecast_data.mean_temp as mean_temp,daily_forecast_data.period as periodic, daily_forecast_data.wind_strength as strength, weather_category.cat_name as weather_desc, daily_forecast.weather as weather FROM daily_forecast LEFT OUTER JOIN daily_forecast_data ON daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN region ON region.id = daily_forecast_data.region_id LEFT OUTER JOIN division on division.region_id = region.id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN forecast_time on daily_forecast_data.period = forecast_time.id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$today' AND daily_forecast.language_id = '$identity' AND daily_forecast.time <> 5 ORDER BY daily_forecast_data.id ASC";
						$query = $conn->query($queryProc);
						if ($query->num_rows > 0) {
							$menuVal ="$divisions, $forecast_s\n";
							while ($row = $query->fetch_assoc()) {
								$from_time = $row["from_time"];
								$to_time = $row["to_time"];
								$days = date("l", strtotime($row["forecasted"]));
								$tomorrow = date( 'Y-m-d', strtotime($row["forecasted"] . ' +1 day' ) );
								$day_tmr = date("l", strtotime($tomorrow));

								if($once<1){
									$menuVal .="\n$sum: ".$row['weather']."\n";
									$day = $days;
								}else{
									$day = $day_tmr;
								}

                            // if($once < ($time_skips)){}else{                            
								$menuVal .= "$day, ".ucwords($row["period"])."\n($from_time - $to_time)"."\n$wind: ".$row["strength"]."\n$temp: ".$row['mean_temp'].".C\n$wet: ".ucwords($row['weather_desc'])."\n\n";

                            // }
								$once++;
							}
						}
					}
				}else if($data_for == "Seasonal Forecast no advisory"){
					$seasonal = "SELECT area_seasonal_forecast.overall_comment as comment, area_seasonal_forecast.onset_period as onset_period,area_seasonal_forecast.onsetdesc as onsetdesc,area_seasonal_forecast.peakdesc as peakdesc,area_seasonal_forecast.expected_peak as expected_peak,area_seasonal_forecast.enddesc as enddesc,area_seasonal_forecast.end_period as end_period, seasonal_forecast.year as year, season_months.abbreviation as abbreviation  FROM area_seasonal_forecast
					LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id 
					LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id
					LEFT OUTER JOIN division on division.sub_region_id = area_seasonal_forecast.subregion_id
					WHERE season_months.abbreviation = '$ses' AND division.division_name = '$divisions'  AND area_seasonal_forecast.language_id='$identity'";




					$query = $conn->query($seasonal);
					if ($query->num_rows > 0) {
						while ($row = $query->fetch_assoc()) {
							if(date('Y') == $row["year"] && (($row["abbreviation"]) == $this->get_seasom()  )){


								$recd = array(strtolower($row["peakdesc"]),strtolower($row["onsetdesc"]),strtolower($row["enddesc"]));
								$final = array();
								foreach ($checking as $k) {
									foreach ($recd as $m) {
										if($m == $k){
											$final[] = $this->loadUssdMenu($k, $menu_table);
											break;
										}
									}
								}

                               // $menuVal = "$divisions, $forecast_s:\n$start ".$final[1]." ".$row['onset_period'].", $peak ".$final[0]." ".$row["expected_peak"]." $ends ".$final[2]." ".$row["end_period"];


								$menuVal = "$divisions,  $forecast_s:\n".$row["comment"];



							}
						}
					}

				}else if($data_for == "Daily Forecast"){
                // Daily forecast with advisory
                // date_default_timezone_set('Africa/Nairobi');


					$daily_and_advisory="SELECT daily_advisory.message_summary as summary,weather_category.cat_name as weather_desc, minor_sector.minor_name as sector, daily_forecast.time as currently FROM daily_advisory LEFT OUTER JOIN minor_sector on minor_sector.id=daily_advisory.sector LEFT OUTER JOIN daily_forecast on daily_forecast.id=daily_advisory.forecast_id LEFT OUTER JOIN daily_forecast_data on daily_forecast_data.forecast_id = daily_advisory.forecast_id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN division on division.region_id = daily_forecast_data.region_id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$today' AND daily_forecast.language_id = '$identity'";


					if(isset($ad)){
						$daily_and_advisory .=" AND minor_sector.id = '$ad'";
					}
                // $daily_and_advisory .=" LIMIT 1";
					$query = $conn->query($daily_and_advisory);
					if ($query->num_rows > 0) {
						while ($row = $query->fetch_assoc()) {

							$daily_data = "SELECT daily_forecast_data.mean_temp as mean_temp, daily_forecast_data.wind_strength as strength, daily_forecast.weather as weather FROM daily_forecast LEFT OUTER JOIN daily_forecast_data ON daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN region ON region.id = daily_forecast_data.region_id LEFT OUTER JOIN division on division.region_id = region.id WHERE division.division_name = '$divisions' AND daily_forecast.date = '$today'  AND daily_forecast.language_id = '$identity' ORDER BY daily_forecast_data.id DESC LIMIT 1";

							$qy = $conn->query($daily_data);
							$strength = "";
							$mean_temp = "";
							$weather = "";


							while ($rows = $qy->fetch_assoc()) {
								$strength = $rows['strength'];
								$mean_temp = $rows['mean_temp'];
								$weather = $rows['weather'];

							}
							$menuVal = "$divisions, $day, $forecast_s\n$wind: ".$strength."\n$temp: ".$mean_temp.".C\n$wet: ".$row['weather_desc']."\n$sum: ".$weather."\n\n".$row['sector'].": ".$row['summary'];



						}
					}
				}else if($data_for == "Seasonal Forecast"){
					$ses= $this->get_seasom();
                // write a query that joins the forecast to the advisory but for the where clause of advisory sector
                // it should be in the if statement
					$seasonal_and_advisory = "SELECT advisory.message_summary as summary, season_months.abbreviation as abbreviation, seasonal_forecast.year as year, minor_sector.minor_name as sector FROM advisory LEFT OUTER JOIN minor_sector on advisory.sector = minor_sector.id LEFT OUTER JOIN seasonal_forecast  on  advisory.forecast_id = seasonal_forecast.id LEFT OUTER JOIN area_seasonal_forecast  on  advisory.forecast_id = area_seasonal_forecast.forecast_id LEFT OUTER JOIN division  on  area_seasonal_forecast.region_id = division.main_region LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE division.division_name = '$divisions' AND season_months.abbreviation='$ses' ";
					if(isset($ad)){
						$seasonal_and_advisory .=" AND minor_sector.id = $ad";
					}
					$seasonal_and_advisory .=" GROUP BY advisory.id";


					$query = $conn->query($seasonal_and_advisory);
					if ($query->num_rows > 0) {
						while ($row = $query->fetch_assoc()) {
							$data_qry = "SELECT area_seasonal_forecast.overall_comment as comment, area_seasonal_forecast.onset_period as onset_period,area_seasonal_forecast.onsetdesc as onsetdesc,area_seasonal_forecast.peakdesc as peakdesc,area_seasonal_forecast.expected_peak as expected_peak,area_seasonal_forecast.enddesc as enddesc,area_seasonal_forecast.end_period as end_period, seasonal_forecast.year as year, season_months.abbreviation as abbreviation  FROM area_seasonal_forecast
							LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id 
							LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id
							LEFT OUTER JOIN division on division.sub_region_id = area_seasonal_forecast.subregion_id
							WHERE season_months.abbreviation = '$ses' AND division.division_name = '$divisions'  AND area_seasonal_forecast.language_id='$identity'";

							$querying = $conn->query($data_qry);
							$onsetdesc = "";
							$onset_period = "";
							$peakdesc = "";
							$expected_peak = "";
							$enddesc = "";
							$end_period = "";$comments = "";


							while ($rows = $querying->fetch_assoc()) {
								$comments = $rows['comment'];
								$onsetdesc = $rows["onsetdesc"];
								$onset_period = $rows["onset_period"];
								$peakdesc = $rows["peakdesc"];
								$expected_peak = $rows["expected_peak"];
								$enddesc = $rows["enddesc"];
								$end_period = $rows["end_period"];
							}

							if(date('Y') == $row["year"] && (($row["abbreviation"]) == $this->get_seasom())){

								$recd = array(strtolower($peakdesc),strtolower($onsetdesc),strtolower($enddesc));
								$final = array();
								foreach ($checking as $k) {
									foreach ($recd as $m) {
										if($m == $k){
											$final[] = $this->loadUssdMenu($k, $menu_table);
											break;
										}
									}
								}

								if($lange == "English"){
									$menuVal = "$divisions,  $forecast_s:\n$start ".$final[1]." ".$onset_period .", $peak ".$final[0]." ".$expected_peak." $ends ".$final[2]." ".$end_period."\n\n$advice ".$row['sector'].": ".$row['summary'];
								}else{
									$menuVal = "$divisions,  $forecast_s:\n".$comments."\n\n$advice ".$row['sector'].": ".$row['summary'];
								}


							}
						}
					}

				}else if($data_for == "Dekadal Forecast"){
                // Dekadal forecast with advisory
                // You may label the query variable a different name
					$queryProc = "";
    //  $menuVal = "qwertyuiouytdfsfghjklsdadfyuiuyjgd";


				}else if($data_for == "Dekadal Forecast no advisory"){ 
                // The dakadal with no advisory
                // You may label the query variable a different name
					$queryProc = "";
               // $menuVal = "sdfghjkljhgfdsdfghjilo;yutrefstryuilfg";
				}
             // The menuVal variable in the loop holds the data from the database that shall be send via the sms, when is starts working
           //changed the format(john)



			}catch(Exception $e){
			}
			return $menuVal;
		}

		public function sendMessage($phoneNumber, $message)
		{
			$msg=str_replace("<br/>", "\n", $message);
			$resp = "";
			try {
				$textmessage = urlencode($msg);
				$url = 'http://simplysms.com/getapi.php';
				$urlfinal = $url.'?'.'email'.'='.'rc4wids@yahoo.com'.'&'.'password'.'='.'VBsd9A2'.'&'.'sender'.'='.'8777'.'&'.'message'.'='.$textmessage.'&'.'recipients'.'='.$phoneNumber;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $urlfinal);
				curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER =>1,CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

				$resp = curl_exec($ch);

				curl_close($ch);
			} catch (Exception $e) {
			}
			return $resp;
		}

		public function Insertion_UpdateQuerys($sql)
		{
			try {
				$conn = $this->connectionStr->ConnectionFc();


				if ($conn->query($sql) === true) {

				} else {
					$action = "end";
					$strToDisp = "APPLICATION CONNECTION ERROR";
					$this->smsApi->ussdResponseSender(trim($strToDisp), $action);
				}

             //$conn->close();
			} catch (Exception $ex) {
			}
		}

		public function Insertion_UpdateQuerysMainSession($sql)
		{
			$status = "";
			try {
				$conn = $this->connectionStr->ConnectionFc();

				if ($conn == "") {
					$status = "1" ;
				} else {
					if ($conn->query($sql) === true) {
                     //
						$status = "0" ;
					} else {
						$status = "1" ;
					}
				}


             //$conn->close();
			} catch (Exception $ex) {
			}

			return $status;
		}
		public function triggerCall($number){

			try{
				$this->makeCall($number);
			}catch(Exception $e){
            // echo "Encountered an error while making the call";
				echo $e;
			}
		}
		function makeCall($number){
                // Set your app credentials

			$username = "personalization";
			$apiKey   = "bfc0a4fef4dcbd28c2010e042b701fc96aa5e3efca0a3f49abd0f735584653b2";

            // // Initialize the SDK
			$AT       = new AfricasTalking($username, $apiKey);

            // Get the voice service
			$voice    = $AT->voice();

            // // Set your Africa's Talking phone number in international format
			$from     = "+256312319888";

            // // Set the numbers you want to call to in a comma-separated list
			$to       = "+".$number;

			try {
            //         // Make the call
				$results = $voice->call([
					'from' => $from,
					'to'   => $to
				]);

                    // print_r($results);

				return $results;
			} 
			catch (Exception $e) {
				return $e;
				echo "Error: ".$e->getMessage();
			} 
		}

		public function checkAudio(){
			try{
				$conn = $this->connectionStr->ConnectionFc();
				$lang = $_SESSION['language'];
				$lang_id = $_SESSION['lang_id'];

				$season = "unknown";
				if((date('m') == 1) || (date('m') == 2) ) $season = 'MAM';
				else  if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
				else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
				else $season = 'SOND';


				$name = $lang_id."_".$season."_".date('Y');
				$queryProc = "SELECT * FROM voice WHERE voice_name = '$name' LIMIT 1";
				$query = $conn->query($queryProc);
				if ($query->num_rows > 0) {
					return 1;
				}else{
					return 0;
				}
			}catch(Exception $ex){}
		}

		public function LogCall_Requests($msisdn, $sessionID)
		{
			$lang_id = $_SESSION['lang_id'];
			$sql= "INSERT INTO `voice_requests`(`phone`, `language_id`,`sessionID`) VALUES ('$msisdn', '$lang_id', '$sessionID')";
			$result = $this->Insertion_UpdateQuerysMainSession($sql);
		}
	}

	?>
