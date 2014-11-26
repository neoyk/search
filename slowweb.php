<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>SASM - Slow website report</title>
</head>

<body>
<?php include 'head.php'?>
<form name = "query" action = "slowweb.php" method = "get">
<table><tr><td><a href="index.php" title="Go to SASM Home - search by IP"><IMG src="img/logo_small.gif" name="logo" alt="SASM" width=109 height=35 border="0" ></a></td>
<td>

<?php
require 'function.php';

$flag=1;
$basedir = dirname(__FILE__);
if(isset($_GET['ipv']) and $_GET['ipv']!='')
    $ipv=$_GET['ipv'];
else
    $ipv='ipv4';
if($ipv!='ipv4' and $ipv!='ipv6')$ipv='ipv4';

if(isset($_GET['domain']) and $_GET['domain']!='')
{	
    $fulldir=trim($_GET['domain']);
    //print_r(parse_url($in));
    //print_r(parse_url($domain,PHP_URL_HOST));
    if(parse_url($fulldir,PHP_URL_HOST))
        $domain=parse_url($fulldir,PHP_URL_HOST);
    else
        $domain=$fulldir;
    $domain=trim($domain,'.');
    if($domain.count('/'))
    {
        $tem=explode('/',$domain);
        if($tem[0])
            $domain=$tem[0];
    }
    $tem=explode($domain,$fulldir);
    $dir=trim($tem[1],'.');
    if($dir == null)
        $dir='/';
    //echo $domain.'<br>'.$dir.'<br>'."\n";

	if($ipv=='ipv6')
	{
		$result=dns_get_record($domain,DNS_AAAA);
		if(count($result))
			$in = $result[0]['ipv6'];
		else
			$in = '0';
	}
	else
	{	
		$result=dns_get_record($domain,DNS_A);
		$in = $result[0]['ip'];
	}
	echo "<p m><input id=km name = \"domain\" type = \"text\" value=\"$fulldir\"  />";

}
else
{	
	$flag=0;
	$in='0';
	echo "<p id=lm><input id=km name = \"domain\" type = \"text\" />";
}

if($ipv=='ipv6')
    echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv6</option><option>ipv4</option></select>&nbsp;\n";
else
    echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv4</option><option>ipv6</option></select>&nbsp;\n";

?>
<input id=lm name = "ok" type = "submit" value = "Submit" />
</p></td></tr></table>
</form>
<br />

<?php
if($flag==0)
{
	echo "<table class=\"fancy\" ><tr><td>&nbsp;Please input the domain of the website which is slow to connect. Donot input IP address directly!</td></tr></table><br><br>\n";
	exit();
}
if(filter_var($in,FILTER_VALIDATE_IP)!=true)
	echo "<table class=\"fancy\" ><tr><td>Cannot resolve $ipv address from this domain. Please check your input.</td></tr></table><br><br>\n";
