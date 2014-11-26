<?php
$callpage = basename($_SERVER['SCRIPT_FILENAME']);
echo "<form name = plot action = $callpage method = get>\n";

echo "<p><b>Choose IP version :</b> ";
echo "<input type=radio name=version value=4 ";
if($version==4) echo "checked";
echo ">4&nbsp;<input type=radio name=version value=6 ";
if($version==6) echo "checked";
echo ">6";
$dbarray = array('CERNET'=>'mnt','Microsoft@Europe'=>'ms1','Microsoft@USA'=>'ms2','Google@USA'=>'google','Brightbox@Britian'=>'brightbox','Aliyun@China'=>'aliyun','Tianyiyun@China'=>'tianyiyun');
if($callpage != 'full.php')
{
	echo "<p><b>Choose AS :</b> ";
	echo "<select name=yvalue><option selected=selected>$in3</option>\n";
	$result3 = mysql_query("select distinct(asn) from ipv".$version."server where asn!='as$in3' order by asn asc", $link);
	while($row3 = mysql_fetch_array($result3))
	{   
		$as=$row3[0];
		$asn=explode('AS',$as);
		echo "<option>$asn[1]</option>";
	}   
	echo "</select>\n";
	if($count>1)
	{   
	    echo "&nbsp;--AND--&nbsp;There are $count servers tested in this AS.&nbsp;\n";
	    echo "Choose one to plot:&nbsp;<select name=limit><option selected=selected>$limit</option>\n";
	    $temp=1;
	    while($temp<=$count)
	    {   
	        if($temp!=$limit)   echo "<option>$temp</option>";
	        $temp=$temp+1;
	    }   
	    echo "</select>\n";
	}   
	else
	    echo "&nbsp;--AND--&nbsp;There is $count server tested in this AS.\n";
	echo "&nbsp;--OR-- input id: <input name = kid type = text size=2 value=$kid>\n";
	echo "</p>\n";
}
else
{
	echo "<p><b>Choose database :</b> ";
	echo "<select name=dbkey>";
	foreach($dbarray as $key => $value)
	{
		if($dbkey==$key)
			echo "<option selected=selected>$key</option>\n";
		else
			echo "<option >$key</option>\n";
	}
	echo "</select></p>";
	echo "<p><b>Selection criterion: </b><input name = where type = text size=80 value=\"$where\"></p>\n";
}
echo "<p><b>Choose parameter: </b>\n";
echo "<input type=checkbox name=bandwidth value=1 ";
if(in_array('bandwidth',$para)) echo "checked";
echo ">Bandwidth &nbsp;\n";
echo "<input type=checkbox name=latency value=1 ";
if(in_array('latency',$para)) echo "checked";
echo ">Latency &nbsp;\n";
echo "<input type=checkbox name=pagesize value=1 ";
if(in_array('pagesize',$para)) echo "checked";
echo ">Pagesize &nbsp;\n";
echo "<input type=checkbox name=lossrate value=1 ";
if(in_array('lossrate',$para)) echo "checked";
echo ">Loss rate &nbsp;\n";
if($callpage == 'debug.php')
{
	echo "<input type=checkbox name=cluster-latency value=1 ";
	if(in_array('cluster-latency',$para)) echo "checked";
	echo ">Cluster latency &nbsp;\n";
	echo "<input type=checkbox name=cluster-bandwidth value=1 ";
	if(in_array('cluster-bandwidth',$para)) echo "checked";
	echo ">Cluster bandwidth &nbsp;\n";

	echo "# of clusters: <input name = k type = text size=2 width = 5 value=$k>\n";
}
/*
if($para=="bandwidth_latency")
	echo "<select name=entry><option selected=selected>Bandwidth_Latency</option><option>Bandwidth</option><option>Latency</option><option>Pagesize</option></select>\n";
elseif($para=="latency")
	echo "<select name=entry><option selected=selected>Latency</option><option>Bandwidth</option><option>Bandwidth_Latency</option><option>Pagesize</option></select>\n";
else if($para=="pagesize")
	echo "<select name=entry><option selected=selected>Pagesize</option><option>Bandwidth</option><option>Latency</option><option>Bandwidth_Latency</option></select>\n";
else
	echo "<select name=entry><option selected=selected>Bandwidth</option><option>Pagesize</option><option>Bandwidth_Latency</option><option>Latency</option></select>\n";
 */
echo "</p>\n";

echo "<p><b>Plot X Range : </b>\n";
if($in=="Month")
	echo "<select name=xaxis><option selected=selected>Month</option><option>Two_weeks</option><option>Full</option><option>--OR--</option></select>\n";
else if($in=="Full")
	echo "<select name=xaxis><option selected=selected>Full</option><option>Month</option><option>Two_weeks</option><option>--OR--</option></select>\n";
else if($in=="--OR--")
	echo "<select name=xaxis><option selected=selected>--OR--</option><option>Two_weeks</option><option>Full</option><option>Month</option></select>\n";
else
{
	echo "<select name=xaxis><option selected=selected>Two_weeks</option><option>Month</option><option>Full</option><option>--OR--</option></select>\n";
}
if($in=="--OR--")
	echo " --OR-- Choose Start and End Dates: from <input name = time1 type = text size=8 width = 10 value=$t1> to <input name = time2 type = text size=8 width = 10 value=$t2>";
else 
	echo " --OR-- Choose Start and End Dates: from <input name = time1 type = text size=8 width = 10> to <input name = time2 type = text size=8 width = 10>";
 " (eg:20110528 to ".date('Ymd').")</p>\n";

echo "<p><b>Plot Y Range : </b>\n";
if($in2=="Auto")
	echo "<select name=yaxis><option selected=selected>Auto</option><option>--OR--</option></select>\n";
else if($in2=="--OR--")
	echo "<select name=yaxis><option selected=selected>--OR--</option><option>Auto</option></select>\n";
echo " --OR-- Enter Min and Max Y Axis values: from <input name = min type = text size=8 width = 10 \n";
if($in2=="--OR--")
	echo "value=$in4 /> to <input name = max type = text size=8 width = 10 value=$in5 /></p>\n";
else if($in2=="Auto")
	echo "/> to <input name = max type = text size=8 width = 10 /></p>\n";

echo "<p><b>Zoom Figure (0.5~3) :</b> Width *  ";
echo "<input name = xzoom type = text size=8 width = 10 value=$in6>, ";
echo "Height * ";
echo "<input name = yzoom type = text size=8 width = 10 value=$in7>. ";
echo "\n<b>Plot color: </b>";
if($in8=="red")
	echo "<select name=color><option selected=selected >red</option><option>auto</option><option>green</option><option>blue</option></select>\n";
else if($in8=="green")
	echo "<select name=color><option selected=selected >green</option><option>auto</option><option>red</option><option>blue</option></select>\n";
else if($in8=="blue")
	echo "<select name=color><option selected=selected >blue</option><option>auto</option><option>red</option><option>green</option></select>\n";
else
	echo "<select name=color><option selected=selected >auto</option><option>red</option><option>green</option><option>blue</option></select>\n";
echo "</p>";
echo "<p><input name = ok type = submit value = Plot /></p></form>\n";
?>
