<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>Smart DNS Demo</title>
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
	$result0 = mysql_query("select count(*) from ipv4dns ", $link);
	$row0 = mysql_fetch_array($result0);
	$count = $row0[0];
	if($count<$limit)$limit=$count;
	$id = $limit;
	$result2 = mysql_query("select webdomain, directory from ipv4dns where id=$id", $link);
	$row2 = mysql_fetch_array($result2);
	echo "<h3>$id $row2[0]$row2[1]</h3>";

	
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
