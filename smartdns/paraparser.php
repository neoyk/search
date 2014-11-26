<?php
error_reporting(E_WARNING);

$link = mysql_connect("115.25.86.2", "pathperf", "perf@FIT4204!") or die('Connecting Failure!');
$db = mysql_select_db("mnt");
mysql_query("set names utf8", $link);

$para = array();
if($_GET['bandwidth'])
		array_push($para, 'bandwidth');
if($_GET['latency']) 
		array_push($para, 'latency');
if($_GET['pagesize']) 
		array_push($para, 'pagesize');
if(count($para)==0 ) 
		array_push($para, 'bandwidth');

$kid=intval($_GET['kid']); if($kid == null)  $kid = 0;

$where=$_GET['where'];

$in=$_GET['xaxis'];	if(strlen($in)==0)	$in="Month";

$in2=$_GET['yaxis'];	if(strlen($in2)==0)	$in2="Auto";

$limit=$_GET['limit'];	$limit=intval($limit);	if($limit<=0)	$limit=1;

$in4=$_GET['min'];	$in4=intval($in4);	if(strlen($in4)==0)	$in4=0;

$in5=$_GET['max'];	$in5=intval($in5);	if(strlen($in5)==0)	$in5=0;

$in6=$_GET['xzoom'];	if(strlen($in6)==0 or $in6<0.5 or $in6>3)	$in6=1;

$in7=$_GET['yzoom'];	if(strlen($in7)==0 or $in7<0.5 or $in7>3)	$in7=1;

$in8=$_GET['color'];	if(strlen($in8)==0)	$in8="auto";

date_default_timezone_set('Asia/Chongqing');
$showdate=intval(date("Ymd"));
$correct=1;

$t1=$_GET['time1'];	$t1=intval($t1);	if($t1<20140616 or $t1>$showdate)	$t1=20140616;

$d=$t1%100;
$t=$t1/100;
$m=$t%100;
$y=intval($t/100);
if(!checkdate($m,$d,$y))$correct=0;
//echo "t1=",$t1," d=",$d," m=",$m," y=",$y," correct=",$correct,"<br />";

$t2=$_GET['time2'];	$t2=intval($t2);	if($t2>$showdate or $t2<$t1)	$t2=$showdate;
$d=$t2%100;
$t=$t2/100;
$m=$t%100;
$y=intval($t/100);
if(!checkdate($m,$d,$y))$correct=0;
$ok = $_GET['ok'];
?>
