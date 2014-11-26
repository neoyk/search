<?php
$link = mysql_connect("localhost","root", "") or die('Connection Failure!'); 
$db = mysql_select_db("webserver");  
$sql3="select name from asname where asn='$asn'";
$result3 = mysql_query($sql3, $link); 
$row3 = mysql_fetch_array($result3);
echo "<table><tr><td id=lm>AS information:</td></tr><tr><td id=lm>AS&nbsp;Name: $row3[0]</td></tr>\n";
if($callpage != 'as.php')
{
	if($realipv =='ipv4')
	{
		$cmd = "/var/www/html/quagga-com 127.0.0.1 bgpd \"sh ip bgp $in\" |grep -B1 \"202.112.60.243\"";
		//$cmd = "whoami";
		exec($cmd,$aspath);
		echo "<tr><td id=lm>AS Path: ".$aspath[0]."</td></tr>";
	}
	if($realipv =='ipv6')
	{
		$cmd = "/var/www/html/quagga-com 127.0.0.1 bgpd \"sh ipv6 bgp $in\" |grep -B1 \"2001:250:0:1::3 \"";
		exec($cmd,$aspath);
		echo "<tr><td id=lm> AS Path: ".$aspath[0]."</td></tr>";
	}
}
?>
