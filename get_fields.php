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
      
$output = "";
foreach($arrdata as $key => $value) {
    if(is_array($value)==true) {
        $output .= "<tr><td>$key</td><td>".$key."_value</td><td>".$key."_abw</td><td>$value[0]</td><td>$value[1]</td><td>2</td></tr>";
    } else {
        $output .= "<tr><td>$key</td><td>".$key."_value</td><td></td><td>$value</td><td></td><td>1</td></tr>";
    }
}
print "<table border='1'>";
print "<tr style='background: #CCCCCC; font-weight: bold;'><td>Schlüssel</td><td>Feld 1</td><td>Feld 2</td><td>Wert 1</td><td>Wert 2</td><td>Anzahl Werte</td></tr>";
print str_replace(".",",",$output);
print "</table>";
?>