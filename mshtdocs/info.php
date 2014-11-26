<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="The Trans-Eurasia High Performance Video Conference test page.">
	<title>Conference Information Query Page</title>
    <link rel="shortcut icon" href="http://video.sasm3.net/favicon.ico" type="image/x-icon" >
	<style type="text/css">
		body {font-family:"arial";}
		a { color:blue; text-decoration: underline}
		a:hover 
		{ 
			color:blue;
			text-decoration: underline
		}
		#b {font:12px arial; color: #77c}
		#km {font-size: 18px}
		#lm {font: 16px arial;}
        #pm {text-align:left;  text-indent: 8.5em;}
	</style>

</head>

<body>
<a href="index.php">Step 1</a>
<a href="step2.php">Step 2</a>
&nbsp;Conference Information Query

<center>
<h2><font color="#084B8A">Conference Information Query</font></h2>
<?php
include('function.php');
	$id=1;$code=123456;$right=1;$full=1;$dnserr=0;
	if(isset($_GET['id']))
		$id=intval(trim($_GET['id']));
	else
		$full=0;

	if(isset($_GET['code']))
		$code=intval(trim($_GET['code']));
	else
		$full=0;

	if($full==1)
	{
		$link = mysql_connect("127.0.0.1","root", "") or die('Connecting Failure!');
    	$db = mysql_select_db("video");
    	mysql_query("set names utf8", $link);
		$sql= "select count(*) from registration where id='$id' and code='$code'";
		$result= mysql_query($sql,$link);
        $row = mysql_fetch_array($result);
		if(intval($row[0])!=1)
			$right=0;
	}
	if($right==1 and $full)
	{
		$sql= "select * from registration where id='$id' and code='$code'";
		$result= mysql_query($sql,$link);
        $row = mysql_fetch_array($result);
		echo "<form  name = \"infoquery\" action = \"info.php\" method = \"get\">\n";
		echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        echo "<tr><td><font size=+1 color=\"#084b8a\">Conference information:</font></td><td width=260>&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        echo "<tr><td width=40>id:&nbsp;&nbsp;</td><td width=160><input id=km name=id size=8 type=text value=$id ></td>\n";
        echo "<td width=40>code:</td><td width=200><input id=km name=code size=10 type=text value=$code ></td></tr></table>\n";
		echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        echo "<tr><td width=40>IP1:&nbsp;</td><td width=420>$row[1]</td></tr>\n";
        echo "<tr><td width=40>HG1:&nbsp;</td><td>$row[9]</td></tr>\n";
        echo "<tr><td width=40>IP2:&nbsp;</td><td>$row[2]</td></tr>\n";
        echo "<tr><td width=40>HG2:&nbsp;</td><td>$row[10]</td></tr>\n";
	    echo "<tr><td width=40>Type:&nbsp;</td>";	
	    $ta=array('DVTS(30mbps)','VLC(27~30mbps)','uncompressed(800mbps)');
		$offset=intval($row[3])-1;
		$vtype=$ta[$offset];
		echo "<td>$vtype</td></tr>\n";
		if(intval($row[4])) //bw
			echo "<tr><td width=40>BW:&nbsp;</td><td>$row[4]Mbps</td></tr>\n";
		else
			echo "<tr><td width=40>BW:&nbsp;</td><td>untested</td></tr>\n";
	    echo "<tr><td width=40>Time:&nbsp;</td><td>$row[5]&nbsp;to&nbsp;$row[6]</td></tr>\n";
		echo "</table>\n";
		if(intval($row[8])) //fin
		{
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        	echo "<tr><td><font size=+1 color=\"#084b8a\">Port assignment:</font></td><td width=325>&nbsp;</td></tr>\n</table>\n";
			$port1=intval($row[12]);$port11=$port1+1;
			$port2=intval($row[13]);$port22=$port2+1;
			echo "IP1 -> HG1:$port1 -> HG2:$port22 -> IP2:8000<br>\n";
			echo "IP2 -> HG2:$port2 -> HG1:$port11 -> IP1:8000<br>\n";
			echo "<p>Registration finished.</p>\n";
		}
		else
			echo "<p>Registration unfinished.</p>\n";
	}
	else
	{
		if($right>0)
		{
			echo "<p>Plesae input conference token or go <a href=\"index.php\"  \>back</a> to register.</p>\n";
			echo "<form  name = \"iptest\" action = \"info.php\" method = \"get\">\n";
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        	echo "<tr><td width=40>id:&nbsp;</td><td><input id=km name=id size=20 type=text ></td></tr>\n";
        	echo "<tr><td width=40>code:&nbsp;</td><td><input id=km name=code size=20 type=text ></td></tr>\n";
			echo "</table>\n";
			echo "<input id=lm name =ok type = submit value = Submit />\n";
			echo "</form>\n<br><br>\n";
		}
		elseif($right==0)
		{
			echo "<p>Plesae input <font color=red>correct</font> conference token or go <a href=\"index.php\"  \>back</a> to register.</p>\n";
			echo "<form  name = \"iptest\" action = \"info.php\" method = \"get\">\n";
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        	echo "<tr><td width=40>id:&nbsp;</td><td><input id=km name=id size=20 type=text ></td></tr>\n";
        	echo "<tr><td width=40>code:&nbsp;</td><td><input id=km name=code size=20 type=text ></td></tr>\n";
			echo "</table>\n";
			echo "<input id=lm name =ok type = submit value = Submit />\n";
			echo "</form>\n<br><br>\n";
		}
	}
?>
<div><p id=b>&copy;2009-2011 All rights reserved. CERNET</p></div>
</center>
</body>
</html>

