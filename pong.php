<?php
if(isset($_GET['ip']) and isset($_GET['vantage']) and isset($_GET['avg']) and isset($_GET['list']) )
{ 
	$ip = $_GET['ip'];
	$vantage = $_GET['vantage'];
	$avg = $_GET['avg'];
	$list = $_GET['list'];
    if(filter_var($ip,FILTER_VALIDATE_IP) and filter_var($vantage,FILTER_VALIDATE_IP))
	{
		$link = mysql_connect("localhost","root", "") or die('Connection Failure!');
    	$db = mysql_select_db("mnt");
    	mysql_query("set names utf8", $link);
		$sql = "replace into proximity_rtt values('$ip', '$vantage',$avg, '$list', now())";
    	$result = mysql_query($sql, $link);
	}
}

if(isset($_GET['ip']) and isset($_GET['dnsip']) and isset($_GET['avg']) and isset($_GET['list']) )
{ 
	$ip = $_GET['ip'];
	$dnsip = $_GET['dnsip'];
	$avg = $_GET['avg'];
	$list = $_GET['list'];
    if(filter_var($ip,FILTER_VALIDATE_IP) and filter_var($dnsip, FILTER_VALIDATE_IP))
	{
		$link = mysql_connect("localhost","root", "") or die('Connection Failure!');
    	$db = mysql_select_db("mnt");
    	mysql_query("set names utf8", $link);
		$sql = "replace into proximity_dns values('$ip', '$dnsip',$avg, '$list', now())";
    	$result = mysql_query($sql, $link);
	}
}
if(isset($_GET['ip']) and isset($_GET['vantage_domain']) and isset($_GET['bw']) and isset($_GET['msg']) )
{ 
	$ip = $_GET['ip'];
	$vantage = $_GET['vantage_domain'];
	$bw = $_GET['bw'];
	$msg = $_GET['msg'];
    if(filter_var($ip,FILTER_VALIDATE_IP) )
	{
		$link = mysql_connect("localhost","root", "") or die('Connection Failure!');
    	$db = mysql_select_db("mnt");
    	mysql_query("set names utf8", $link);
		$sql = "replace into proximity_bw values('$ip', '$vantage',$bw, '$msg', now())";
    	$result = mysql_query($sql, $link);
	}
}
?>

