<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="The Trans-Eurasia High Performance Video Conference test page.">
	<title>Step 3: Troubleshooting</title>
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
&nbsp;Step 3: Troubleshooting: inadequate bandwidth
<center>
<h2><font color="#084B8A">Bottleneck detection </font></h2>
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
	else
	{
		$sql= "select step2 from registration where id='$id' and code='$code'";
		$result= mysql_query($sql,$link);
       	$row = mysql_fetch_array($result);
		if(intval($row[0])==0)
			$right=-1;
	}
}
if($right==1 and $full)
{
	$sql= "select * from registration where id='$id' and code='$code'";
	$result= mysql_query($sql,$link);
	$row = mysql_fetch_array($result);
	echo "<form  name = \"iptest\" action = \"traceweb.php\" method = \"get\">\n";
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
	$bw=array(30,30,800);
	$offset=intval($row[3])-1;
	$vtype=$ta[$offset];
	echo "<td>$vtype</td></tr>\n";
	if(intval($row[8])) echo "<tr><td width=40>BW:&nbsp;</td><td>$row[4]mbps</td></tr>\n";
    echo "<tr><td width=40>Time:&nbsp;</td><td>$row[5]&nbsp;to&nbsp;$row[6]</td></tr>\n";
	echo "</table>\n";
	if(intval($row[8]==0)) //fin
	{	
		echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
       	echo "<tr><td><font size=+1 color=\"#084b8a\">Traceroute results between two home gateways.</font></td><td width=70>&nbsp;</td></tr>\n</table>\n";
		$parm="hg=$row[10]";
		$trans=array(":" => "%3A", " " => "+");
		$parm=strtr($parm, $trans);
		$file=fopen("http://[$row[9]]/trace.php?".$parm, "r");$trout='';
		if($file)	fgets($file);
		while ($file and !feof($file))
			$trout=$trout.fgets($file);
		if($file) 
		{
			fclose($file);	
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
			echo "<tr><td bgcolor=\"#F2F2F2\"><font color=\"#04B4AE\"><pre>HG1 traceroute HG2 (traceroute -6n $row[10])\n$trout</pre></font></td></tr>\n</table>\n";
			$trl=explode(" ",$trout);
			$abc=array();
			$def=array();
			$bw12=PHP_INT_MAX;
			echo "<table border=1 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#CCCCCC\">\n";
			echo "<tr><td>IPv6 address</td><td>AS number</td><td>web server</td><td>page size</td><td>Bandwidth</td></tr>";
			for ($tem=0;$tem<count($trl);$tem++)
			if(filter_var($trl[$tem],FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
			{
				if(in_array($trl[$tem],$abc))
					continue;
				else
				{
					array_push($abc,$trl[$tem]);
				
					$ipl=ExpandIPv6Notation($trl[$tem]);
					$ipf=padding_ipv6($ipl);
					$ipt=explode(':',$ipf);
        			$domain=$ipt[7].'.'.$ipt[6].'.'.$ipt[5].'.'.$ipt[4].'.'.$ipt[3].'.'.$ipt[2].'.'.$ipt[1].'.'.$ipt[0].'.ip6asn.sasm4.net';
					$result=dns_get_record($domain,DNS_TXT);
					$as=$result[0]['entries'][0];
					if($as==null)
					{
						echo "<tr><td>&nbsp;$trl[$tem]&nbsp;</td><td>&nbsp;null&nbsp;</td><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</tr>";
						continue;
					}
        			$domain=$ipt[6].'.'.$ipt[5].'.'.$ipt[4].'.'.$ipt[3].'.'.$ipt[2].'.'.$ipt[1].'.'.$ipt[0].'.ip6server.sasm4.net';
					$result=dns_get_record($domain,DNS_AAAA);
					//print_r($result);
					$web=$result[0]['host'];
					if($web==null)
					{
						echo "<tr><td>&nbsp;$trl[$tem]&nbsp;</td><td>&nbsp;$as&nbsp;</td><td>&nbsp;null&nbsp;</td><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</tr>";
						continue;
					}
					echo "<tr><td>&nbsp;$trl[$tem]&nbsp;</td>";
					echo "<td>&nbsp;$as&nbsp;</td><td>&nbsp;<a href=\"http://$web\" target=_blank>$web</a>&nbsp;</td>";
					if(array_key_exists($web,$def))
					{
						$bwt=$def[$web][0];
						$pagesize=$def[$web][1];
						$tbw=bwc($bwt);
						echo "<td>$pagesize</td><td>$tbw[0]$tbw[1]</td></tr>\n";
					}
					else
					{
						$def[$web]=array(0,0);//bw pagesize
						//web page download via HG2
						$parm="wd=$web";
						$trans=array(":" => "%3A", " " => "+");
						$parm=strtr($parm, $trans);
						$file=fopen("http://[$row[10]]/bwweb.php?".$parm, "r");
						if($file) 
						{
							$webd=fgets($file);
							fclose($file);
							$webl=explode(" ",$webd);
							$bwt=intval($webl[1]);
							$def[$web][0]=$bwt;
							$def[$web][1]=$webl[0];
							$tbw=bwc($bwt);
							if($bwt>0)$bw12=($bw12<$bwt)?$bw12:$bwt;
							echo "<td>$webl[0]</td><td>$tbw[0]$tbw[1]</td></tr>\n";
						}
					}
				}
			}
			echo "</table>\n";
		}
		//traceroute HG2 from HG1
		$parm="hg=$row[9]";
		$trans=array(":" => "%3A", " " => "+");
		$parm=strtr($parm, $trans);
		$file=fopen("http://[$row[10]]/trace.php?".$parm, "r");$trout='';
		if($file)	fgets($file);
		while ($file and !feof($file))
			$trout=$trout.fgets($file);
		if($file) 
		{
			fclose($file);	
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
       		echo "<tr><td bgcolor=\"#F2F2F2\"><font color=\"#04B4AE\"><pre>HG2 traceroute HG1 (traceroute -6n $row[9])\n$trout</pre></font></td></tr>\n</table>\n";
			$trl=explode(" ",$trout);
			$abc=array();$def=array();
			$bw21=PHP_INT_MAX;
			echo "<table border=1 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#CCCCCC\">\n";
			echo "<tr><td>IPv6 address</td><td>AS number</td><td>web server</td><td>page size</td><td>Bandwidth</td></tr>";
			for ($tem=0;$tem<count($trl);$tem++)
			if(filter_var($trl[$tem],FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
			{
				if(in_array($trl[$tem],$abc))
					continue;
				else
				{
					array_push($abc,$trl[$tem]);
				
					$ipl=ExpandIPv6Notation($trl[$tem]);
					$ipf=padding_ipv6($ipl);
					$ipt=explode(':',$ipf);
        			$domain=$ipt[7].'.'.$ipt[6].'.'.$ipt[5].'.'.$ipt[4].'.'.$ipt[3].'.'.$ipt[2].'.'.$ipt[1].'.'.$ipt[0].'.ip6asn.sasm4.net';
					$result=dns_get_record($domain,DNS_TXT);
					$as=$result[0]['entries'][0];
					if($as==null)
					{
						echo "<tr><td>&nbsp;$trl[$tem]&nbsp;</td><td>&nbsp;null&nbsp;</td><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</tr>";
						continue;
					}
        			$domain=$ipt[6].'.'.$ipt[5].'.'.$ipt[4].'.'.$ipt[3].'.'.$ipt[2].'.'.$ipt[1].'.'.$ipt[0].'.ip6server.sasm4.net';
					$result=dns_get_record($domain,DNS_AAAA);
					//print_r($result);
					$web=$result[0]['host'];
					if($web==null)
					{
						echo "<tr><td>&nbsp;$trl[$tem]&nbsp;</td><td>&nbsp;$as&nbsp;</td><td>&nbsp;null&nbsp;</td><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</tr>";
						continue;
					}
					echo "<tr><td>&nbsp;$trl[$tem]&nbsp;</td>";
					echo "<td>&nbsp;$as&nbsp;</td><td>&nbsp;<a href=\"http://$web\" target=_blank>$web</a>&nbsp;</td>";
					if(array_key_exists($web,$def))
					{
						$bwt=$def[$web][0];
						$pagesize=$def[$web][1];
						$tbw=bwc($bwt);
						echo "<td>$pagesize</td><td>$tbw[0]$tbw[1]</td></tr>\n";
					}
					else
					{
						$def[$web]=array(0,0);//bw pagesize
						//web page download via HG2
						$parm="wd=$web";
						$trans=array(":" => "%3A", " " => "+");
						$parm=strtr($parm, $trans);
						$file=fopen("http://[$row[10]]/bwweb.php?".$parm, "r");
						if($file) 
						{
							$webd=fgets($file);
							fclose($file);
							$webl=explode(" ",$webd);
							$bwt=intval($webl[1]);
							$def[$web][0]=$bwt;
							$def[$web][1]=$webl[0];
							$tbw=bwc($bwt);
							if($bwt>0)$bw21=($bw21<$bwt)?$bw21:$bwt;
							echo "<td>$webl[0]</td><td>$tbw[0]$tbw[1]</td></tr>\n";
						}
					}
				}
			}
			echo "</table>\n";
		}
		$bw1to2=bwc($bw21);
		$bw2to1=bwc($bw12);
		echo "Bandwidth from HG1 to HG2 is $bw1to2[0]$bw1to2[1].\n";
		echo "Bandwidth from HG2 to HG1 is $bw2to1[0]$bw2to1[1].<br>";
	}
	else
	{
		echo "<p>Registration finished.</p>\n";
	}
}
else
{
	if($right>0)
	{
		echo "<p>Plesae input conference token or go <a href=\"index.php\"  \>back</a> to register.</p>\n";
		echo "<form  name = \"iptest\" action = \"traceweb.php\" method = \"get\">\n";
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
		echo "<form  name = \"iptest\" action = \"traceweb.php\" method = \"get\">\n";
		echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
       	echo "<tr><td width=40>id:&nbsp;</td><td><input id=km name=id size=20 type=text ></td></tr>\n";
       	echo "<tr><td width=40>code:&nbsp;</td><td><input id=km name=code size=20 type=text ></td></tr>\n";
		echo "</table>\n";
		echo "<input id=lm name =ok type = submit value = Submit />\n";
		echo "</form>\n<br><br>\n";
	}
	elseif($right==-1)
	echo "<p>Plesae finish <a href=\"index.php?id=$id&code=$code\">Conference registration</a> first.</p>\n";
}
?>
<div><p id=b>&copy;2010 All rights reserved. CERNET</p></div>
</center>
</body>
</html>

