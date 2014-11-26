<?php
$callpage = basename($_SERVER['SCRIPT_FILENAME']);
if($callpage!='index.php' and $callpage !='asindex.php')
	echo "<table class=\"fancy\"><tr><td>";
echo "Search server by:";
if(isset($_GET['myip']) and $_GET['myip']=='true')
    {   $flag=2;
        echo "<b>my IP/AS</b>\n&nbsp;<a href=\"index.php\">IP</a>\n";
    }
    elseif($callpage=='ip.php')
    {
        echo "<a href=\"ip.php?myip=true\">my IP/AS</a>\n";
        echo "&nbsp;<b>IP</b>\n";
    }
	else
	{
		echo "<a href=\"ip.php?myip=true\">my IP/AS</a>\n";
		echo "&nbsp;<a href=\"index.php\">IP</a>\n";
	}
if($callpage=='domain.php')
	echo "&nbsp;<b>Domain</b>\n";
else
	echo "&nbsp;<a href=\"domain.php\">Domain</a>\n";
if($callpage=='as.php')
	echo "<b>AS</b>&nbsp;";
else
	echo "&nbsp;<a href=\"asindex.php\">AS</a>&nbsp;\n";
if($callpage=='update.php')
	echo "|&nbsp;<b>Update&nbsp;</b>";
else
	echo "|&nbsp;<a href=\"update.php\">Update</a>\n";
if($callpage=='perftest.php')
	echo "|&nbsp;<b>Performance test</b>&nbsp;\n";
else
	echo "|&nbsp;<a href=\"perftest.php\">Performance test</a>&nbsp;\n";
if($callpage=='slowweb.php')
	echo "|&nbsp;<b>Slow website report</b>&nbsp;\n";
else
	echo "|&nbsp;<a href=\"slowweb.php\">Slow website report</a>&nbsp;\n";

echo "|&nbsp;<a onclick=s(this) href=\"documentation.html\">Documentation</a>&nbsp;\n";
if($callpage!='index.php' and $callpage !='asindex.php')
	echo "</td></tr></table>\n";
//if(basename($_SERVER['SCRIPT_FILENAME'])!='perftest.php')
	echo "<br /> ";
?>
