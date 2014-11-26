<?php
echo exec('whoami');
if(isset($_GET['bw']) and isset($_GET['msg']) )
{ 
	$bw = $_GET['bw'];
	$msg = $_GET['msg'];
    $link = mysql_connect("localhost","root", "") or die('Connection Failure!');
    $db = mysql_select_db("mnt");
    mysql_query("set names utf8", $link);
	$sql = "insert into d80 values('iperf',$bw, now(), '$msg')";
    $result = mysql_query($sql, $link);
}

system("/var/www/html/80ab.py", $retval);
system("/var/www/html/80wget.py", $retval);
?>

