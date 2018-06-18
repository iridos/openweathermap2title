 <!DOCTYPE html>
 <html>
<head>
<meta charset="UTF-8">
<title> <?php
$input=$_GET['q'];
$place=filter_var($input,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$key = rtrim(file_get_contents('../weatherkey', true));
$url = "http://api.openweathermap.org/data/2.5/weather?q=$place&appid=".$key;
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
curl_close($ch);
$obj = json_decode($result);

$output= "$obj->name,".$obj->sys->country.
	"(lat,lon=".$obj->coord->lat.",".$obj->coord->lon.
	") - Condition: ".$obj->weather[0]->main.
	" - ".$obj->weather[0]->description;
$output.=". Temperature ".(-273.15+$obj->main->temp)."°C (".(-273.15+$obj->main->temp_min);
$output.="°C to ".(-273.15+$obj->main->temp_max).") ";
$output.="Wind: ".round(1.609344*$obj->wind);
$output.="km/h Humidity:".$obj->main->humidity."% pressure at sea level:";
$output.=$obj->main->pressure."hPa Visibility: ".($obj->visibility/1000)."km ";
#echo "Sunrise: ";
if(!(isset($obj->main->temp))){$output="Sorry, '$input' not found";};
echo $output;

?> </title>
</head> <body>
<?php 
echo $output;
echo "<p><br>$result<br><br><p>";
#echo "x".$url."x";
?>
</body>
 </html> 
