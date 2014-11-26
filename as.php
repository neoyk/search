<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv = "Content-Type" content = "text-html; charset = utf-8" />
<title>SASM - Search by AS</title>
</head>

<body>
<?php include 'head.php';?>
<form name = "query" action = "as.php" method = "get">
<table><tr><td><a href="asindex.php" title="Go to SASM Home - search by AS"><img src="img/logo_small.gif" name="logo" alt="SASM" width=109 height=35 border="0" ></a></td>
<td>

<?php

	function calcutime()
	{
		$time = explode( " ", microtime());
		$usec = (float)$time[0];
		$sec = (float)$time[1];
		return $sec + $usec;
	}

$flag=1;
if(isset($_GET['as']) and $_GET['as']!='')
{
	$temp0=$_GET['as'];
	if(intval($temp0)!=0)
	{
		$in=intval($temp0);
		$flag=2;
	}
	else
	{
		$temp1=strtoupper($temp0);
		$temp2=explode('AS',$temp1);
		if(count($temp2)==2 and intval($temp2[1])!=0)
		{
			$in=intval($temp2[1]);
			$flag=2;
		}
        else 
			$flag=-1;
	}
}
else
{
	$flag=0;
	$in=0;
}
if($flag>0)
	echo "<p id=lm><input id=km name = \"as\" type = \"text\" value=\"AS$in\"  />";
else
	echo "<p id=lm><input id=km name = \"as\" type = \"text\" />";


if(isset($_GET['ipv']) and $_GET['ipv']!='')
    $ipv=$_GET['ipv'];
else
    $ipv='ipv4';

if($ipv=='ipv6')
    echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv6</option><option>ipv4</option></select>&nbsp;\n";
else
{	$ipv='ipv4';
    echo "&nbsp;Choose a protocol: <select name=ipv><option selected=selected>ipv4</option><option>ipv6</option></select>&nbsp;\n";
}

