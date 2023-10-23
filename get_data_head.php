<?php
// Konfiguration einbinden
require_once("airq.inc.php");

function decrypt($msgb64,$password)
{
    $airqpass = $password;
	if (strlen($airqpass) < 32) {
		for ($i = strlen($airqpass); $i < 32; $i++) {
			$airqpass = $airqpass . '0';
		}
	} else {
		if (strlen($airqpass) > 32) {
			$airqpass = substr($airqpass,0,32);
		}
	}

	$key = utf8_encode ($airqpass);
//	$cyphertext = base64_decode ($msgb64);
//	But with verly long messages there could be some problems in base64_decode
	$decoded = "";
	for ($i=0; $i < ceil(strlen($msgb64)/256); $i++)
	   $decoded = $decoded . base64_decode(substr($msgb64,$i*256,256));
	$cyphertext = $decoded;


	$iv = substr($cyphertext,0,16);
	$cyphertext = substr($cyphertext,16,strlen($cyphertext));

//	With php version <= 7.1 you can use the following, but this is deleted in version 7.2 and above
//	$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $cyphertext, MCRYPT_MODE_CBC, $iv);
	
	$decrypted = openssl_decrypt($cyphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	
	return utf8_encode($decrypted);
}

// Possible comands: ping, config, data, log, dirbuff, file&request=..
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = "";
$url = $airq . '/data';
curl_setopt($ch, CURLOPT_URL, $url); 
$output = curl_exec($ch);
$json = json_decode($output, true);
$output = decrypt($json["content"],$password);
$arrdata = json_decode($output, TRUE);
      
$fd = fopen("data.csv","w");
$zeile = "\n";
$head  = "\n";
foreach($arrdata as $key => $value) {
    switch ($key) {
        case "cnt0_3":
        case "cnt0_5":
        case "cnt1":
        case "cnt10":
        case "cnt2_5":
        case "cnt5":
        case "co":
        case "co2":
        case "dewpt":
        case "h2s":
        case "humidity":
        case "humidity_abs":
        case "no2":
        case "o3":
        case "oxygen":
        case "pm1":
        case "pm10":
        case "pm2_5":
        case "pressure":
        case "pressure_rel":
        case "sound":
        case "sound_max":
        case "temperature":
        case "tvoc":
        case "virus":                
            $zeile .= $value[0].";".$value[1].";";
            $head .= $key."_value;".$key."_abw;";
            break;
        case "dCO2dt":
        case "DeviceID":
        case "dHdt":
        case "health":
        case "measuretime":
        case "performance":
        case "Status":
        case "timestamp":
        case "TypPS":
        case "uptime":
            $zeile .= $value.";";
            $head .= $key."_value;";
            break;    
    }
}
fwrite($fd,$head);
fwrite($fd,$zeile);
fclose($fd);
print_r($head);
print_r($zeile);
?>