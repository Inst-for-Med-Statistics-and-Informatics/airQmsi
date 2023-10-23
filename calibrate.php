<?php
// Konfiguration einbinden
include("airq.inc.php");

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

$url = $airq . "/calibrate";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
   "Content-Type:application/x-www-form-urlencoded",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = file_get_contents("calibrate.json");          
          
curl_setopt($curl, CURLOPT_POSTFIELDS,"request=".encrypt($data,$password));
$output  = "";
$output  = curl_exec($curl);
curl_close($curl);
$json    = json_decode($output, true);
$output  = decrypt($json["content"],$password);
$arrdata = json_decode($output, TRUE);

// Debug: Url
print "<table width='100%' style='background: #CCCCCC;'>";
print "<tr><td><b>URL-POST:</b></td>";
print "<td>$url, <br />request=".encrypt($data,$password)."</td></tr>";
print "</table>";
print "<hr />";
print "<table width='100%'>";
print "<tr><td><b>Datenarray</b></td><td><pre>$data</pre></td></tr>";
print "</table>";
print "<hr />";
print "<pre>";
print_r($arrdata);
print "</pre>";
?>