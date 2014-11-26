<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="The Trans-Eurasia High Performance Video Conference registration and test page.">
	<title>Step 1: High Performance Video Conference registration</title>
	<link rel="shortcut icon" href="http://video.sasm3.net/favicon.ico" type="image/x-icon" >
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
Step 1: Conference  Registration 
&nbsp;<a href="step2.php">Step 2</a>
<a href="info.php">Conf Info Query</a>
<center>
<h2 class=hl>Video Conference Registration</h2>
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse" bgcolor="#FFFFFF" bordercolor="#111111">
<form  name = "registration" action = "index.php" method = "post">
<?php
include('function.php');

$link = mysql_connect("127.0.0.1","root", "") or die('Connecting Failure!');
$db = mysql_select_db("video");
mysql_query("set names utf8", $link);

$clientip=getRealIpAddr();
$ad1='::1';$ad2='::1';$right=1;$full=1;
if(isset($_POST['id1']))
{
	$id1=trim($_POST['id1']);
 	echo "<tr><td width=35>ID1:&nbsp;</td><td align=\"left\"><input class=km name=id1 size=40 type=text value=$id1 >\n";
	echo "</td></tr>\n";
}
else
{
	echo "<tr><td width=35>ID1:&nbsp;</td><td align=\"left\"><input class=km name=id1 size=40 type=text ></td></tr>\n";
	$full=0;
}

if(isset($_POST['ad1']))
{
	$ad1=trim($_POST['ad1']);
 	echo "<tr><td width=35>IP1:&nbsp;</td><td align=\"left\"><input class=km name=ad1 size=40 type=text value=$ad1 >\n";
	if(filter_var($ad1,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)!=true) 
	{
		$right=0;
		echo "<i class=redsi>Invalid IPv6 address!</i></td></tr>\n";
	}
	else
		echo "</td></tr>\n";
}
else
{
	echo "<tr><td width=35>IP1:&nbsp;</td><td align=\"left\"><input class=km name=ad1 size=40 type=text value=$clientip ></td></tr>\n";
	$full=0;
}

if(isset($_POST['id2']))
{
	$id1=trim($_POST['id2']);
 	echo "<tr><td width=35>ID2:&nbsp;</td><td align=\"left\"><input class=km name=id2 size=40 type=text value=$id2 >\n";
	echo "</td></tr>\n";
}
else
{
	echo "<tr><td width=35>ID2:&nbsp;</td><td align=\"left\"><input class=km name=id2 size=40 type=text ></td></tr>\n";
	$full=0;
}

if(isset($_POST['ad2']))
{
	$ad2=trim($_POST['ad2']);
 	echo "<tr><td width=35>IP2:&nbsp;</td><td align=\"left\"><input class=km name=ad2 size=40 type=text value=$ad2 >\n";
	if(filter_var($ad2,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)!=true) 
	{
		$right=0;
		echo "<i class=redsi>Invalid IPv6 address!</i></td></tr>\n";
	}
	else
	{
		if($full and $right)
		{
			$ipl1=ExpandIPv6Notation($ad1);
			$ipf1=padding_ipv6($ipl1);
			$ipl2=ExpandIPv6Notation($ad2);
			$ipf2=padding_ipv6($ipl2);
			if($ipf1==$ipf2)
			{
				$right=0;
				echo "<i class=redsi>IP address must be different!</i></td></tr>\n";
			}
			else
				echo "</td></tr>";
		}
		else
			echo "</td></tr>";
	}
}
else
{
	echo "<tr><td width=35>IP2:&nbsp;</td><td align=\"left\"><input class=km name=ad2 size=40 type=text ></td></tr>\n";
	$full=0;
}

echo "<tr><td width=35>&nbsp;</td><td align=\"left\"><i class=graysi>Input IDs and IP addresses of the two conference participants.</i></td></tr>\n";
$clientip=getRealIpAddr();
//echo "<tr><td width=40>&nbsp;</td><td><small class=grays>Your IP address: $clientip</small></td></tr>\n";
echo "<tr><td width=35>Type:&nbsp;</td><td align=\"left\">\n";
echo "<select name=tp>\n";
$ta=array('DVTS(30mbps)','VLC(27~30mbps)','uncompressed(800mbps)');
$tpr=1;
$vtype=1;
if(isset($_POST['tp']))
{
	$tp=trim($_POST['tp']);
	if(in_array($tp,$ta)==0)
		$tpr=0;
}
else 
{
	$tp='DVTS(30mbps)';
	$full=0;
}

echo "<option selected=selected>$tp</option>";
foreach($ta as $temp)
	if($temp!=$tp)
		$vtype=$vtype+1;
	else 
		break;

foreach($ta as $temp)
	if($temp!=$tp)
		echo "<option>$temp</option>\n";
echo "</select></td>\n";
if($tpr==0)
{
	$right=0;
	echo "<td><i class=redsi>Invalid video type!</i></td></tr>\n";
}
else
	echo "</tr>";
echo "<tr><td width=35>&nbsp;</td><td align=\"left\"><i class=graysi>Select video type.</i></td></tr>\n";
echo "<tr><td width=35>Time:&nbsp;</td><td align=\"left\">Starts at&nbsp\n";
   
