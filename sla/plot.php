<?php
		$len = count($para);
		$figidx = 1;
		foreach( $para as $entry)
		{
			if(strpos($entry,'cluster')!==false)
			{
				$cluster = $k;
				$entry = substr($entry,8);
				echo "\n<br>".$entry." after cluster:<br/>\n";
			}
			else 
				$cluster = 0;
			if($len == $figidx)	
				echo "<img src=slafig.php?short=0&entry=$entry&cluster=$cluster&xaxis=$in&yaxis=$in2&yvalue=$in3&min=$in4&max=$in5&xzoom=$in6&yzoom=$in7&color=$in8&time1=$t1&time2=$t2&id=$id&version=$version&dbn=$dbn />";
			else
				echo "<img src=slafig.php?short=1&entry=$entry&cluster=$cluster&xaxis=$in&yaxis=$in2&yvalue=$in3&min=$in4&max=$in5&xzoom=$in6&yzoom=$in7&color=$in8&time1=$t1&time2=$t2&id=$id&version=$version&dbn=$dbn />";
			$figidx += 1;
			echo "\n<br />\n";
		}
?>
