<?php

$dbname = '';
$dbuser = '';
$dbpass = '';
$dbhost = 'localhost';
$conn = mysqli_connect($dbhost, $dbuser,$dbpass,$dbname);

function get_seasom(){
	$season = "unknown";
	if((date('m') == 1) || (date('m') == 2) ) $season = 'MAM';
	else  if((date('m') == 3) || (date('m') == 4)  || (date('m') == 5) ) $season = 'MAM';
	else if ((date('m') == 6) || (date('m') == 7)  || (date('m') == 8) ) $season = 'JJA';
	else $season = 'SOND';
	return $season;
}

function getFirstMonth($season){
	$month = 0;
	switch ($season) {
		case 'SOND':
		$month = 9;

		break;
		case 'JJA':
		$month = 6;
		break;
		case 'MAM':
		$month = 3;
		break;
		
		default:
			# code...
		break;
	}
	return date('Y-m-d', strtotime("1-".$month."-".date('Y')));
}
function getMonth($num){

}
function getDistrict($conn, $dist_name){
	$queryProc = "SELECT * FROM `division` WHERE id = '".$dist_name."'";
	$query = mysqli_query($conn, $queryProc);
	$name = "";
	while ($row = $query->fetch_assoc()) { 
		$name = $row['division_name'];
	}
	$_SESSION['district_selected'] = ucwords(strtolower($name));
	return ucwords(strtolower($name));
}

function getForcest($identity){
	$label = "";
	switch ($identity) {
		case '1':
		$label = "Daily Forecast";
		break;
		case '2':
		$label = "Seasonal Forecast";
		break;
		
		default:
			# code...
		break;
	}
	$_SESSION['forecast'] = $label;
	return $label;
}

function checkData($conn, $id){
	$exists = 0;
	try{
		$today = date('Y-m-d');
		$ses= get_seasom();
		$queryProc = "SELECT * FROM division LEFT OUTER JOIN region on division.main_region = region.id LEFT OUTER JOIN area_seasonal_forecast on area_seasonal_forecast.region_id =  division.main_region LEFT OUTER JOIN sub_region on area_seasonal_forecast.subregion_id = sub_region.id LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id WHERE area_seasonal_forecast.language_id='$id' AND  season_months.abbreviation = '$ses' LIMIT 1";

		// $rest = $this->loaded_data("Seasonal Forecast no advisory");

		$query = mysqli_query($conn, $queryProc);
		if (($query->num_rows > 0 )) {
			return 1;
		}else{
			return 0;
		}

	}catch(Exception $ex){ }

}



function loadUssdMenu($peds, $conn, $table){
	$queryProc = "SELECT * FROM $table WHERE menuname = '$peds'";
	$query = mysqli_query($conn, $queryProc);
	if ($query->num_rows > 0) {
		while ($row = $query->fetch_assoc()) {
			$menuVal = $row["menudescription"];
		}
	}
	return $menuVal;

}

function Get_Menu($conn, $id){

	$queryProc = "";
	$menu_table = "";
	try {
		$lang = getLanguageById($conn, $id);

		$lang_qry = "SELECT language_text_table FROM ussdmenulanguage WHERE language = '$lang'";
		$qry = mysqli_query($conn, $lang_qry);
		if ($qry->num_rows > 0) {
			while ($row = $qry->fetch_assoc()) {
				$menu_table = $row['language_text_table'];
			}   
		}

	}catch(Exception $ex){}
	return $menu_table;
}

function getLanguageById($conn, $id){
	$lang_qry = "SELECT * FROM ussdmenulanguage WHERE id = '$id'";
	$qry = mysqli_query($conn, $lang_qry);
	$lang = "";
	while ($row = $qry->fetch_assoc()) {
		$lang = $row['language'];
	}   
	return $lang;
}
function seasonal($conn, $district, $period, $lang){
	$forecast_s = getForcest($period);
	$menu_table = Get_Menu($conn, $lang);
	$ses= get_seasom();
	$divisions = getDistrict($conn, $district);
	$checking = array('early','mid','late');

	$seasonal = "SELECT area_seasonal_forecast.overall_comment as comment, area_seasonal_forecast.onset_period as onset_period,area_seasonal_forecast.onsetdesc as onsetdesc,area_seasonal_forecast.peakdesc as peakdesc,area_seasonal_forecast.expected_peak as expected_peak,area_seasonal_forecast.enddesc as enddesc,area_seasonal_forecast.end_period as end_period, seasonal_forecast.year as year, season_months.abbreviation as abbreviation  FROM area_seasonal_forecast
	LEFT OUTER JOIN seasonal_forecast on area_seasonal_forecast.forecast_id = seasonal_forecast.id 
	LEFT OUTER JOIN season_months on seasonal_forecast.season_id = season_months.id
	LEFT OUTER JOIN division on division.sub_region_id = area_seasonal_forecast.subregion_id
	WHERE season_months.abbreviation = '$ses' AND division.division_name = '$divisions'  AND area_seasonal_forecast.language_id='$lang'";


	$query = mysqli_query($conn, $seasonal);
	if ($query->num_rows > 0) {
		while ($row = $query->fetch_assoc()) {
			if(date('Y') == $row["year"] && (($row["abbreviation"]) == get_seasom()  )){
				$recd = array(strtolower($row["peakdesc"]),strtolower($row["onsetdesc"]),strtolower($row["enddesc"]));
				$final = array();
				foreach ($checking as $k) {
					foreach ($recd as $m) {
						if($m == $k){
							$final[] = loadUssdMenu($k,$conn, $menu_table);
							break;
						}
					}
				}
				$menuVal = "$divisions,  $forecast_s:\n".$row["comment"];
			}
		}
	}
	return $menuVal;

}

