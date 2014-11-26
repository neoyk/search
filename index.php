<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE>SASM - search by IP</TITLE>
<link rel="shortcut icon" href="http://search.sasm3.net/favicon.ico" type="image/x-icon" > 
<META http-equiv=Content-Type content=text/html;charset=utf-8>
<link rel="stylesheet" type="text/css" href="indexstyle.css" />
</HEAD>

<BODY>
<!-- comments  -->
<?php include 'head.php';?><br />
	
<CENTER>
<br /><br />
<IMG src="img/sasmIP.gif" name="logo" alt="SASM - search by ip" title="SASM - search by ip" width=309 height=131 border="0" >
<!--<h1>SASM</h1>-->
<br /><br /><br />
<form name = "ip" action = "ip.php" method = "get">
<input id=km name = "ip" type = "text"  /><br /><br />
Choose a protocol: <select name=ipv><option selected=selected>ipv4</option><option>ipv6</option></select>
<input id=lm name = "ok" type = "submit" value = "Search by IP" />
<div align="center"><br><p id=pm>Usage: Input an IP address to lookup web servers in the same AS.</p><br /></div>

</form>
<?php include 'tail.php';?>
</CENTER>
</BODY>
</HTML>
