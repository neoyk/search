<?php
$figidx = 1;
$len = count($para);
	foreach( $para as $entry)
	{
		$cluster = 0;
		if($len == $figidx)
			echo "<img src=slafig.php?short=0&token=smartdns&entry=$entry&cluster=$cluster&xaxis=$in&yaxis=$in2&yvalue=$in3&min=$in4&max=$in5&xzoom=$in6&yzoom=$in7&color=$in8&time1=$t1&time2=$t2&id=$id&version=$version />";
		else
			echo "<img src=slafig.php?short=1&token=smartdns&entry=$entry&cluster=$cluster&xaxis=$in&yaxis=$in2&yvalue=$in3&min=$in4&max=$in5&xzoom=$in6&yzoom=$in7&color=$in8&time1=$t1&time2=$t2&id=$id&version=$version />";
		$figidx += 1;
		echo "\n<br />\n";
	}
?>