date_default_timezone_set('Asia/Chongqing');
$s0=date("Y-m-d H:i:s");
$y=intval(date('Y'));
if(isset($_POST['s1y']))
{
	$s1y=intval(trim($_POST['s1y']));
	echo "<input class=right18 name=s1y style= \"width:45px;\" size=3 maxlength=4 type=text value=\"$s1y\" >\n";
	if($y>$s1y)
		$right=0;
}
else
{
	echo "<input class=right18 name=s1y style= \"width:45px;\" size=3 maxlength=4 type=text >\n";
	$full=0;
}
echo '-';
if(isset($_POST['s1m']))
{
	$s1m=intval(trim($_POST['s1m']));
	echo "<input class=right18 name=s1m style= \"width:22px;\" size=1 maxlength=2 type=text value=\"$s1m\" >\n";
	if($s1m<1 or $s1m>12)
		$right=0;
}
else
{
	echo "<input class=right18 name=s1m style= \"width:22px;\" size=1 maxlength=2 type=text >\n";
	$full=0;
}
echo '-';
if(isset($_POST['s1d']))
{
	$s1d=intval(trim($_POST['s1d']));
	echo "<input class=right18 name=s1d style= \"width:22px;\" size=1 maxlength=2 type=text value=\"$s1d\" >\n";
	if($s1d<1 or $s1d>31)
		$right=0;
}
else
{
	echo "<input class=right18 name=s1d style= \"width:22px;\" size=1 maxlength=2 type=text >\n";
	$full=0;
}
echo "&nbsp;";
if(isset($_POST['s1h']))
{
	$s1h=intval(trim($_POST['s1h']));
	echo "<input class=right18 name=s1h style= \"width:22px;\" size=1 maxlength=2 type=text value=\"$s1h\" >\n";
	if($s1h<0 or $s1h>23)
		$right=0;
}
else
{
	echo "<input class=right18 name=s1h style= \"width:22px;\" size=1 maxlength=2 type=text >\n";
	$full=0;
}
echo ":";
if(isset($_POST['s1f']))
{
	$s1f=intval(trim($_POST['s1f']));
	echo "<input class=right18 name=s1f style= \"width:22px;\" size=1 maxlength=2 type=text value=\"$s1f\" >\n";
	if($s1f<0 or $s1f>59)
		$right=0;
}
else
{
	echo "<input class=right18 name=s1f style= \"width:22px;\" size=1 maxlength=2 type=text >\n";
	$full=0;
}
echo ":00,&nbsp;";
if($full)
{
	$s1=$s1y.'-'.$s1m.'-'.$s1d.' '.$s1h.':'.$s1f.':00';
	if(checktime($s1)==0)
	{	
		$right=0;
		echo "<i class=redsi>Invalid time!</i>\n";
	}
}
echo "Lasts&nbsp;";
if(isset($_POST['h2']))
{
	$h2=floatval(trim($_POST['h2']));
	echo "<input class=km name=h2 style= \"width:40px;\" size=2 type=text value=\"$h2\" >&nbsp;hour(s)\n";
	if($h2<=0 )
	{
		$right=0;
		echo "<i class=redsi>Invalid time!</i>\n";
	}
	elseif($full and $right)
	{
		$timestamp=strtotime($s1)+3600*$h2;
		$s2=date("Y-m-d H:i:s",$timestamp);
	}
}
else
{
	echo "<input class=km name=h2 style= \"width:40px;\" size=2 type=text >&nbsp;hour(s)\n";
	$full=0;
}

echo "</td></tr>";
if($right==1 and $full==1 and ($h2<0.01 or $h2>5))//1h<time<5h
{
	echo "<tr><td width=35>&nbsp;</td><td><i class=redsi>The meeting should last more than 1 hour and less than 5 hours.</i></td></tr>\n";
	$right=0;
}

echo "<tr><td width=35>&nbsp;</td><td align=\"left\"><i class=grays>Start time and duration of the conference. (Current time: $s0)</i></td></tr>\n</table>\n";

if($right and $full)
{	
	//$right=0; //just for debug
	$sava=1;
	echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
   	echo "<tr><td class=shl>IP address test results:</td><td width=320>&nbsp;</td></tr>\n";
	echo "</table>\n";
	echo "<table border=1 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#CCCCCC\">\n";
	echo "<tr><td>&nbsp;IPv6 address&nbsp;</td><td>&nbsp;AS number&nbsp;</td><td>&nbsp;valid&nbsp;</td></tr>\n";
	$asn1=iptoasn6($ad1);
	echo "<tr><td>$ad1</td><td>&nbsp;$asn1&nbsp;</td>";
	if($id1!='internal' and checkip($ad1,$id1)>=0)
		echo "<td>&nbsp;yes&nbsp;</td></tr>\n";
	elseif($id1=='internal' and checkipinternal($ad1)>=0)
		echo "<td>&nbsp;yes&nbsp;</td></tr>\n";
	else
	{
		$right=$sava=0;
		echo "<td class=red>&nbsp;no&nbsp;</td></tr>\n";
	}
	$asn2=iptoasn6($ad2);
	echo "<tr><td>$ad2</td><td>&nbsp;$asn2&nbsp;</td>";
	if($id2!='internal' and checkip($ad2,$id2)>=0)
		echo "<td>&nbsp;yes&nbsp;</td></tr>\n";
	elseif($id2=='internal' and checkipinternal($ad2)>=0)
		echo "<td>&nbsp;yes&nbsp;</td></tr>\n";
	else
	{
		$right=$sava=0;
		echo "<td>&nbsp;<font color=red>no</font>&nbsp;</td></tr>\n";
	}
	echo "</table>\n";
	if($sava==0)
        echo "<p>IP address test <font color=red>failed</font>. Please try again.</p>\n";
}

