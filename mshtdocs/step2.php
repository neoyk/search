<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="The Trans-Eurasia High Performance Video Conference test page.">
	<title>Step 2: Bandwidth Test Page</title>
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
&nbsp;Step 2: Bandwidth Test
<a href="info.php">Conf Info Query</a>
<center>
<h2><font color="#084B8A">Bandwidth Test </font></h2>
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
		echo "<form  name = \"iptest\" action = \"step2.php\" method = \"get\">\n";
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
	    $ta=array('DVTS(30mbps)','VLC(27mbps)','uncompressed(800mbps)');
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
        	echo "<tr><td width=125>&nbsp;</td><td><font size=+1 color=\"#084b8a\">Bandwidth test between two home gateways. This may take 10 seconds.</font></td></tr>\n</table>\n";
			$cou=-1;$bwa=0;$dvout='';
			$parm="bw=$bw[$offset]&hg=$row[10]";
			$trans=array(":" => "%3A", " " => "+");
			$parm=strtr($parm, $trans);
			$file=fopen("http://[$row[9]]/dvping.php?".$parm, "r");
			while ($file and !feof($file))
				$dvout=$dvout.fgets($file);
			if($file) fclose($file);
	/*		$dvout="dvping -bw 40m -t 1 -p 8000 -rp 8001 2001:250:3::ca26:650c
ping 2001:250:3::ca26:650c with dvts data:
send port:8000
recv port:8001
period   :1 s
snd_pkt  rcv_pkt  snd_rate     rcv_rate     loss      rtt
36445    36445    40Mbps       40Mbps       0%        0.6333ms
";	*/
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        	echo "<tr><td bgcolor=\"#F2F2F2\"><font color=\"#04B4AE\"><pre>$dvout</pre></font></td></tr>\n</table>\n";
			//echo "<textarea name=\"dvpingout\" cols=\"55\" rows=\"8\" >$dvout</textarea>\n<br>";
			$dv=array_reverse(array_filter(explode(' ',$dvout)));
			foreach($dv as $temp)
			{
				$cou=$cou+1;
				if(stristr($temp,'%'))
				{
					$tem=explode('%',$temp);
					$per=intval($tem[0]);
					$rer=$dv[$cou+1];
					if(stristr($rer,'Mbps'))
						$rer=intval($rer);
					elseif(stristr($rer,'Gbps'))
						$rer=intval($rer)*1000;
					elseif(stristr($rer,'kbps'))
						$rer=intval($rer)/1000;
					elseif(stristr($rer,'bps'))
						$rer=intval($rer)/1000000;
					else
						continue;
					$bwa=(100-$per)*$rer/100;
					break;
				}
			}
			if($bwa>=$bw[$offset])
			//if($bwa>100)
			{
				$parm="id=$id&code=$code&bw=$bwa";
				$trans=array(":" => "%3A", " " => "+");
				$parm=strtr($parm, $trans);
				//echo "http://[$hg1]/hgwconf.php?".$parm;
				$file=fopen("http://[$row[9]]/regfin.php?".$parm, "r");
				$l=trim(fgets($file, 100)); 
				$line=explode(':',$l); 
				if($line[0]!="conf info received") 
				{
					echo "<font color=red size=-1>Can't transfer information to HG1! HG1 response: $l</font><br>\n";
					$right=0;
				}
				else
					$p1=intval($line[1]);
				$file=fopen("http://[$row[10]]/regfin.php?".$parm, "r");
				$l=trim(fgets($file, 100)); 
				$line=explode(':',$l); 
				if($line[0]!="conf info received") 
				{
					echo "<font color=red size=-1>Can't transfer information to HG2! HG2 response: $l</font><br>\n";
					$right=0;
				}
				else
					$p2=intval($line[1]);
				fclose($file);
				if($right)
				{
					$parm="id=$id&code=$code&port2=$p2";
					$trans=array(":" => "%3A", " " => "+");
					$parm=strtr($parm, $trans);
					//echo "http://[$hg1]/hgwconf.php?".$parm;
					$file=fopen("http://[$row[9]]/regfin2.php?".$parm, "r");
					$l=trim(fgets($file, 100)); 
					$line=explode(':',$l); 
					if($line[0]!="conf info received") 
					{
						echo "<font color=red size=-1>Can't transfer information to HG1! HG1 response: $l</font><br>\n";
						$right=0;
					}
					$parm="id=$id&code=$code&port2=$p1";
					$trans=array(":" => "%3A", " " => "+");
					$parm=strtr($parm, $trans);
					$file=fopen("http://[$row[10]]/regfin2.php?".$parm, "r");
					$l=trim(fgets($file, 100)); 
					$line=explode(':',$l); 
					if($line[0]!="conf info received") 
					{
						echo "<font color=red size=-1>Can't transfer information to HG2! HG2 response: $l</font><br>\n";
						$right=0;
					}
					if($right==0) 
						echo "<input id=lm name =ok type = submit value = Submit /></form>\n";
					else
					{
						echo "<font color=gray size=-1>Finish transfering information to two home gateways.</font><br>\n";
						//sql
						$sql= "update registration set bandwidth=$bwa,fin=1,port1=$p1,port2=$p2 where id='$id' and code='$code'";
						$result= mysql_query($sql,$link);
        				echo "Available bw: $bwa"."mbps. Bandwidth test <font color=green>passed</font>. Registration finished.\n";
						echo "<br>Please goto <a  href=\"info.php?id=$id&code=$code\">conf info query page</a> for more information.";
					}
				}
				else
				{
					echo "<input id=lm name =ok type = submit value = Submit /></form>\n";
				}
			}
			else
			{
        		echo "Available bandwidth: $bwa"."mbps. <font color=red>Inadequate</font> for video conference.<br> Go to <a href=\"traceweb.php?id=$id&code=$code\">troubleshooting page</a>.\n";
			}
		}
		else
		{
			echo "<p>Registration finished.</p>\n";
			echo "Please goto <a  href=\"info.php?id=$id&code=$code\">conf info query page</a> for more information.";
		}
	}
	else
	{
		if($right>0)
		{
			echo "<p>Plesae input conference token or go <a href=\"index.php\"  \>back</a> to register.</p>\n";
			echo "<form  name = \"iptest\" action = \"step2.php\" method = \"get\">\n";
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
			echo "<form  name = \"iptest\" action = \"step2.php\" method = \"get\">\n";
			echo "<table border=0 cellpadding=5 cellspacing=0 style=\"border-collapse: collapse\" bgcolor=\"#FFFFFF\" bordercolor=\"#111111\">\n";
        	echo "<tr><td width=40>id:&nbsp;</td><td><input id=km name=id size=20 type=text ></td></tr>\n";
        	echo "<tr><td width=40>code:&nbsp;</td><td><input id=km name=code size=20 type=text ></td></tr>\n";
			echo "</table>\n";
			echo "<input id=lm name =ok type = submit value = Submit />\n";
			echo "</form>\n<br><br>\n";
		}
		elseif($right==-1)
		echo "<p>Plesae finish <a href=\"step2.php?id=$id&code=$code\">Home Gateway registration</a> first.</p>\n";
	}
?>
<div><p id=b>&copy;2009-2011 All rights reserved. CERNET</p></div>
</center>

</body>

</html>

