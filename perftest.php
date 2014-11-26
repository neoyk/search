<html>   
<head>   
<title>SASM - Performance Test</title>   
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">   
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">   
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">   
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">   
</head>
<body>
<?php include 'head.php';?>
<p class="title">Performance Test</p><br />
<p class="subtitle">Connection status: </p><br />

<?php
require $_SERVER['DOCUMENT_ROOT']."/function.php";
require_once $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";
use GeoIp2\Database\Reader;
$ip = getRealIpAddr();
if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
{
    $ipv='IPv6';
	$asn=getASN6($ip);
}
elseif(filter_var($ip,FILTER_VALIDATE_IP)==true)
{
    $ipv='IPv4';
	$asn=getASN($ip);
}

    $link = mysql_connect("localhost","root", "") or die('Connection Failure!');
    $db = mysql_select_db("webserver");
    mysql_query("set names utf8", $link);
	$sql = "select name from asname where asn='$asn'";
    $result = mysql_query($sql, $link);
    $row = mysql_fetch_array($result);

echo "<p class=info>You are using $ipv address: <b>$ip</b>";
try
{
	$geo = new Reader($_SERVER['DOCUMENT_ROOT'].'/GeoLite2-City.mmdb');
	$record = $geo->city($ip);
	//print($record->location->latitude . "\n"); // 44.9733
	//print($record->location->longitude . "\n"); // -93.2323
	echo " from {$record->city->name} {$record->country->name} (Geolocation info comes from <a target=_blank href=\"http://www.maxmind.com/\">MaxMind</a>)";
}
catch (Exception $e){}
echo ".</p>\n";
echo "<p class=info>ISP information: AS$asn, $row[0]</p><br />\n";
//", <a target=_blank href=ip.php?myip=true>click here</a> to find out web servers in your ISP.";
echo "<p class=subtitle>Bandwidth Test:</p><br />\n";
echo "<p class=info>This test will measure your connection speed to and from our server by downloading some data from our server, and then uploading it back to the server. The test should take approximately 30 seconds to complete.</p>\n";
echo "<p class=info>The precision of the test can be affected by server load fluctuation and the number of users doing the same test.</p><br />\n";
$load = serverload();
if ($load>2)
	echo "<p class=warn>Server overload. Current load: $load, please come back later.</p><br />\n";
else
	echo "<p><a class=subnoback href=\"speedtest/download.php\">Start Test</a></p><br />\n";

echo "<p class=subtitle>Latency measurement:</p><br />\n";

list ($result, $latency) = latency($ip, $ipv);
foreach ($result as $line)
	echo "<p class=info>$line</p>\n";
echo "<br />";
//rtt min/avg/max/mdev = 2.187/2.194/2.206/0.008 ms, pipe 3

if($latency == -1)
	echo "<p class=warn>Latency Estimation failed. Your machine does not respond to ping packets.</p><br />\n";
	
if($ipv =='IPv6')
{
	$ip = padding_ipv6(ExpandIPv6Notation($ip));
	$sql = "insert into mnt.latencytest6 values('$ip', now(),$latency)";
}
else
	$sql = "insert into mnt.latencytest values('$ip', now(),$latency)";
$result = mysql_query($sql, $link);
ob_flush();flush();
/*
$KB=0;
echo "<p class=subtitle>Testing download speed, this may take upto 5 seconds... </p>";
echo "<p class=c>Note1: Download speed is estimated by calculating how many bytes transmited to your browser in 5 seconds.<br />\n The precision of the test may be affected duo to server load fluctuation and the number users doing the same test.</p><br />\n";
//echo "<br />Testing download speed, this may take upto 5 seconds... <!-";
echo "<!-";
flush();
$start = now();

while(now() - $start<5){
    echo str_pad('', 1024, '.');
	$KB=$KB+1;
	if($KB>=50)break;
    flush();
}
$deltat = now() - $start;
echo "->\n $KB KB downloaded. Your speed is ". round(8*$KB / $deltat, 3)."kbps<br /><br />\n";
echo "<p class=subtitle>Testing upload speed...</p><br />";
*/
echo "<p class=subtitle>Traceroute output:</p><br />";
if($ipv=="IPv6")
$cmd = "traceroute -6I -w1 ".$ip;
else
$cmd = "traceroute -I -w1 ".$ip;
echo "<p class=info>$cmd</p><br />\n";
$handle = popen($cmd, "r");
while(!feof($handle))
{
	$line = fgets($handle);
	echo "<p class=info>$line</p>\n";
	ob_flush();flush();
}
pclose($handle);
//echo "<p class=info>$cmd</p><br />\n";
echo "<br />";
/*echo "<p class=subtitle>Whois information:</p><br />";
$cmd = "whois ".$ip;
$result = '';
//echo "<p class=info>$cmd</p><br />\n";
$exec = exec($cmd, $result);
foreach ($result as $line)
	echo "<p class=info>$line</p>\n";
echo "<br />";
*/
echo "<p class=subtitle>Previous tests:</p><br />\n";
if($ipv=='IPv6')
{
	$ipfull = strtolower(padding_ipv6(ExpandIPv6Notation($ip)));
	$sql = "select max(downspeed), min(downspeed), max(upspeed),min(upspeed), 
		max(latency),min(latency) from mnt.speedtest6 where ip_full='$ipfull'";
}
else
{
	$sql = "select max(downspeed), min(downspeed), max(upspeed),min(upspeed), 
		max(latency),min(latency) from mnt.speedtest where ip_string='$ip'";
}
$result = mysql_query($sql, $link);
$row = mysql_fetch_array($result);
if (!is_null($row[0]))
{
	echo "<table class=cute><tr><td>&nbsp;</td><td>Download speed (kbps)&nbsp;</td><td>Upload speed (kbsp)&nbsp;</td><td>Latency (ms)&nbsp;</td><tr>\n";
	echo "<tr><td class=info>Maximum&nbsp;</td><td>$row[0]</td><td>$row[2]</td><td>$row[4]</td></tr>\n";
	echo "<tr><td class=info>Minimum&nbsp;</td><td>$row[1]</td><td>$row[3]</td><td>$row[5]</td></tr>\n";
	echo "</table>";
}
else
	echo "<p class=info>No history available.\n";
echo "<br /><br />";

echo "<p class=subtitle>Other test resources:</p><br />";
echo "<p class=info><a target=_blank href=\"http://ipv6-test.com/\">IPv6 Connection Test</a></p>\n";
echo "<p class=info><a target=_blank href=\"http://www.worldipv6launch.org/\">World IPv6 Launch</a></p>\n";
echo "<p class=info><a target=_blank href=\"http://www.speedtest.net/\">Speed test all over the Internet</a></p>\n";
echo "<br />";

include 'tail.php';
?>

</body>
</html>