if($right and $full)
{	
	//$right=0;
	echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
   	echo "<tr><td class=shl>Home Gateway test results:</td><td width=270>&nbsp;</td></tr>\n";
	echo "</table>\n";
	echo "<table border=1 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#CCCCCC\">\n";
	echo "<tr><td>&nbsp;IPv6 address&nbsp;</td><td>&nbsp;Gateway IP address&nbsp;</td><td>&nbsp;valid&nbsp;</td></tr>\n";
	$hg1=hgw($ad1);
	$hg2=hgw($ad2);
	echo "<tr><td>$ad1</td>";
	if($hg1=='::1')
	{
		$right=0;
		echo "<td class=red>&nbsp;NULL&nbsp;</td><td>&nbsp;&nbsp;</td></tr>";
		echo "<tr><td>$ad2</td>";
		echo "<td class=red>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td></tr>";
	}
	elseif($hg2=="::1")
	{
		$right=0;
		echo "<td>&nbsp;$hg1&nbsp;</td><td>&nbsp;&nbsp;</td></tr>";
		echo "<tr><td>$ad2</td>";
		echo "<td class=red>&nbsp;NULL&nbsp;</td><td>&nbsp;&nbsp;</td></tr>";
	}
	else
	{
		//genate id and code
		srand((double)microtime()*1000000);
		$code = rand(100000,999999);
		$link = mysql_connect("127.0.0.1","root", "") or die('Connecting Failure!');
	   	$db = mysql_select_db("video");
	   	mysql_query("set names utf8", $link);
		$sql = "insert into registration values('','$ad1','$ad2','$vtype','0','$s1','$s2','$code','0','$hg1','$hg2','1','0','0')";
		$result = mysql_query($sql, $link);
		$sql2= "select id from registration where ip1='$ad1' and code='$code' order by id desc limit 1";
		$result2= mysql_query($sql2,$link);
		$row2 = mysql_fetch_array($result2);
		$id=$row2[0];

		echo "<td>&nbsp;$hg1&nbsp;</td>";

		$parm="id=$id&code=$code&ip1=$ad1&ip2=$ad2&vtype=$vtype&stime=$s1&etime=$s2&hg1=$hg1&hg2=$hg2";
		$trans=array(":" => "%3A", " " => "+");
		$parm=strtr($parm, $trans);
		//echo "http://[$hg1]/hgwconf.php?".$parm;
		$file=fopen("http://[$hg1]/hgwconf.php?".$parm, "r");
		$line=fgets($file, 100); 
		//echo $line;
		if(trim($line)=="conf info received") 
			echo "<td>&nbsp;yes&nbsp;</td></tr>\n";
		else
		{
			echo "<td class=red>no<p class=redsi>HG1 response: $line</p></td></tr>\n";
			$right=0;
		}
		fclose($file);
		echo "<tr><td>$ad2</td><td>&nbsp;$hg2&nbsp;</td>";
		$file=fopen("http://[$hg2]/hgwconf.php?".$parm, "r");
		$line=fgets($file, 100); 
		//echo $line;
		if(trim($line)=="conf info received") 
			echo "<td>&nbsp;yes&nbsp;</td></tr>\n";
		else
		{
			echo "<td class=red>no<p class=redsi>HG2 response: $line</p></td></tr>\n";
			$right=0;
		}
		fclose($file);
	}
	echo "</table>\n";
	if($right==0)
        echo "<p>Home Gateway test <font color=red>failed</font>. Please try again.</p>\n";
}

if($full==1 and $right==1)
{
	echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
	echo "<tr><td><font size=+1 color=\"#084b8a\">Step 1 accomplished. Conference token:</font></td><td width=140>&nbsp;</td></tr>\n";
	echo "</table>\n";
	echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
	echo "<tr><td width=35>id:&nbsp;</td><td width=440>$row2[0]</td></tr>\n";
	echo "<tr><td width=35>code:</td><td>$code</td></tr>\n";
	echo "</table>\n";

	echo "<br><a href=\"step2.php?id=$row2[0]&code=$code\" >Step 2: Bandwidth test>></a><br>\n";
}
else
	echo "<input id=lm name =ok type = submit value = Submit />\n";

?>
<br><br>
<div><p id=b>&copy;2009-2011 All rights reserved. CERNET</p></div>
</center>
</body>
</html>
