<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>Pathperf - Update database</title>
</head>

<body>
<?php include 'head.php';?>
<form name = "domain" action = "update.php" method = "get">
<table><tr><td><a href="index.php" title="Go to SASM Home - search by IP"><IMG src="img/logo_small.gif" name="logo" alt="SASM" width=109 height=35 border="0" ></a></td>
<td>

<?php
require 'function.php';
//local directory
$basedir = dirname(__FILE__);
// read parameters
$dnserr=0;
if(isset($_GET['refresh']))
	$refresh=1;
else
	$refresh=0;
if(isset($_GET['domain']) and $_GET['domain']!='')
{	
	$flag=1;
	$domain=trim($_GET['domain']);
	//print_r(parse_url($in));
	//print_r(parse_url($domain,PHP_URL_HOST));
	if(parse_url($domain,PHP_URL_HOST))
		$in=parse_url($domain,PHP_URL_HOST);
	else
		$in=$domain;
	$in=trim($in,'.');
	if($in.count('/'))
	{
		$tem=explode('/',$in);
		if($tem[0])
		{
			$in=$tem[0];
		}
		else
		{
			$flag=-1;
		}
	}
	$tem=explode($in,$domain);
	//print_r($tem);
	$dir=trim($tem[1],'.');
	if($dir == null)
		$dir='/';
	//echo $in.'<br>'.$dir.'<br>'."\n";
}
else
{       
	$flag=0;
	$in=0;$dir='';
}

if(isset($_GET['ipv']) and ($_GET['ipv']=='ipv4' or $_GET['ipv']=='ipv6' or $_GET['ipv']=='both'))
	$ipv=$_GET['ipv'];
else
	$ipv='both';

