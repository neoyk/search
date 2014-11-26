<?php
	require_once 'vendor/autoload.php';
	use GeoIp2\Database\Reader;
	try
	{	
		$geo = new Reader($_SERVER['DOCUMENT_ROOT'].'/GeoLite2-City.mmdb');
		$ipgeoinfo = $geo->city($in);
		if($ipgeoinfo->location->latitude == 0 and $ipgeoinfo->location->longitude == 0 ) 
			$usegeo = 0;
		else
			$usegeo = 1;
	}
	catch (Exception $e){
		$usegeo=0;
	}
	$start_time=calcutime();
	if($realipv=='ipv4')
		$asn = getASN($in);
	elseif($realipv=='ipv6')
		$asn = getASN6($in);
	$time1=calcutime();
	$link = mysql_connect("localhost","root", "") or die('Connection Failure!'); 
	$db = mysql_select_db("webserver");  
	mysql_query("set names utf8", $link);
	$sql = "select count(webdomain) from ".$ipv."server where asn='AS$asn'";
	$result = mysql_query($sql, $link);  
	$row = mysql_fetch_array($result);
	$totalnum = $row[0];
	if($asn>0 )
	{	
		echo "<table class=fancy><tr><td>";
		if($callpage=='domain.php')
			echo "&nbsp;<b>$domain $in</b>";
		else
			echo "&nbsp;<b>$in</b>";
		echo " belongs to <b>AS$asn</b>";
		if($usegeo)
		{
			$where = "and abs(latitude-{$ipgeoinfo->location->latitude})<5 and abs(longitude-{$ipgeoinfo->location->longitude})<10";
			$sql .= $where;
			$result = mysql_query($sql, $link);  
			$row = mysql_fetch_array($result);
			$nearby = $row[0];
			echo "&nbsp;from {$ipgeoinfo->city->name} {$ipgeoinfo->country->name}";
			echo ". <a href=as.php?as=$asn&ipv=$ipv><b>$totalnum</b> $ipv web server(s)</a> found in this AS";
			if($nearby>0 or $totalnum>0) 
				echo ", <b>$nearby</b> of which are close to the input IP address.\n";
			else
				echo ". ";
		}
		else
		{	
			$where = '';
			echo ". <a href=as.php?as=$asn&ipv=$ipv><b>$totalnum</b> $ipv web server(s)</a> found in this AS";
			$nearby = $totalnum;
		}
		if($nearby==0)
			echo "If you know a web server near this IP address, please help <a target=_blank href=\"update.php\">update</a> our database.</td>";
		else
		{
			$max=ceil($nearby/10);
			if($page>$max) $page=$max;
			$start=$page*10-9;if($start<1)$start=1;$offset=$page*10-10;$end=min($page*10,$nearby);
			echo "<td>Web server <b>$start</b> - <b>$end</b></td>";
		}
		echo "</tr></table><br>\n";
		require("asinfo.php");
		if($ipv=='ipv6')
			echo "<tr><td id=lm><a target=_blank href='http://www.cidr-report.org/cgi-bin/as-report?as=AS$asn&view=2.0&v=6'>IPv6 Routing information from CIDR Report</a></td></tr></table><br><br>\n";
		else
			echo "<tr><td id=lm><a target=_blank href='http://www.cidr-report.org/cgi-bin/as-report?as=AS$asn&view=%28null%29'>Routing information from CIDR Report</a></td></tr></table><br><br>\n";
		if($nearby>0)
		{   
			if($usegeo)
			{
				if($ipv=='ipv4')
					$sql = "select webdomain,ip,bw,pagesize,directory,abs(ipnum - cast(inet_aton('$in') as signed))as iperror,abs(latitude-{$ipgeoinfo->location->latitude})+abs(longitude-{$ipgeoinfo->location->longitude}) as error from ".$ipv."server where asn='AS$asn' ".$where." order by error, iperror, bw desc limit $offset,10";
				else
					$sql = "select webdomain,ip,bw,pagesize,directory,abs(latitude-{$ipgeoinfo->location->latitude})+abs(longitude-{$ipgeoinfo->location->longitude}) as error from ".$ipv."server where asn='AS$asn' ".$where." order by error asc, bw desc limit $offset,10";
			}
			else
				$sql = "select webdomain,ip,bw,pagesize,directory from ".$ipv."server where asn='AS$asn' and pagesize>50000 order by bw desc limit $offset,10";
			$result = mysql_query($sql, $link);  
			while($row = mysql_fetch_array($result))
			{
				if($row[2]>1000000)
				{
					$bw=$row[2]/1000000;$bw=number_format($bw,3);$unit='MB/S';
				}
				elseif($row[2]>1000)
				{
					$bw=$row[2]/1000;$bw=number_format($bw,3);$unit='KB/S';
				}
				else
				{
					$bw=$row[2];$bw=number_format($bw,3);$unit='B/S';
				}
    				
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
					echo "<tr><td>$row[0]&nbsp;$row[1]&nbsp;Pagesize: $row[3]B&nbsp;Performance: $bw$unit\n";
					//echo "<tr><td id=lm>&nbsp;Title (cache under construction)</td></tr>";
					echo "&nbsp;<a target=_blank href='http://$row[0]$row[4]'>Download URL</a>\n";
					//echo "&nbsp;<a href='bwtest.php?domain=$row[0]$row2[3]&ipv=$ipv'>Test from this server</a></td></tr>\n";
					echo "</table><br>&nbsp;\n";
				}
				else
				{
					echo "<table><tr><td id=lm>&nbsp;<a target=_blank href='http://$row[0]'>$row[0]</a> &nbsp;</td><td class=low>$row[1] &nbsp;Pagesize: $row[3]B Performance: $bw$unit \n";
					echo "&nbsp;<a target=_blank href='http://$row[0]$row[4]'>Download URL</a>\n";
					//echo "&nbsp;<a href='bwtest.php?domain=$row[0]$row2[3]&ipv=$ipv'>Test from this server</a></td>\n";
					echo "</tr></table><br>&nbsp;\n";
				}
			}
			if($page>1)
			{$previous=$page-1;echo "<br><br><a href='ip.php?ip=$in&ok=Submit&ipv=$ipv&page=$previous' >Previous</a>&nbsp;\n";}
			$mi=max(1,$page-5);
			$ma=min($max,$mi+10);
			$temp=$mi;
			while ($temp>=$mi and $temp<=$ma)
			{
				if($temp==$page)
					echo "[$page]&nbsp;\n";
				else
					echo "<a href='ip.php?ip=$in&ok=Submit&ipv=$ipv&page=$temp'>[$temp]</a>&nbsp;\n";
				$temp=$temp+1;
			}
			if($page<$max)
			{$next=$page+1;echo "<a href='ip.php?ip=$in&ok=Submit&ipv=$ipv&page=$next' >Next</a><br><br>\n";}
    		}
	}
	elseif($asn==0)
	{
		echo "<table class=\"fancy\" ><tr><td>No AS or web server information available.</td></tr></table><br>\n";
	}
	else
	{
		echo "<table class=\"fancy\" ><tr><td>DNS is temporarily  unavailable. Please try again later. Sorry for the inconvenience.</td></tr></table><br>\n";
	}

	$end_time=calcutime();
	$dns_time=round($time1-$start_time,2);
	$db_time =round($end_time-$time1,2);
	$total=round($end_time-$start_time,2);
	echo "<p>Time elasped: dns $dns_time s; database $db_time s; total $total s.</p>\n<br />";
?>
