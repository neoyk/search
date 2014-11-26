<?php
function get_real_ip()
{

    if (isset($_SERVER["HTTP_CLIENT_IP"]))
    {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
    {
        return $_SERVER["HTTP_X_FORWARDED"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
    {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED"]))
    {
        return $_SERVER["HTTP_FORWARDED"];
    }
    else
    {
        return $_SERVER["REMOTE_ADDR"];
    }
}
$ip = get_real_ip();
$adds = explode(".", $ip);
$domain=$adds[3].'.'.$adds[2].'.'.$adds[1].'.'.$adds[0].'.ip2asn.sasm4.net';
$result=dns_get_record($domain,DNS_TXT);
$as=$result[0]['txt'];
$dnserr=0;
if($as==null) $dnserr=1;
if($as!="No Record" and $as!="Error" and $dnserr!=1 )
{
	$ass = explode('as',$as);
	$asn = intval($ass[1]);
	//echo $asn;
	$iplong = ip2long($ip);
	$link = mysql_connect("localhost","root", "") or die('Connection Failure!');
	$db = mysql_select_db("webserver");
	mysql_query("set names utf8", $link);
	$sql = "select * from ipv4prefix2vantage where asn=$asn and start<=inet_aton('$ip') and end >= inet_aton('$ip')";
	//print($sql);
	$result = mysql_query($sql, $link);
	$row = mysql_fetch_array($result);
	//print_r($row);

	echo $ip.'|'.$row[7].'|'.$row[8];
}
else
{
	echo "error";
}
?>

