<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>SASM - Search by IP</title>
</head>

<body>
<?php
	$flag=1;
	if(isset($_GET['myip']) and $_GET['myip']=='true')
		$flag=2;
	include 'head.php';
?>
<form name = "query" action = "ip.php" method = "get">
<table><tr><td><a href="index.php" title="Go to SASM Home - search by IP"><IMG src="img/logo_small.gif" name="logo" alt="SASM" width=109 height=35 border="0" ></a></td>
<td>

<?php
require 'function.php';
if($flag==2)
{	
	$in=getRealIpAddr();
	$len=strlen($in);
	if($len<15)$len=15;
	echo "<p class=input><input id=km name = \"ip\" size=\"$len\" type = \"text\" value=\"$in\"  />";
}
elseif(isset($_GET['ip']) and $_GET['ip']!='')
{	
	$in=$_GET['ip'];
	$in=trim($in);
	$len=strlen($in);
	if($len<15)$len=15;
	echo "<p id=lm><input id=km name = \"ip\" size=\"$len\" type = \"text\" value=\"$in\"  />";
}
else
{	
	$flag=0;
	$in='';
	echo "<p id=lm><input id=km size=\"15\" name = \"ip\" type = \"text\" />";
}
$private = 0;
if(filter_var($in,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
{	
	$realipv='ipv6';
}
elseif(filter_var($in,FILTER_VALIDATE_IP)==true)
{	
	$realipv='ipv4';
	$part = explode('.',$in);
	$ipint = array();
	foreach($part as $str)
	{	
		array_push($ipint,intval($str));
	}
	if( $ipint[0]==10 or $ipint[0]==0 or $ipint[0] == 127 or $ipint[0]>=224 or ($ipint[0]==192 and $ipint[1]==168) or ($ipint[0]==172 and $ipint[1]>=16 and $ipint[1]<=31))
	{	
		$private = 1;
	}
}

if(isset($_GET['ipv']) and $_GET['ipv']!='')
    $ipv=$_GET['ipv'];
else
    $ipv=$realipv;
if($ipv!='ipv4' and $ipv!='ipv6')$ipv='ipv4';

if($ipv=='ipv6')
    echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv6</option><option>ipv4</option></select>&nbsp;\n";
else
    echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv4</option><option>ipv6</option></select>&nbsp;\n";

	if(isset($_GET['page']) and $_GET['page']!='')
	{	$page=$_GET['page'];
		$page=intval($page);
		if($page==0)
			$page=1;
	}
	else
		$page=1;
	
?>
<input id=lm name = "ok" type = "submit" value = "Search" />
</p></td></tr></table>
</form>
<br />

<?php
if(filter_var($in,FILTER_VALIDATE_IP)==true and $flag>0 and !$private)
{	
	require 'ipcore.php';
}
elseif($flag==0) echo "<table class=\"fancy\" ><tr><td>&nbsp;Please input an IP address.</td></tr></table><br><br>\n";
elseif($private) echo "<table class=\"fancy\"><tr><td>&nbsp;Please input a unicast address!</td></tr></table><br><br>\n";
else echo "<table class=\"fancy\"><tr><td>&nbsp;Invalid address!</td></tr></table><br><br>\n";
include 'tail.php';
?>
<br />
</body>
</html>

