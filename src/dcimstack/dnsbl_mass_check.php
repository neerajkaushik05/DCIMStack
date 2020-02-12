<?php

#error_reporting(E_ALL);
#ini_set('display_errors', 1);
error_reporting(0);

include_once('config/db.php');

$sql = "SELECT value FROM settings WHERE id='9'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
	$lock = $row["value"];
    }
}


$running = exec("ps aux|grep /var/www/DCIMStack/dnsbl_mass_check.php|grep -v grep|wc -l");
if($running > 1) {
echo "I am running\n";
   exit;
}

#check for time/date if script isnt running check time then kill if failed.

#start script
$today = date("Y-m-d"); 
$old = date("Y-m-d", strtotime($today." -1 Days"));
$sql = "SELECT * FROM bipm WHERE last_date_check < '$old' ORDER BY last_date_check ASC LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	$ip_range_node = $row["id"];
	$ip_range = $row["ip_range"];
        #echo " $ip_range_node<br>";
    }
} else {
    echo "0 results";
    exit();
}

function mattermost($ip_range) {
$ch = curl_init('https://mattermost.crowncloud.net/hooks/5i57r84jxpfet8qtd1a75hzmzh');
$payload = [
	'username' => "BIPMBOT",
	'text' => "Starting Check on $ip_range",
	'channel' => "host-alerts"
];
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'payload=' . json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HEADER, 1);
$result = curl_exec($ch);
curl_close($ch);
return $result;
}

mattermost($ip_range);

$sql = "UPDATE settings SET value='Yes' WHERE id='9'";

if ($conn->query($sql) === TRUE) {
    echo "I have locked mass lookup while running.\n";
}

function flush_buffers() { 
    ob_end_flush(); 
    flush(); 
    ob_start(); 
}

function dnsbllookup($ip)
{
$dnsbl_lookup=array(
"all.s5h.net",
"b.barracudacentral.org",
"bl.emailbasura.org",
"bl.spamcannibal.org",
"bl.spamcop.net",
"blacklist.woody.ch",
"bogons.cymru.com",
"cbl.abuseat.org",
"cdl.anti-spam.org.cn",
"combined.abuse.ch",
"db.wpbl.info",
"dnsbl-1.uceprotect.net",
"dnsbl-2.uceprotect.net",
"dnsbl-3.uceprotect.net",
"dnsbl.anticaptcha.net",
"dnsbl.cyberlogic.net",
"dnsbl.dronebl.org",
"dnsbl.inps.de",
"dnsbl.sorbs.net",
"drone.abuse.ch",
"duinv.aupads.org",
"dul.dnsbl.sorbs.net",
"dyna.spamrats.com",
"dynip.rothen.com",
"exitnodes.tor.dnsbl.sectoor.de",
"http.dnsbl.sorbs.net",
"ips.backscatterer.org",
"ix.dnsbl.manitu.net",
"korea.services.net",
"misc.dnsbl.sorbs.net",
"noptr.spamrats.com",
"orvedb.aupads.org",
"pbl.spamhaus.org",
"proxy.bl.gweep.ca",
"psbl.surriel.com",
"relays.bl.gweep.ca",
"relays.nether.net",
"sbl.spamhaus.org",
"short.rbl.jp",
"singular.ttk.pte.hu",
"smtp.dnsbl.sorbs.net",
"socks.dnsbl.sorbs.net",
"spam.abuse.ch",
"spam.dnsbl.anonmails.de",
"spam.dnsbl.sorbs.net",
"spam.spamrats.com",
"spambot.bls.digibase.ca",
"spamrbl.imp.ch",
"spamsources.fabel.dk",
"ubl.lashback.com",
"ubl.unsubscore.com",
"virus.rbl.jp",
"web.dnsbl.sorbs.net",
"wormrbl.imp.ch",
"xbl.spamhaus.org",
"z.mailspike.net",
"zen.spamhaus.org",
"zombie.dnsbl.sorbs.net",
    ); // Add your preferred list of DNSBL's

    $AllCount = count($dnsbl_lookup);
    $BadCount = 0;
    if($ip)
    {
        $reverse_ip = implode(".", array_reverse(explode(".", $ip)));
        foreach($dnsbl_lookup as $host)
        {
            if(checkdnsrr($reverse_ip.".".$host.".", "A"))
            {
		include 'config/db.php';
		$sql = "UPDATE bipm_iplist SET `".$host."`='Yes' WHERE ip='$ip'";
		if ($conn->query($sql) == TRUE) {
                flush_buffers();
                $BadCount++;
		}

            }
            else
            {
		include 'config/db.php';
		$sql = "UPDATE `bipm_iplist` SET `".$host."`='No' WHERE `ip`='$ip'";
		if ($conn->query($sql) === TRUE) {
                flush_buffers();
		}
            }
        }
    }
    else
    {
        echo "Empty IP!\n";
        flush_buffers();
    }

	$sql = "UPDATE `bipm_iplist` SET total='$BadCount' WHERE `ip`='$ip'";
	if ($conn->query($sql) === TRUE) {
        flush_buffers();
	}

}

$sql = "SELECT ip FROM `bipm_iplist` WHERE ip_node = '$ip_range_node'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
$ip = $row["ip"];
echo"$ip\n";
if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/",$ip) == true)
{
    dnsbllookup($ip);
    sleep(3);
}
}
}

$sql = "UPDATE settings SET value='No' WHERE id='9'";

if ($conn->query($sql) === TRUE) {
    echo "Resetting locking code record updated successfully.\n";
}

$sql1 = "UPDATE bipm SET last_date_check='$today' WHERE id='$ip_range_node'";
if ($conn->query($sql1) === TRUE) {
    echo "Last Check Record updated Successfully\n";
}

$sql1 = "SELECT count(id) AS Total FROM bipm_iplist WHERE ip_node = '$ip_range_node' AND total >= '1'";

$result1 = $conn->query($sql1);

if ($result1->num_rows > 0) {
    // output data of each row
    while($row1 = $result1->fetch_assoc()) {
	#echo"Test ".$row1["Total"]." ";
	$total = $row1["Total"];

# must replace the URL with the actual hook URL with proper token on it
$ch = curl_init('https://mattermost.crowncloud.net/hooks/5i57r84jxpfet8qtd1a75hzmzh');
# its easier doing it this way because then json_encode 
# will take care of escaping or whatever else it needs to do
$payload = [
	'username' => "BIPMBOT",
	'text' => "IP Node $ip_range Has total of $total Bad IPS",
	'channel' => "host-alerts"
];
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'payload=' . json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HEADER, 1);
$result = curl_exec($ch);
curl_close($ch);
return $result;

    }
} else {
    echo "0 results";
}


?>

