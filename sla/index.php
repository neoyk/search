<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>SLA monitor - Web Server Performance Plot</title>
    <style type="text/css">
    <!--
 		#b {font:12px arial; padding-top: 4px; height: 30px; color: #77c}
		body {font-family:"arial";}
		a:link { color:blue; }
        a:visited { color:navy; }
        a:hover { color:#CB092C;}
        a:active { cursor: hand;}
    -->
    </style>

</head>

<body>

<?php
	
	require('paraparser.php');
	$result0 = mysql_query("select count(*) from ipv".$version."server where asn='AS$in3'", $link);
	$row0 = mysql_fetch_array($result0);
	$count = $row0[0];
	if($count==0){$in3=4538;$count=3;}
	if($count<$limit)$limit=$count;
	$offset=$limit-1;
	
	if($kid != 0) 
		$id = $kid;
	else
	{
		$result1 = mysql_query("select id from ipv".$version."server where asn='AS$in3' limit $offset,1", $link);
		$row1 = mysql_fetch_array($result1);
		$id=$row1[0];
	}	
	$result2 = mysql_query("select webdomain, upper(asn),ip,location,aspath from ipv".$version."server where id=$id", $link);
	$row2 = mysql_fetch_array($result2);
	$domain = $row2[0];
	$asn = $row2[1];
	$loc = $row2[3];
	$ipaddr = $row2[2];
	$aspath = $row2[4];
	echo "<h3>$id $asn $domain, $ipaddr, $aspath, Location: $loc</h3>";

	
	if($in=="--OR--" and $correct==0)
		echo "<font color=red>Wrong Data! Please check your input!</font> <br />";
	else
		require('plot.php');
	echo "<br />Replot Test Results:<br />";
	require("form.php");
?>
<br />
<div align="center"><p id=b>&copy;1998-<script>clientdate=new Date();document.write(clientdate.getUTCFullYear());</script> <a href="http://www.nic.edu.cn/" target="_blank">CERNIC</a>, <a href="http://www.edu.cn/cernet_fu_wu_1325/index.shtml" target="_blank">CERNET</a>. All rights reserved. China Education and Research Network</p></div>
</body>