else
{
	$name=calcutime();
	if( $ipv=='ipv4' )
	{
		$as = getASN($in);
		$cmd='wget -4 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1" -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$fulldir;
		$cmd2='wget -4 -t 1 -T 10 '.$fulldir;
	}
	else
	{
		$as = getASN6($in);
		$cmd='wget -6 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"  -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$fulldir ;
		$cmd2='wget -6 -t 1 -T 10 '.$fulldir;
	}
	$link = mysql_connect("localhost","root", "") or die('Connecting Failure!'); 
	$db = mysql_select_db("webserver");  
	mysql_query("set names utf8", $link);
	if($as!=-1)
	{	
		$sql3="select name from asname where asn='AS$as'";
    	$result3 = mysql_query($sql3, $link); 
		$row3 = mysql_fetch_array($result3);
		echo "<table class=\"fancy\"><tr><td>";
		echo "$domain ($in) belongs to <b>AS$as</b> $row3[0]</td>\n";
		echo "</tr></table><br />\n";
	}
	else
		echo "<table class=\"fancy\" ><tr><td>&nbsp;No AS information available.</td></tr></table><br><br>\n";
	ob_flush();flush();
	$ans=`$cmd`;
	echo "<p class=subtitle>Bandwidth Test:</p><br />\n";
	echo '<p id=lm>'.$cmd2.'</p><br />';

	$file= fopen("/tmp/".$name.".txt", "r");
	if($file==0)
    	echo "<br>fopen error!<br>";
	else
	{
		echo '<p id=lm>';
		while (!feof($file))
		{
		    $line = fgets($file);
		    echo $line.'<br />';
		}
		fclose($file);
		echo "</p>";
		
		$cmd="python $basedir/pywget.py ".$name." >/tmp/".$name."bw.txt";
		//echo $cmd;
		$ans=`$cmd`;
		$file= fopen("/tmp/".$name."bw.txt", "r");
		$pagesize = intval(fgets($file));
		$bw = intval(fgets($file));
		$ip = fgets($file);
		$ip = preg_replace('/\r?\n$/', '', $ip);  
		$cmd='rm -f /tmp/'.$name.'*';
		//echo $cmd;
		$ans=`$cmd`;
		
		if($pagesize>0 and $bw>0)
		{	
			list($result, $latency) = latency($ip,$ipv);
			echo "<p class=subtitle>Latency measurement:</p><br />\n";
			foreach ($result as $line)
				echo "<p class=info>$line</p>\n";
			echo "<br />";
			if($latency == -1)
				echo "<p class=warn>Latency Estimation failed. This website does not respond to ping packets.</p><br />\n";
    		echo "<p class=subtitle>Database Entry:</p><br />\n";
			if( $ipv=='ipv4' )
			{ 
				$as = getASN($ip);
				$sql2 = "select count(*) from ipv4slow where ip = '$ip' and webdomain = '$domain'";
				$result2 = mysql_query($sql2, $link);
				$row2 = mysql_fetch_array($result2);
				if($row2[0])
				{	
					$sql2 = "update ipv4slow set latency = $latency, dnsdate=now(), bw = $bw, occurance = occurance+1 where ip = '$ip' and webdomain = '$domain'";
					echo "<p id=lm>We have already rerorded this website:</p><br>\n";
				}
				else
				{	$sql2 = "insert into ipv4slow values(null, '$ip','$domain','$dir',$as,inet_aton(ip),$pagesize,$bw,$latency,now(),1)";
					echo "<p id=lm>Newly added entry is listed as follows:</p><br>\n";
				}
			}
			else
			{
				$as = getASN6($ip);
				$ipfull = padding_ipv6(ExpandIPv6Notation($ip));
				$sql2 = "select count(*) from ipv6slow where ipfull = '$ipfull' and webdomain = '$domain'";
				$result2 = mysql_query($sql2, $link);
				$row2 = mysql_fetch_array($result2);
				if($row2[0])
				{	
					$sql2 = "update ipv6slow set latency = $latency, dnsdate=now(), bw = $bw, occurance = occurance+1 where ipfull = '$ipfull' and webdomain = '$domain'";
					echo "<p id=lm>We have already rerorded this website:</p><br>\n";
				}
				else
				{	$sql2 = "insert into ipv6slow values(null, '$ip','$domain','$dir',$as,'$ipfull',$pagesize,$bw,$latency,now(),1)";
					echo "<p id=lm>Newly added entry is listed as follows:</p><br>\n";
				}
			}
			$result2 = mysql_query($sql2, $link);
			echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
            echo "<tr><td id=lm>&nbsp;AS number&nbsp;</td><td id=lm>&nbsp;IP address&nbsp;</td><td id=lm>&nbsp;domain&nbsp;</td><td id=lm>&nbsp;directory&nbsp;</td><td id=lm>&nbsp;Pagesize&nbsp;</td><td id=lm>&nbsp;Download speed&nbsp;</td><td id=lm>&nbsp;Latency&nbsp;</td></tr>\n";
            echo "<tr><td id=lm>&nbsp;AS$as</td><td id=lm>&nbsp;$ip&nbsp;</td><td id=lm>&nbsp;$domain&nbsp;</td><td id=lm>&nbsp;$dir&nbsp;</td><td id=lm>&nbsp;$pagesize&nbsp;B&nbsp;</td><td id=lm>&nbsp;$bw B/s&nbsp;</td><td id=lm>&nbsp;$latency ms&nbsp;</td></tr>\n</table>\n<br>";
			//update client IP
			$client = getRealIpAddr();
			if(filter_var($client,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
			    $realipv=6;
			else
			    $realipv=4;
			if( $ipv=='ipv4' )
			{ 
				$sql2 = "select id from ipv4slow where ip = '$ip' and webdomain = '$domain'";
				$result2 = mysql_query($sql2, $link);
				$row2 = mysql_fetch_array($result2);
				$sql2 = "replace into ipv4slowUserIP values($row2[0], '$client', $realipv)";
				mysql_query($sql2, $link);
			}
			else			
			{ 
				$sql2 = "select id from ipv6slow where ipfull = '$ipfull' and webdomain = '$domain'";
				$result2 = mysql_query($sql2, $link);
				$row2 = mysql_fetch_array($result2);
				$sql2 = "replace into ipv6slowUserIP values($row2[0], '$client', $realipv)";
				mysql_query($sql2, $link);
			}			
		}
		else
			echo "<p id=lm>This is not a valid web server, please check your input.</p><br>";
	}
}
include 'tail.php';
?>
<br />
</body>
</html>

