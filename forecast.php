<?php

class Prediction {
	public $day='';
	public $temp_c=0;
	public $temp_f=0;
	public $humidity=0;
	public $cond='';
	public $wind=0;

	function set ($day, $temp, $humidity, $cond, $wind) {
		$this->day = $day;
		$this->temp_c = -273.15+$temp;
		$this->temp_f = $this->temp_c*9/5+32;
		$this->humidity = $humidity;
		$this->cond = $cond;
		$this->wind = $wind;
	}

	function obj_set ($obj) {
		$this->set($obj->dt_txt, $obj->main->temp, $obj->main->humidity, $obj->weather[0]->description, $obj->wind->speed);
	}

	function to_str () {
		$output = date("l", strtotime($this->day));
		$output .= " ";
		$output .= date("H", strtotime($this->day));
		$output .= ": ";

		# Temperature
		$output .= sprintf(" %.1f°C/%.0f°F ", $this->temp_c, $this->temp_f);

		# Conditions
		$output .= $this->cond;
		$output .= " ";

		# Wind
		#$output .= sprintf("wind %.0fm/s, ", $this->wind);
		
		# Humidity
		#$output .= sprintf("humidity %.0f", $this->humidity);
		#$output .= "% ";

		#$output .= "● ";
		return $output;
	}
}

function get_data ($url, $key) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url.$key);
	$result = curl_exec($ch);
	curl_close($ch);
	$obj = json_decode($result);
	return $obj;
}

function get_location_string ($obj) {
	$city = $obj->city->name;
	$country = $obj->city->country;
	return $city . ", " . $country;
}

function filter_json ($obj, $date) {
	$pred_list = $obj->list;
	foreach ($pred_list as &$pred) {
		if ($pred->dt_txt > $date) {
			return $pred;
		}
	}
	return $pred_list[0];
}

function get_prediction_for_day ($obj, $day) {
	$pred_obj = filter_json($obj, date($day." 14:00:00"));
	$pred = new Prediction();
	$pred->obj_set($pred_obj);
	return $pred;
}

function get_predictions ($obj, $start_date) {
	$all_predictions = "";
	$start_day = date("Y-m-d", strtotime($start_date));
	$start_hour = date("H", strtotime($start_date));
	
	if ( ((int) $start_hour) > 18) {
		$start_day = date("Y-m-d", strtotime($start_day. " +1 days"));
	}

	for ($i=0; $i < 3; $i++) {
		$pred_date = date("Y-m-d", strtotime($start_day. " +".$i." days"));
		$pred = get_prediction_for_day($obj, $pred_date);
		$all_predictions .= $pred->to_str();

		if ($i < 2) { $all_predictions .= "● "; }
	}

	return $all_predictions;
}

function get_latest_date ($obj) {
	$date_str = $obj->list[1]->dt_txt;
	return $date_str;
}


$input=$_GET['q'];
$place=filter_var($input,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$key = rtrim(file_get_contents('../weatherkey', true));
$url = "http://api.openweathermap.org/data/2.5/forecast?q=$place&appid=";

$obj = get_data($url, $key);

$location_str = get_location_string ($obj);
$start_date = get_latest_date($obj);
$pred_str = get_predictions($obj, $start_date);

?>

<!doctype html>
<html>
<head>
  <title><?php echo $location_str . " - " . $pred_str ?></title>
</head>

<body>
<?php echo $location_str . " - " . $pred_str ?>
</body>
