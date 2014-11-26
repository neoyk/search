<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE>SASM - search by AS</TITLE>
<META http-equiv=Content-Type content=text/html;charset=utf-8>
<link rel="stylesheet" type="text/css" href="indexstyle.css" />
</HEAD>

<BODY>
<!-- comments  -->
<?php include 'head.php';?><br />
	
<CENTER>
<br /><br />
<IMG src="img/sasmAS.gif" name="logo" alt="SASM - search by AS" title="SASM - search by AS" width=309 height=131 border="0" >
<br /><br /><br />
<form name = "as" action = "as.php" method = "get">
<input id=km name = "as" type = "text"  /><br /><br />
Choose a protocol: <select name=ipv><option selected=selected>ipv4</option><option>ipv6</option></select>
<input id=lm name = "ok" type = "submit" value = "Search by AS" />
<div align="center"><br><p id=pm>Usage: Input an AS number (eg:AS4538) to lookup web servers in the AS.</p><br /></div>

</form>

<!-- <P style="HEIGHT: 60px">
<TABLE id=lk cellSpacing=0 cellPadding=0>
  <TBODY>
  <TR>
    <TD></TD></TR>
  </TBODY>
</TABLE></P>
--> 
<?php include 'tail.php'; ?>
<br />
</CENTER>
</BODY>
</HTML>
