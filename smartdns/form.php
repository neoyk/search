<?php
$callpage = basename($_SERVER['SCRIPT_FILENAME']);
echo "<form name = plot action = $callpage method = get>\n";

if($callpage != 'full.php')
{
	if($count>1)
	{   
	    echo "Choose one to plot:&nbsp;<select name=limit><option selected=selected>$limit</option>\n";
	    $temp=1;
	    while($temp<=$count)
	    {   
	        if($temp!=$limit)   echo "<option>$temp</option>";
	        $temp=$temp+1;
	    }   
	    echo "</select>\n";
	}   
	echo "</p>\n";
}
else
{
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

echo "<p><b>Zoom Figure (0.5~3) :</b> Width *  ";
echo "<input name = xzoom type = text size=8 width = 10 value=$in6>, ";
echo "Height * ";
echo "<input name = yzoom type = text size=8 width = 10 value=$in7>. ";
echo "</p>";
echo "<p><input name = ok type = submit value = Plot /></p></form>\n";
?>