function daily($conn, $district,$period, $lang){
	$menu_table = Get_Menu($conn, $lang);
	$forecast = loadUssdMenu('daily', $conn, $menu_table);
	$wind = loadUssdMenu('wind', $conn, $menu_table);
	$temp = loadUssdMenu('temp', $conn, $menu_table);
	$wet = loadUssdMenu('wet', $conn, $menu_table);
	$sum = loadUssdMenu('sum', $conn, $menu_table);
	$advice = loadUssdMenu('advise', $conn, $menu_table);
	$today = date('Y-m-d');
	$the_date = $today;
	// $the_date = date( 'Y-m-d', strtotime( $today . ' -2 day' ) );
	$day = date('jS F Y', strtotime($the_date));
	$forecast_s = getForcest($period);
	$divisions = getDistrict($conn, $district);
	$queryProc = "SELECT daily_forecast.date as forecasted, forecast_time.period_name as period, forecast_time.from_time, forecast_time.to_time, daily_forecast_data.mean_temp as mean_temp,daily_forecast_data.period as periodic, daily_forecast_data.wind_strength as strength, weather_category.cat_name as weather_desc, daily_forecast.weather as weather FROM daily_forecast LEFT OUTER JOIN daily_forecast_data ON daily_forecast.id = daily_forecast_data.forecast_id LEFT OUTER JOIN region ON region.id = daily_forecast_data.region_id LEFT OUTER JOIN division on division.region_id = region.id LEFT OUTER JOIN weather_category on weather_category.id = daily_forecast_data.weather_cat_id LEFT OUTER JOIN forecast_time on daily_forecast_data.period = forecast_time.id WHERE division.id = '$district' AND daily_forecast.date = '$the_date' AND daily_forecast.language_id = '$lang' AND daily_forecast.time = 5 ORDER BY daily_forecast_data.id ASC";
	$query = mysqli_query($conn, $queryProc);
	$once = 0;
	$menuVal = "";
	if ($query->num_rows > 0) {
		$menuVal ="$divisions, $forecast_s, $day";
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
			$menuVal .= "$day, ".ucwords($row["period"])."\n($from_time - $to_time)"."\n$wind: ".$row["strength"]."\n$temp: ".$row['mean_temp'].".C\n$wet: ".ucwords($row['weather_desc'])."\n\n";
			$once++;
		}
	}
	return $menuVal;
}

function Messages($message,$phoneNumber){ 

	$resp = "";
	try{
		$textmessage = urlencode($message."\nDial *255*85#");
		$ch = curl_init();
		curl_setopt_array($ch,array(
			CURLOPT_RETURNTRANSFER =>1, 
			CURLOPT_URL =>'http://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=wimea&passwd=w1m34M4k&from=Wimea&numbers='.$phoneNumber.'&msg='.$textmessage.'&type=text',
            // CURLOPT_URL =>'http://simplysms.com/getapi.php?email=mnsabagwa@cit.ac.ug&password=XyZp3q7&sender=8777&message='.$textmessage.'&recipients='.$phoneNumber,
			CURLOPT_USERAGENT =>'Codular Sample cURL Request'));

		$resp = curl_exec($ch);

		curl_close($ch);

	}catch(Exception $e){}
	return $resp;

}

function log_messages($conn, $phone, $message){
	$qry = "INSERT INTO `ussd_subscriptions_messages`(`phone`, `message`, `district`, `forecast`) VALUES ('".$phone."','".$message."', '".$_SESSION['district_selected']."', '".$_SESSION['forecast']."')";
	mysqli_query($conn, $qry);
}

if($conn){

	// For the daily forecast
	$qry = "SELECT * FROM ussd_subscriptions WHERE forecast = 1";
	$rest = mysqli_query($conn, $qry);
	// echo getDistrict($conn, 1);
	while($row = $rest->fetch_assoc()){
		$data = daily($conn, $row['district'], 1,$row['language_id']);
		if(!empty($data)){
			Messages($data, $row['phone']);
			log_messages($conn, $row['phone'], $data);
		}
		
	}

	// For the seasonal Forecast
	if(date("Y-m-d") == getFirstMonth(get_seasom())){
		$qry = "SELECT * FROM ussd_subscriptions WHERE forecast = 2";
		$rest = mysqli_query($conn, $qry);
		while($row = $rest->fetch_assoc()){
			$data = seasonal($conn, $row['district'], 2, $row['language_id']);
			if(!empty($data)){
				Messages($data, $row['phone']);
				log_messages($conn, $row['phone'], $data);
			}
		}
	}
}

?>
