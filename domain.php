<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>SASM - Search by domain</title>
</head>

<body>
<?php include 'head.php'?>
<form name = "query" action = "domain.php" method = "get">
<table><tr><td><a href="index.php" title="Go to SASM Home - search by IP"><IMG src="img/logo_small.gif" name="logo" alt="SASM" width=109 height=35 border="0" ></a></td>
<td>

<?php
require 'function.php';

$flag=1;

if(isset($_GET['ipv']) and $_GET['ipv']!='')
    $ipv=$_GET['ipv'];
else
    $ipv='ipv4';
if($ipv!='ipv4' and $ipv!='ipv6')$ipv='ipv4';

$isip=0;
if(isset($_GET['domain']) and $_GET['domain']!='')
{	
	$domain=$_GET['domain'];
	$domain=trim($domain);
	if(parse_url($domain,PHP_URL_HOST))
		$domain=parse_url($domain,PHP_URL_HOST);
	if($domain.count('/'))
	{
		$tem=explode('/',$domain);
		if($tem[0])
		{
			$domain=$tem[0];
		}
		else
		{
			$flag=-1;
		}
	}

	echo "<p id=lm><input id=km name = \"domain\" type = \"text\" value=\"$domain\"  />";

	$realipv='';
	if(filter_var($domain,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
	{	
		$isip=1;
		$realipv='ipv6';
		$in=$domain;
	}
	elseif(filter_var($domain,FILTER_VALIDATE_IP)==true)
	{	
		$isip=1;
		$realipv='ipv4';
		$in=$domain;
	}
}
else
{	
	$flag=0;
	$in='';
	echo "<p id=lm><input id=km name = \"domain\" type = \"text\" />";
}

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
if($flag>0 and $isip==0)
{	
	if($ipv=='ipv6')
	{	
		$realipv = 'ipv6';
		$result=dns_get_record($domain,DNS_AAAA);
		if(count($result))
			$in = $result[0]['ipv6'];
		else
			$in = '0';
	}
	else
	{	
		$realipv = 'ipv4';
		$result=dns_get_record($domain,DNS_A);
		$in = $result[0]['ip'];
	}
	
}
elseif($isip==0)
{$in=0;}
if(filter_var($in,FILTER_VALIDATE_IP)==true and $flag>0)
{
	require 'ipcore.php';
}
elseif($flag==0) echo "<table class=\"fancy\" id=l><tr><td>&nbsp;Please input a web domain.</td></tr></table><br><br>\n";
else echo "<table class=\"fancy\" id=l><tr><td>&nbsp;Cannot resolve $ipv address from this domain. Please check your input.</td></tr></table><br><br>\n";
include 'tail.php';
?>
<br />
</body>
</html>