if(filter_var($in,FILTER_VALIDATE_IP)==true and filter_var($in,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==false) 
{
	$isip=4;
	$ipv='ipv4';
}
elseif(filter_var($in,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
{
	$isip=6;
	$ipv='ipv6';
}
else
	$isip=0;
//echo "$ipv<br>$isip";
$len=strlen($in.$dir);
if($flag>0)
{
	if($len<35)
		echo "<p id=lm><input id=km name = \"domain\" type = \"text\" size=\"$len\"value=\"$in$dir\"  />\n";
	elseif($len>35 and $len<46)
		echo "<p id=lm><input id=lm name = \"domain\" type = \"text\" size=\"$len\"value=\"$in$dir\"  />\n";
	else
		echo "<p id=lm><input id=lm name = \"domain\" type = \"text\" size=\"46\"value=\"$in$dir\"  />\n";
}
else
	echo "<p id=lm><input id=km name = \"domain\" type = \"text\" />\n";

if($isip==4 or $ipv=='ipv4')
	echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv4</option><option>ipv6</option><option>both</option></select>&nbsp;\n";
elseif($isip==6 or $ipv=='ipv6')
	echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv6</option><option>ipv4</option><option>both</option></select>&nbsp;\n";
else
	echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>both</option><option>ipv4</option><option>ipv6</option></select>&nbsp;\n";
echo "<input id=lm name = \"up\" type = \"submit\" value = \"Update\" />\n";
?>
</p></td></tr></table>
</form>
<br />

<?php
if($flag>0)
{	$old4=0;$old6=0;$sum=0;
	$link = mysql_connect("localhost","root", "") or die('Connecting Failure!');
	$db = mysql_select_db("webserver");
	mysql_query("set names utf8", $link);
	if($isip==4) 
		$sql = "select count(*) from ipv4server where ip='$in'";
	elseif($isip==6)
	{	
		$ipn=inet_pton($in);
    	$ipp=inet_ntop($ipn);
    	$ipe=ExpandIPv6Notation($ipp);
    	$ipf=strtolower(padding_ipv6($ipe));

		$sql = "select count(*) from ipv6server where ipfull='$ipf'";
	}
	elseif($ipv=='ipv4')
		$sql = "select count(*) from ipv4server where webdomain='$in'";
	elseif($ipv=='ipv6')
		$sql = "select count(*) from ipv6server where webdomain='$in'";
	elseif($ipv=='both')		//modification needed
	{
		$sql = "select count(*) from ipv4server where webdomain='$in'";
		$result = mysql_query($sql, $link);
		$row = mysql_fetch_array($result);
		if($row[0])	$old4=1;	//old ipv4 entry
		$sql = "select count(*) from ipv6server where webdomain='$in'";
		$result = mysql_query($sql, $link);
		$row = mysql_fetch_array($result);
		if($row[0])	$old6=1;	//old ipv6 entry
		$row[0]=0;
		$sum=$old4+$old6;
		if($old4)
		{
			echo "<table class=\"fancy\"id=l><tr>";
			echo "<td>We already know this server via ipv4. Related entry in our database is listed as follows: </td>\n";
			echo "</tr></table><br>\n";
			$sql1 = "select * from ipv4server where webdomain = '$in'";
			$result1 = mysql_query($sql1, $link);
			$row1 = mysql_fetch_array($result1);
			echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
			echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>directory&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
			echo "<tr><td id=lm>$row1[asn]</td><td id=lm>$row1[ip]&nbsp;</td><td id=lm>$row1[webdomain]&nbsp;</td><td id=lm>$row1[directory]&nbsp;</td><td id=lm>$row1[pagesize]&nbsp;B&nbsp;</td><td id=lm>$row1[bw] B/s&nbsp;</td></tr>\n</table>\n<br>";
		}
		if($old6)
		{	
			if($old4)echo "<br>";
			echo "<table class=\"fancy\"id=l><tr>";
			echo "<td>We already know this server via ipv6. Related entry in our database is listed as follows: </td>\n";
			echo "</tr></table><br>\n";
			$sql1 = "select * from ipv6server where webdomain = '$in'";
			$result1 = mysql_query($sql1, $link);
			$row1 = mysql_fetch_array($result1);
			echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
			echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>directory&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
			echo "<tr><td id=lm>$row1[asn]</td><td id=lm>$row1[ip]&nbsp;</td><td id=lm>$row1[webdomain]&nbsp;</td><td id=lm>$row1[directory]&nbsp;</td><td id=lm>$row1[pagesize]&nbsp;B&nbsp;</td><td id=lm>$row1[bw] B/s&nbsp;</td></tr>\n</table>\n<br>";
		}
		
	}
	//echo "$ipv<br>$sql\n";
	$result = mysql_query($sql, $link);
	$row = mysql_fetch_array($result);
	
	if($row[0] and $ipv!='both')	//old entry
	{
		echo "<table class=\"fancy\"id=l><tr>";
		echo "<td>We already know this server. Related entry in our database is listed as follows: </td>\n";
		echo "</tr></table><br>\n";
		if($isip==4)
			$sql1 = "select * from ipv4server where ip = '$in'";
		elseif($isip==6)
			$sql1 = "select * from ipv6server where ipfull = '$ipf'";
		elseif($ipv=='ipv4')
			$sql1 = "select * from ipv4server where webdomain = '$in'";
		elseif($ipv=='ipv6')
			$sql1 = "select * from ipv6server where webdomain = '$in'";
		else		//modification needed
			$sql1 = "select * from ipv4server where webdomain = '$in'";
		//echo "$ipv<br>$sql1";
		$result1 = mysql_query($sql1, $link);
		$row1 = mysql_fetch_array($result1);
		//echo "<pre>";
		//print_r($row1);
		//echo "</pre>\n";
		/*
		if($row1[6]>1000000)
			{$bw=$row1[6]/1000000;$bw=number_format($bw,3);$unit='MB/S';}
		elseif($row1[6]>1000)
			{$bw=$row1[6]/1000;$bw=number_format($bw,3);$unit='KB/S';}
		else
			{$bw=$row1[6];$bw=number_format($bw,3);$unit='B/S';}
		*/
		$sql5 = "select * from website.webinfo where domain like '$in%' ";
		$result5 = mysql_query($sql5, $link);
		echo "<table width=\"650px\">\n";
		while($row5 = mysql_fetch_array($result5))
			echo "<tr><td id=lm><a target=_blank href='http://$row5[0]'>$row5[1]</a><br></td></tr>\n<tr><td class=\"small\">$row5[2]</td></tr>\n";
		echo "</table><br>&nbsp;\n";
		echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
		echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>directory&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
		echo "<tr><td id=lm>$row1[asn]</td><td id=lm>$row1[ip]&nbsp;</td><td id=lm>$row1[webdomain]&nbsp;</td><td id=lm>$row1[directory]&nbsp;</td><td id=lm>$row1[pagesize]&nbsp;B&nbsp;</td><td id=lm>$row1[bw] B/s&nbsp;</td></tr>\n</table>\n<br>";
	}
	elseif($ipv!='both' or $old4==0)	//new domain
	{	//echo "$ipv<br>$old4<br>$old6";
		echo "<table class=\"fancy\"id=l><tr>";
		if($old4==0 and $ipv=='both')
			echo "<td>We don't know this server via ipv4. Performance test by wget:</td>\n";
		else
			echo "<td>We don't know this server. Performance test by wget:</td>\n";
		echo "</tr></table><br>\n";
		
		$name=calcutime();

		if($ipv=='ipv4' or ($old4==0 and $ipv=='both'))
		{
			$cmd='wget -4 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1" -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$in.$dir ;
			$cmd2='wget -4 -t 1 -T 10 '.$in.$dir;
		}
		elseif($ipv=='ipv6' and $isip==6)
		{
			$cmd='wget -6 -t 1 -T 10 -o /tmp/'.$name.'.txt -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1" -O /tmp/'.$name.'.html http://['.$in.']'.$dir ;
			$cmd2='wget -6 -t 1 -T 10 http://['.$in.']'.$dir;
		}
		elseif($ipv=='ipv6' and $isip==0)
		{
			$cmd='wget -6 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"  -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$in.$dir ;
			$cmd2='wget -6 -t 1 -T 10 '.$in.$dir;
		}

		$ans=`$cmd`;
		echo '<p id=lm>'.$cmd2.'</p><br />';

		$file= fopen("/tmp/".$name.".txt", "r");
		if($file==0)
			echo "<br>fopen error! Error 267.<br>";
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
			$cmd='rm -f /tmp/'.$name.'*';
			//echo $cmd;
			$ans=`$cmd`;
			if($pagesize>0 and $bw>0)
			{	
				if($isip==4)
					$ip=$in;
				elseif($isip==6)
					$ip=$ipp;
				elseif($ipv=='ipv4' or ($old4==0 and $ipv=='both'))
				{
					$ip=dns_get_record($in,DNS_A);
					$ip=$ip[0]['ip'];
				}
				elseif($ipv=='ipv6')
				{
					$ip=dns_get_record($in,DNS_AAAA);
					$ip=$ip[0]['ipv6'];

					$ipn=inet_pton($ip);
					$ipp=inet_ntop($ipn);
					$ipe=ExpandIPv6Notation($ipp);
					$ipf=padding_ipv6($ipe);

				}
				if($isip==4 or $ipv == 'ipv4' or ($old4==0 and $ipv=='both'))
				{	
					//echo "$isip";
					$adds = explode(".", $ip);
					//$adds[0]=intval($adds[0]);$adds[1]=intval($adds[1]);$adds[2]=intval($adds[2]);$adds[3]=intval($adds[3]);
					$dns=$adds[3].'.'.$adds[2].'.'.$adds[1].'.'.$adds[0].'.ip2asn.sasm4.net';
					$as=dns_get_record($dns,DNS_TXT);
					$as=$as[0]['txt'];
					if($as==null)$dnserr=1;
					//write into database
					$sql2 = "insert into ipv4server values('$ip','$in','$as',SUBSTRING_INDEX(webdomain, '.', -1),inet_aton(ip),$pagesize,$bw,'$dir',0,null,null,now(),null)";
					$result2 = mysql_query($sql2, $link);
					echo "<p id=lm>Newly added entry is listed as follows:</p><br>\n";
					echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
					echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>directory&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
					echo "<tr><td id=lm>$as</td><td id=lm>$ip&nbsp;</td><td id=lm>$in&nbsp;</td><td id=lm>$dir&nbsp;</td><td id=lm>$pagesize&nbsp;B&nbsp;</td><td id=lm>$bw B/s&nbsp;</td></tr>\n</table>&nbsp;\n";
				}
				elseif($isip==6 or $ipv == 'ipv6')
				{
					$adds = explode(":", $ipe);
					//$adds[0]=intval($adds[0]);$adds[1]=intval($adds[1]);$adds[2]=intval($adds[2]);$adds[3]=intval($adds[3]);
					$dns=$adds[7].'.'.$adds[6].'.'.$adds[5].'.'.$adds[4].'.'.$adds[3].'.'.$adds[2].'.'.$adds[1].'.'.$adds[0].'.ip6asn.sasm4.net';
					$as=dns_get_record($dns,DNS_TXT);
					$as=$as[0]['txt'];
					if($as==null)$dnserr=1;
					//write into database
					$sql2 = "insert into ipv6server values('$ipp','$ipf','$in','$as',SUBSTRING_INDEX(webdomain, '.', -1),$pagesize,$bw,'$dir',0,null,null,now())";
					$result2 = mysql_query($sql2, $link);
					echo "<p id=lm>Newly added entry is listed as follows:</p><br>\n";
					echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
					echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
					echo "<tr><td id=lm>$as</td><td id=lm>$ipp&nbsp;</td><td id=lm>$in&nbsp;</td><td id=lm>$pagesize&nbsp;B&nbsp;</td><td id=lm>$bw B/s&nbsp;</td></tr>\n</table>&nbsp;\n";
				}
				echo "<p id=lm>Thank you for your contribution! </p><br>\n";
			}
			else
			{
				if($old4==0 and $ipv=='both')
					echo "<p id=lm>This is not a valid ipv4 web server.</p><br>";
				else
					echo "<p id=lm>This is not a valid web server, please check your input.</p><br>";
			}
		}
	}	
	if($ipv=='both' and $old6==0)
	{
		echo "<table class=\"fancy\"id=l><tr>";
		echo "<td>We don't know this server via ipv6. Performance test by wget:</td>\n";
		echo "</tr></table><br>\n";
		
		$name=calcutime();
		$cmd='wget -6 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"  -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$in.$dir ;
		$cmd2='wget -6 -t 1 -T 10 '.$in.$dir;
		$ans=`$cmd`;
		echo '<p id=lm>'.$cmd2.'</p><br /><p id=lm>';

		$file= fopen("/tmp/".$name.".txt", "r");
		while ($file and  !feof($file))
		{
			$line = fgets($file);
			echo $line.'<br />';
		}
		fclose($file);
		echo "</p>";
		$cmd="python $basedir/pywget.py ".$name." >/tmp/".$name."bw.txt";
		//$cmd='python /usr/local/apache/htdocs/pywget.py '.$name.' >/tmp/'.$name.'bw.txt';
		//echo $cmd;
		$ans=`$cmd`;
		$file= fopen("/tmp/".$name."bw.txt", "r");
		$pagesize = intval(fgets($file));
		$bw = intval(fgets($file));
		$cmd='rm -f /tmp/'.$name.'*';
		//echo $cmd;
		$ans=`$cmd`;

		if($pagesize>0 and $bw>0)
		{	
			$ip=dns_get_record($in,DNS_AAAA);
			$ip=$ip[0]['ipv6'];

			$ipn=inet_pton($ip);
			$ipp=inet_ntop($ipn);
			$ipe=ExpandIPv6Notation($ipp);
			$ipf=padding_ipv6($ipe);

			$adds = explode(":", $ipe);
			$dns=$adds[7].'.'.$adds[6].'.'.$adds[5].'.'.$adds[4].'.'.$adds[3].'.'.$adds[2].'.'.$adds[1].'.'.$adds[0].'.ip6asn.sasm4.net';
			//echo $dns;
			$as=dns_get_record($dns,DNS_TXT);
        	$as=$as[0]['txt'];
			if($as==null)$dnserr=1;
			
			//write into database
			$sql2 = "insert into ipv6server values('$ipp','$ipf','$in','$as',SUBSTRING_INDEX(webdomain, '.', -1),$pagesize,$bw,'$dir',0,null,null,now())";
			$result2 = mysql_query($sql2, $link);
			echo "<p id=lm>Newly added entry is listed as follows:</p><br>\n";
			echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
			echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
			echo "<tr><td id=lm>$as</td><td id=lm>$ipp&nbsp;</td><td id=lm>$in&nbsp;</td><td id=lm>$pagesize&nbsp;B&nbsp;</td><td id=lm>$bw B/s&nbsp;</td></tr>\n</table>&nbsp;\n";
			echo "<p id=lm>Thank you for your contribution! </p><br>\n";
		}
		else
				echo "<p id=lm>This is not a valid ipv6 web server.</p><br>";
	}
	//update directory if necessory
	$sqld4= "select count(*) from ipv4server where webdomain='$in'";
	$resultd4 = mysql_query($sqld4, $link);
	$rowd4= mysql_fetch_array($resultd4);
	if($rowd4[0])
	{
		$sqld= "select count(*) from ipv4server where webdomain='$in' and directory='$dir'";
		$resultd = mysql_query($sqld, $link);
		$rowd= mysql_fetch_array($resultd);
		if($rowd[0]==0 or $refresh)
		{
			$name=calcutime();
			$cmd='wget -4 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"  -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$in.$dir;
			$ans=`$cmd`;

			$cmd="python $basedir/pywget.py ".$name." >/tmp/".$name."bw.txt";
			//$cmd='python /usr/local/apache/htdocs/pywget.py '.$name.' >/tmp/'.$name.'bw.txt';
			//echo $cmd;
			$ans=`$cmd`;
			$file= fopen("/tmp/".$name."bw.txt", "r");
			$pagesize = intval(fgets($file));
			$bw = intval(fgets($file));
			$cmd='rm -f /tmp/'.$name.'*';
			//echo $cmd;
			$ans=`$cmd`;

			//echo "New pagesize: ".$pagesize." BW:".$bw."B/s <br>";
			if($pagesize>0 and $bw>0)//compare pagesize and store the bigger one
			{
				$sqld= "select max(pagesize) from ipv4server where webdomain='$in'";
				$resultd = mysql_query($sqld, $link);
				$rowd= mysql_fetch_array($resultd);
				//echo "Old pagesize: ".$rowd[0]."<br>";
				if($pagesize>=$rowd[0])
				{
					$ip=dns_get_record($in,DNS_A);
					$ip=$ip[0]['ip'];
					$sqld= "update ipv4server set ip='$ip',dnsdate=now(),pagesize=$pagesize,directory='$dir',bw=$bw where webdomain='$in'";
					$resultd = mysql_query($sqld, $link);
					echo "<table class=\"fancy\"id=l><tr>";
					echo "<td>We update directory information according to your input. New entry is displayed below:</td>\n";
					echo "</tr></table><br>\n";
					$sql1 = "select * from ipv4server where webdomain = '$in' and directory='$dir'";
					$result1 = mysql_query($sql1, $link);
					$row1 = mysql_fetch_array($result1);
					echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
					echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>directory&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
					echo "<tr><td id=lm>$row1[asn]</td><td id=lm>$row1[ip]&nbsp;</td><td id=lm>$row1[webdomain]&nbsp;</td><td id=lm>$row1[directory]&nbsp;</td><td id=lm>$row1[pagesize]&nbsp;B&nbsp;</td><td id=lm>$row1[bw] B/s&nbsp;</td></tr>\n</table>\n<br>";
				}
				if($refresh and $rowd[0])
				{
					//echo "refreshing<br>\n";
					$ip=dns_get_record($in,DNS_A);
					$ip=$ip[0]['ip'];
					$sqld= "update ipv4server set pagesize=$pagesize,bw=$bw,ip='$ip',dnsdate=now(),directory='$dir' where webdomain='$in'";
					$resultd = mysql_query($sqld, $link);
				}
			}
		}
	}

	$sqld6= "select count(*) from ipv6server where webdomain='$in'";
	$resultd6 = mysql_query($sqld6, $link);
	$rowd6= mysql_fetch_array($resultd6);
	if($rowd6[0])
	{
		$sqld= "select count(*) from ipv6server where webdomain='$in' and directory='$dir'";
		$resultd = mysql_query($sqld, $link);
		$rowd= mysql_fetch_array($resultd);
		if($rowd[0]==0 or $refresh)
		{
			$name=calcutime();
			$cmd='wget -6 -t 1 -T 10 -U "Mozilla/5.0 (Windows NT 6.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"  -o /tmp/'.$name.'.txt -O /tmp/'.$name.'.html '.$in.$dir;
			$ans=`$cmd`;

			$cmd="python $basedir/pywget.py ".$name." >/tmp/".$name."bw.txt";
			//$cmd='python /usr/local/apache/htdocs/pywget.py '.$name.' >/tmp/'.$name.'bw.txt';
			//echo $cmd;
			$ans=`$cmd`;
			$file= fopen("/tmp/".$name."bw.txt", "r");
			$pagesize = intval(fgets($file));
			$bw = intval(fgets($file));
			$cmd='rm -f /tmp/'.$name.'*';
			//echo $cmd;
			$ans=`$cmd`;

			if($pagesize>0 and $bw>0)//compare pagesize and store the bigger one
			{
				$sqld= "select pagesize from ipv6server where webdomain='$in'";
				$resultd = mysql_query($sqld, $link);
				$rowd= mysql_fetch_array($resultd);
				if($pagesize>=$rowd[0])
				{
					$ip=dns_get_record($in,DNS_AAAA);
					$ip=$ip[0]['ipv6'];

					$ipn=inet_pton($ip);
					$ipp=inet_ntop($ipn);
					$ipe=ExpandIPv6Notation($ipp);
					$ipf=padding_ipv6($ipe);
					$sqld= "update ipv6server set ip='$ip',ipfull='$ipf',dnsdate=now(),pagesize=$pagesize,directory='$dir',bw=$bw where webdomain='$in'";
					$resultd = mysql_query($sqld, $link);
					echo "<table class=\"fancy\"id=l><tr>";
					echo "<td>We update directory information according to your input. New entry is displayed below:</td>\n";
					echo "</tr></table><br>\n";
					$sql1 = "select * from ipv6server where webdomain = '$in' and directory='$dir'";
					$result1 = mysql_query($sql1, $link);
					$row1 = mysql_fetch_array($result1);
					echo "<table border = \"1\" cellpadding = \"1\" cellspacing = \"1\">\n";
					echo "<tr><td id=lm>AS number&nbsp;</td><td id=lm>IP address&nbsp;</td><td id=lm>domain&nbsp;</td><td id=lm>directory&nbsp;</td><td id=lm>Pagesize&nbsp;</td><td id=lm>Performance&nbsp;</td></tr>\n";
					echo "<tr><td id=lm>$row1[asn]</td><td id=lm>$row1[ip]&nbsp;</td><td id=lm>$row1[webdomain]&nbsp;</td><td id=lm>$row1[directory]&nbsp;</td><td id=lm>$row1[pagesize]&nbsp;B&nbsp;</td><td id=lm>$row1[bw] B/s&nbsp;</td></tr>\n</table>\n<br>";
				}
				if($refresh and $rowd[0])
				{
					$ip=dns_get_record($in,DNS_AAAA);
					$ip=$ip[0]['ipv6'];

					$ipn=inet_pton($ip);
					$ipp=inet_ntop($ipn);
					$ipe=ExpandIPv6Notation($ipp);
					$ipf=padding_ipv6($ipe);
					$sqld= "update ipv6server set pagesize=$pagesize,bw=$bw,ip='$ip',ipfull='$ipf',directory='$dir',dnsdate=now() where webdomain='$in'";
					$resultd = mysql_query($sqld, $link);
				}

			}
		}
	}

}
elseif($flag==-1) echo "<table class=\"fancy\" id=l><tr><td>&nbsp;Please input a valid new web domain.</td></tr></table><br><br>\n";
elseif($flag==0) echo "<table class=\"fancy\" id=l><tr><td>&nbsp;Help update our database. Please input a new web domain.</td></tr></table><br><br>\n";
date_default_timezone_set('Asia/Chongqing');
include 'tail.php';
?>
<br />
</body>
</html>
