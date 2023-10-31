<?php
// Konfiguration einbinden
require_once("airq.inc.php");

// Prüfen ob das Script von der Konsole oder über den Webbrowser aufgerufen wird
// Über den Webbrowser wird nur ein Output generiert, nichts in die data.csv geschrieben
if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
    $console = true;
} else {
    $console = false;
}

// Funktion zum entschlüsseln der Daten des air-Q
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

// Funktion zum verschlüsseln der Daten des air-Q
function encrypt($msgb64,$password)
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
	
	$iv = substr(openssl_random_pseudo_bytes(32),0,16);

	$encrypted = openssl_encrypt($msgb64, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	return base64_encode($iv . $encrypted);
}

// Was soll abgerufen werden (geht nur über Webbrowser)
$param = (isset($_GET['param'])==true) ? $_GET['param'] : "";
switch($param) {
    case "average":
        // gleitenden Mittelwert der aktuellen Messdaten
        // Mittelwert über 30 Messungen
        $url = $airq . "/average";
        break;    
    case "blink":
        // Lässt alle LEDs in den Regenbogenfarben aufleuchten
        $url = $airq . "/blink";
        break;  
    case "config":
        // Konfiguration
        $url = $airq . "/config";
        break;
    case "health":
        // Lässt alle LEDs in den Regenbogenfarben aufleuchten
        $url = $airq . "/health";
        break;
    case "log":
        // Lässt alle LEDs in den Regenbogenfarben aufleuchten
        $url = $airq . "/log";
        break;            
    case "ping":
        // Gibt eine schnelle Kurzzusammenfassung als JSON-Objekt zurück mit dem folgenden Aufbau
        $url = $airq . "/ping";
        break;
    case "playsound":
        // gibt einen Ton über den eingebauten Piepser aus
        $url = $airq . "/playsound";
        break;  
    case "ssid":
        // gibt eine Liste der SSID aus, die der air-Q im Hostspotmodus fand
        $url = $airq . "/ssid";
        break;  
    case "standardpass":
        // gibt aus ob das Standardpasswort "airqsetup" noch gesetzt ist
        // gibt eine Liste der SSID aus, die der air-Q im Hostspotmodus fand
        $url = $airq . "/standardpass";
        break;    
    case "version":
        // Gibt die API-Version als Zahl zurück.
        $url = $airq . "/version";
        break;                                         
    /***************************************************************************
     *             Das folgende geht nur mit eingesteckter SD-Karte            *
     ***************************************************************************/
    case "dir":
        // Ausgabe der Daten eines Verzeichnisses: Format YYYY/MM/DD bzw. YYYY/M/D
        // zB. request=2023/10/15 oder request=2023/8/7 
        if(isset($_GET['request'])==true) {
            $url = $airq . "/dir?request=".$_GET['request'];
        }
        break;
    case "dirbuff":
        // Ausgabe aller Daten des Verzeichnisses 
        $url = $airq . '/dirbuff';
        break;  
    case "file":
        // Ausgabe einer spezifischen Messdaten-Datei
        // zb.  request=2023/9/17/1591176905,
        //      gibt den Inhalt der Datei 1591176905 vom 17.09.2023 zurück
        if(isset($_GET['request'])==true) {
            $url = $airq . "/file?request=".$_GET['request'];
        }
        break;  
    default:
        $url = $airq . '/data';
        break;        
}             

// Debug: Url
if($console!=true) {
 print "<table width='100%' style='background: #CCCCCC;'>";
 print "<tr><td><b>URL:</b></td>";
 print "<td><a href='$url' target='_blank'>$url</a></td></tr>";
 print "</table>";
 print "<hr />";
}

// Possible comands: ping, config, data, log, dirbuff, file&request=..
$curl = curl_init(); 
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($curl, CURLOPT_URL, $url);
$output = ""; 
$output = curl_exec($curl);
curl_close($curl);
$json = json_decode($output, true);
switch($param) {
    // Bei einigen Parameter bedarf es keiner entschlüsselung
    case "blink":
    case "playsound":
    case "standardpass":
    case "version":
        break;
    default:
        $output = decrypt($json["content"],$password);
        break;
}
$arrdata = json_decode($output, TRUE);
      
if($console==true && $param=="") { 
  $fd = fopen("data.csv","a"); 
  $lg = fopen("data_log.csv","a");
  $head  = "";
  $zeile = "\n";
  $loger = "\n".date("d.m.Y H:i:s").";";
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
        //case "measuretime":
        case "performance":
        case "Status":
        //case "timestamp":
        case "TypPS":
        case "uptime":
            $zeile .= $value.";";
            $head .= $key."_value;";
            break;
        case "measuretime":
        case "timestamp":
            $zeile .= $value.";";
            $loger .= $value.";";
            $head .= $key."_value;";
            break;      
      }
  }  
  fwrite($fd,$zeile);
  fwrite($lg,$loger);
  fclose($fd);
  fclose($lg);
} else {
  print "<pre>";
  print_r($arrdata);
  print "</pre>";
}
?>