if(isset($_GET['page']) and $_GET['page']!='')
{       $page=$_GET['page'];
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
if($flag>0)
{	
	$start_time=calcutime();
	//$answer=`$cmd`;
	//$ans=explode("\"",$answer);//AS number
	$asn=$in;
	$link = mysql_connect("localhost","root", "") or die('Connecting Failure!'); 
	$db = mysql_select_db("webserver");  
	mysql_query("set names utf8", $link);
    if($ipv=='ipv6')	
		$sql = "select count(distinct webdomain) from ipv6server where asn='AS$asn'";
	else
		$sql = "select count(distinct webdomain) from ipv4server where asn='AS$asn'";
			
	$result = mysql_query($sql, $link);  
	$row = mysql_fetch_array($result);
	echo "<table class=\"fancy\"id=l><tr>";
	echo "<td>There are <b>$row[0]</b> $ipv web server(s) found in this AS.&nbsp;";
	if($row[0]==0)
		echo "If you know a web server in this AS, please help <a target=_blank href=\"update.php\">update</a> our database.</td>";
	else
		echo "</td>";
	$max=ceil($row[0]/10);
	if($page>$max) $page=$max;
	$start=$page*10-9;if($start<1)$start=1;$offset=$page*10-10;$end=min($page*10,$row[0]);
	if($row[0])echo "<td>Web server <b>$start</b> - <b>$end</b></td>";
	echo "</tr></table><br>\n";
	require("asinfo.php");
	if($ipv=='ipv6')
		echo "<tr><td id=lm><a target=_blank href='http://www.cidr-report.org/cgi-bin/as-report?as=AS$asn&view=2.0&v=6'>IPv6 Routing information from CIDR Report</a></td></tr></table><br><br>\n";
	else	
		echo "<tr><td id=lm><a target=_blank href='http://www.cidr-report.org/cgi-bin/as-report?as=AS$asn&view=%28null%29'>Routing information from CIDR Report</a></td></tr></table><br><br>\n";
 	if($row[0]>0)
	{   
		if($ipv=='ipv6')	
			$sql = "select distinct(webdomain) from ipv6server where asn='AS$asn' and pagesize>50000 and bw<15000000 order by bw desc limit $offset,10";
		else
			$sql = "select distinct(webdomain) from ipv4server where asn='AS$asn' and pagesize>50000 and bw<15000000 order by bw desc limit $offset,10";
		$result = mysql_query($sql, $link);  
		while($row = mysql_fetch_array($result))
		{
			if($ipv=='ipv6')	
				$sql2 = "select ip,bw,pagesize,directory from ipv6server where webdomain='$row[0]' limit 1";
			else
				$sql2 = "select ip,bw,pagesize,directory from ipv4server where webdomain='$row[0]' limit 1";
			$result2 = mysql_query($sql2, $link); 
			$row2 = mysql_fetch_array($result2);
			if($row2[1]>1000000)
			{$bw=$row2[1]/1000000;$bw=number_format($bw,3);$unit='MB/S';}
			elseif($row2[1]>1000)
			{$bw=$row2[1]/1000;$bw=number_format($bw,3);$unit='KB/S';}
			else
			{$bw=$row2[1];$bw=number_format($bw,3);$unit='B/S';}
				
			$sql4 = "select count(*) from website.webinfo where domain like '$row[0]%'";
				$result4 = mysql_query($sql4, $link); 
			$row4 = mysql_fetch_array($result4);
			//echo "<tr><td id=lm>$row4[0]<br></td></tr>\n";
			if($row4[0])
			{
				$sql5 = "select * from website.webinfo where domain like '$row[0]%' limit 2";
					$result5 = mysql_query($sql5, $link); 
				echo "<table>\n";
				while($row5 = mysql_fetch_array($result5))
				echo "<tr><td id=lm><a target=_blank href='http://$row5[0]'>$row5[1]</a><br></td></tr>\n";
                echo "<tr><td>$row[0]&nbsp;$row2[0]&nbsp;Pagesize: $row2[2]B&nbsp;Performance: $bw$unit\n";
				//echo "<tr><td id=lm>&nbsp;Title (cache under construction)</td></tr>";
                echo "&nbsp;<a target=_blank href='http://$row[0]$row2[3]'>Download URL</a>\n";
                //echo "&nbsp;<a href='bwtest.php?domain=$row[0]$row2[3]&ipv=$ipv'>Test from this server</a></td></tr>\n";
                echo "</table><br>&nbsp;\n";
			}
			else
			{
                echo "<table><tr><td id=lm>&nbsp;<a target=_blank href='http://$row[0]'>$row[0]</a> &nbsp;</td><td class=low>$row2[0] &nbsp;Pagesize: $row2[2]B Performance: $bw$unit \n";
                echo "&nbsp;<a target=_blank href='http://$row[0]$row2[3]'>Download URL</a>\n";
                //echo "&nbsp;<a href='bwtest.php?domain=$row[0]$row2[3]&ipv=$ipv'>Test from this server</a></td>\n";
                echo "</tr></table><br>&nbsp;\n";
			}
		}
		if($page>1)
		{$previous=$page-1;echo "<br><br><A onclick=s(this) href='as.php?as=$in&ok=Submit&ipv=$ipv&page=$previous' >Previous</a>&nbsp;\n";}
		$mi=max(1,$page-5);
		$ma=min($max,$mi+10);
		$temp=$mi;
		while ($temp>=$mi and $temp<=$ma)
		{
			if($temp==$page)
				echo "[$page]&nbsp;\n";
			else
				echo "<A onclick=s(this) href='as.php?as=$in&ok=Submit&ipv=$ipv&page=$temp'>[$temp]</a>&nbsp;\n";
			$temp=$temp+1;
		}
		if($page<$max){
			$next=$page+1;echo "<A onclick=s(this) href='as.php?as=$in&ok=Submit&ipv=$ipv&page=$next' >Next</a><br><br>\n";
		}
	}

	$end_time=calcutime();
$total=round($end_time-$start_time,2);
echo "<p>Time elasped: $total s.</p>\n<br />";
}
elseif($flag==0) echo "<table class=\"fancy\" id=l><tr><td>&nbsp;Please input an AS number.</td></tr></table><br><br>\n";
else echo "<table class=\"fancy\" id=l><tr><td>&nbsp;Invalid AS number!</td></tr></table><br><br>\n";
include 'tail.php';
?>
<br />
</body>
</html>

