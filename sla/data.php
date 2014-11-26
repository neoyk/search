<?php
$callpage = basename($_SERVER['SCRIPT_FILENAME']);
if($callpage =='location6.php')
	$version = '6';
else
	$version = '4';
	function bw2p($bandwidth)
	{
		if ($bandwidth>1005000)
			return strval(round($bandwidth/ pow(1024,2),2)).' MB/s';
		if ($bandwidth>1024)
			return strval(round($bandwidth/ pow(1024,1),2)).' KB/s';
		return strval(round($bandwidth)).' B/s';
	}
	$datetime = $_GET['datetime'];
	if(empty($datetime))
		$datetime = 'now()';
    $link = mysql_connect("127.0.0.1","root", "") or die('Connection Failure!'); 
	mysql_set_charset('utf8',$link);
	$link2 = mysql_connect("127.0.0.1","root", "") or die('Connection Failure!'); 
    $db = mysql_select_db("mnt");
    $sql = "select count(*),longitude,latitude,webdomain,id,location,performance,aspath,bandwidth from ipv{$version}server where ip!='0' and performance != 'N/A' group by location having longitude is not null";
    $result = mysql_query($sql, $link);
    $count = 1;
    while($row = mysql_fetch_array($result))
	{
		$performance = $row[6];
		$bandwidth = $row[8];
		if($datetime!='now()')
		{
			$sql = "select pagesize, bandwidth, maxbw, latency, lossrate, time from web_perf$version where id=$row[4] and time>'$datetime' order by time limit 1";
			$result2 = mysql_query($sql, $link2);
			if(0 != mysql_num_rows($result2))
			{
				$row2 = mysql_fetch_array($result2);
				$bandwidth = $row2[1];
				$pagesize = number_format($row2[0]);
				$btc = bw2p($row2[1]);
				$peak = bw2p($row2[2]);
				$lossrate = $row2[4];
				$performance = "Page size: $pagesize B, BTC: $btc(peak: $peak), RTT: $row2[3]ms, lossrate: $lossrate% @ $row2[5]";
			}
		}
		$des = "$row[5]<br># of websites: $row[0]<br>Eg: <a target=blank href=http://$row[3]>$row[3]</a>,&nbsp;AS path:&nbsp;$row[7]<br>$performance<br>Plot: ";
		if($row[0]>1)
		{
			$num = 0;
			$sql = "select id from mnt.ipv{$version}server where ip!='0' and location=\"$row[5]\"";
			$result2 = mysql_query($sql, $link2);
			while($row2 = mysql_fetch_array($result2))
			{
				$des .= "&nbsp;<a target=blank href=index.php?version=$version&kid=$row2[0]>$row2[0]</a>";
				$num += 1;
				if($num>9)
				{$des .= "<br>";$num=0;}
			}
		}
		else
			$des .= "<a target=blank href=index.php?kid=$row[4]>$row[4]</a>";

        echo "citymap['$count'] = {
		center: new google.maps.LatLng($row[2],$row[1]), \n		population: $row[0],\n";
		if($bandwidth>2000000)
			echo "		color : 'green',\n";
		elseif($bandwidth>200000)
			echo "		color : 'yellow',\n";
		else
			echo "		color : 'red',\n";
        echo "		description: \"$des\"\n
};\n";
        $count += 1;
    }
?>
