<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>Web Performance Plot</title>
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
echo "<h2>Web Performance Plot:</h2>\n";
require('form.php');
if(!empty($ok))
{
	if($in=="--OR--" and $correct==0)
		echo "<font color=red>Wrong Data! Please check your input!</font> <br />";
	else
	{	
		$link = mysql_connect("127.0.0.1", "root", "") or die('Connecting Failure!');
		mysql_select_db("mnt",$link);
		mysql_query("set names utf8", $link);
		mysql_query("flush tables", $link);
		$basedir = dirname(__FILE__);
		if(!empty($where))
		{
			$link2 = mysql_connect("127.0.0.1", "root", "") or die('Connecting Failure!');
			mysql_select_db("mnt",$link2);

			$result1 = mysql_query("select webdomain, asn, ip, location, id,aspath from ipv".$version."server where $where",$link);
			while($row1 = mysql_fetch_array($result1))
			{
				$id = $row1[4];
				$domain = $row1[0];
				$in3 = strtoupper($row1[1]);
				$loc = $row1[3];
				$ipaddr = $row1[2];
				$path = $row1[5];
				echo "<h3>$id, $in3 $domain, $ipaddr, $path, $loc</h3>";
				require("plot.php");
				echo "<br /><hr />\n";
			}
		}
		else
		{
			$file = fopen($basedir.'/id.list','r');
			while(!feof($file))
			{
				$line = explode(' ', fgets($file));
				$id = intval($line[0]);
				if($id==0) break;
				$pagesize = intval($line[1]);
				$result1 = mysql_query("select webdomain, asn,ip,location,aspath from ipv".$version."server where id=$id");
				$row1 = mysql_fetch_array($result1);
				$domain = $row1[0];
				$in3 = strtoupper($row1[1]);
				$loc = $row1[3];
				$ipaddr = $row1[2];
				$path = $row1[4];
				echo "<h3>$id, $in3 $domain, $ipaddr, $path, $loc, Pagesize: $pagesize B</h3>";
				require("plot.php");
				echo "<br /><hr />\n";
			}
			fclose($file);	
		}
	}
}
?>
<div align="center"><p id=b>&copy;1998-<script>clientdate=new Date();document.write(clientdate.getUTCFullYear());</script> <a href="http://www.nic.edu.cn/" target="_blank">CERNIC</a>, <a href="http://www.edu.cn/cernet_fu_wu_1325/index.shtml" target="_blank">CERNET</a>. All rights reserved. China Education and Research Network</p></div>
</body>
