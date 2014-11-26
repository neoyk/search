<?php
function checktime($t)
{
	//$t='2010-07-14 09:00:01';
	$t1=explode(" ",$t);
	//print_r($t1);
	if(count($t1)<2)	return 0;

	$t2=explode("-",$t1[0]);
	//print_r($t2);
	if(count($t2)!=3)	return 0;

	$t3=explode(":",$t1[1]);
	//print_r($t3);
	if(count($t3)!=3)	return 0;
	if(checkdate(intval($t2[1]),intval($t2[2]),intval($t2[0]))==0)
		return 0;
	if (is_numeric($t2[0])==0 or is_numeric($t2[1])==0 or is_numeric($t2[2])==0)
		return 0;
	if (is_numeric($t3[0]) && is_numeric($t3[1]) && is_numeric($t3[2]))
	{
		if (($t3[0] >= 0 && $t3[0] <= 23) && ($t3[1] >= 0 && $t3[1] <= 59) && ($t3[2] >= 0 && $t3[2] <= 59))
			return 1;
		else
			return 0;
	} 
	else 
		return 0;
}

function ExpandIPv6Notation($ip)
{
    if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)
    {
        if(strpos($ip, '::')==strlen($ip)-2)$ip=$ip.'0';
        if (strpos($ip, '::') !== false)
            $ip = str_replace('::', str_repeat(':0', 8 - substr_count($ip, ':')).':', $ip);
        if (strpos($ip, ':') === 0) $ip = '0'.$ip;
    }
    return $ip;
}

function padding_ipv6($ip="")
{
    if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true and substr_count($ip, ':')==7)
    {
        $ips=explode(':',$ip);
        $num=0;
        $ip='';
        foreach($ips as $var)
        {
            if($num)
            $ip=$ip.':'.str_repeat('0', 4 - strlen($var)).$var;
            else
            $ip=str_repeat('0', 4 - strlen($var)).$var;
            $num=$num+1;
        }
    }
    return $ip;
}

function getRealIpAddr()
{
   if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip=$_SERVER['HTTP_CLIENT_IP'];
   elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //proxy penetrate
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
   else
        $ip=$_SERVER['REMOTE_ADDR'];
   return $ip;
}

function iptoasn6($ip)
{	
	if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)!=true)return "IPError";
	$ipl=ExpandIPv6Notation($ip);
	$ipf=padding_ipv6($ipl);
	$ipt=explode(':',$ipf);
   	$domain=$ipt[7].'.'.$ipt[6].'.'.$ipt[5].'.'.$ipt[4].'.'.$ipt[3].'.'.$ipt[2].'.'.$ipt[1].'.'.$ipt[0].'.ip6asn.sasm4.net';
	$result=dns_get_record($domain,DNS_TXT);
	$as=$result[0]['entries'][0];
	if($as==null) return 'DNSError';
	return $as;
}
function iptoasn($ip)
{
	if(filter_var($ip,FILTER_VALIDATE_IP)!=true)return "IPError";
	if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)==true)return "IPError";
	$ipt=explode('.',$ip);
   	$domain=$ipt[3].'.'.$ipt[2].'.'.$ipt[1].'.'.$ipt[0].'.ip2asn.sasm4.net';
	$result=dns_get_record($domain,DNS_TXT);
	$as=$result[0]['entries'][0];
	if($as==null) $as='Error';
	return $as;
}

function hgw($ip)
{
	$link = mysql_connect("127.0.0.1","root", "") or die('Connecting Failure!');
   	$db = mysql_select_db("video");
   	mysql_query("set names utf8", $link);

	$ipl=ExpandIPv6Notation($ip);
	$ipf=padding_ipv6($ipl);
	$ipt=explode(':',$ipf);
			
	$ipp0=hexdec($ipt[0].$ipt[1]);
	$ipp1=hexdec($ipt[2].$ipt[3]);
	$ipp2=hexdec($ipt[4].$ipt[5]);
	$ipp3=hexdec($ipt[6].$ipt[7]);
	$sql0="select count(*) from hgw where ipaddr0=$ipp0 and ipaddr1=$ipp1 and ipaddr2=$ipp2 and prefix>=96 and prefix<=128 and ipaddr3>>(128-prefix)=$ipp3>>(128-prefix)";
	$res0=mysql_query($sql0, $link);
	$row0=mysql_fetch_array($res0);
	$flag=$row0[0];	

	if($flag!=0)
	{
		$sql00="select max(prefix) from hgw where ipaddr0=$ipp0 and ipaddr1=$ipp1 and ipaddr2=$ipp2 and prefix>=96 and prefix<=128 and ipaddr3>>(128-prefix)=$ipp3>>(128-prefix)";
		$res00=mysql_query($sql00, $link);
		$pre=mysql_fetch_array($res00);
		$prefix=intval($pre[0]);
		$sql01="select hgw from hgw where ipaddr0=$ipp0 and ipaddr1=$ipp1 and ipaddr2=$ipp2 and prefix=$prefix and ipaddr3>>(128-prefix)=$ipp3>>(128-prefix)";
		$res01=mysql_query($sql01, $link);
		$hgw=mysql_fetch_array($res01);
		return $hgw[0];
	}
	else
	{
		$sql1="select count(*) from hgw where ipaddr0=$ipp0 and ipaddr1=$ipp1 and prefix>=64 and prefix<96 and ipaddr2>>(96-prefix)=$ipp2>>(96-prefix)";
		$res1=mysql_query($sql1, $link);
		$row1=mysql_fetch_array($res1);
		$flag=$flag+$row1[0];
	}		

	if($flag!=0)
	{
		$sql11="select max(prefix) from hgw where ipaddr0=$ipp0 and ipaddr1=$ipp1 and prefix>=64 and prefix<96 and ipaddr2>>(96-prefix)=$ipp2>>(96-prefix)";
		$res11=mysql_query($sql11, $link);
		$pre=mysql_fetch_array($res11);
		$prefix=intval($pre[0]);
		$sql12="select hgw from hgw where ipaddr0=$ipp0 and ipaddr1=$ipp1 and prefix=$prefix and ipaddr2>>(96-prefix)=$ipp2>>(96-prefix)";
		$res12=mysql_query($sql12, $link);
		$hgw=mysql_fetch_array($res12);
		return $hgw[0];
	}
	else
	{
		$sql2="select count(*) from hgw where ipaddr0=$ipp0 and prefix>=32 and prefix<64 and ipaddr1>>(64-prefix)=$ipp1>>(64-prefix)";
		$res2=mysql_query($sql2, $link);
		$row2=mysql_fetch_array($res2);
		$flag=$flag+$row2[0];
	}
	
	if($flag!=0)
	{
		$sql21="select max(prefix) from hgw where ipaddr0=$ipp0 and prefix>=32 and prefix<64 and ipaddr1>>(64-prefix)=$ipp1>>(64-prefix)";
		$res21=mysql_query($sql21, $link);
		$pre=mysql_fetch_array($res21);
		$prefix=intval($pre[0]);
		$sql22="select hgw from hgw where ipaddr0=$ipp0 and prefix=$prefix and ipaddr1>>(64-prefix)=$ipp1>>(64-prefix)";
		$res22=mysql_query($sql22, $link);
		$hgw=mysql_fetch_array($res22);
		return $hgw[0];
	}
	else
	{
		$sql3="select count(*) from hgw where prefix<32 and prefix>=0 and ipaddr0>>(32-prefix)=$ipp0>>(32-prefix)";
		$res3=mysql_query($sql3, $link);
		$row3=mysql_fetch_array($res3);
		$flag=$flag+$row3[0];
	}

	if($flag!=0)
	{
		$sql31="select max(prefix) from hgw where prefix<32 and prefix>=0 and ipaddr0>>(32-prefix)=$ipp0>>(32-prefix)";
		$res31=mysql_query($sql31, $link);
		$pre=mysql_fetch_array($res31);
		$prefix=intval($pre[0]);
		$sql32="select hgw from hgw where prefix=$prefix and ipaddr0>>(32-prefix)=$ipp0>>(32-prefix)";
		$res32=mysql_query($sql32, $link);
		$hgw=mysql_fetch_array($res32);
		return $hgw[0];
	}
	else
		return '::1';
}

function checkip($ip,$id)
{
	$file=fopen("https://trust.ccert.edu.cn/API/q.php?ip=".$ip."&id=".$id, "r");
	$line=fgets($file,5);
	//echo "<br>".$line;
	fclose($file);
	if(trim($line)=="yes")
		return 1;
	else
		return 0;
}

function checkipinternal($ip)
{
	$link = mysql_connect("127.0.0.1","root", "") or die('Connecting Failure!');
   	$db = mysql_select_db("cernet");
   	mysql_query("set names utf8", $link);

	$ipl=ExpandIPv6Notation($ip);
	$ipf=padding_ipv6($ipl);
	$ipt=explode(':',$ipf);
			
	$ipp0=hexdec($ipt[0].$ipt[1]);
	$ipp1=hexdec($ipt[2].$ipt[3]);
	$ipp2=hexdec($ipt[4].$ipt[5]);
	$ipp3=hexdec($ipt[6].$ipt[7]);
	$sql0="select count(*) from ipassign6 where ipaddr0=$ipp0 and ipaddr1=$ipp1 and ipaddr2=$ipp2 and prefix>=96 and prefix<=128 and ipaddr3>>(128-prefix)=$ipp3>>(128-prefix)";
	$res0=mysql_query($sql0, $link);
	$row0=mysql_fetch_array($res0);
	$flag=$row0[0];	

	if($flag==0)
	{
		$sql1="select count(*) from ipassign6 where ipaddr0=$ipp0 and ipaddr1=$ipp1 and prefix>=64 and prefix<96 and ipaddr2>>(96-prefix)=$ipp2>>(96-prefix)";
		$res1=mysql_query($sql1, $link);
		$row1=mysql_fetch_array($res1);
		$flag=$flag+$row1[0];
	}		

	if($flag==0)
	{
		$sql2="select count(*) from ipassign6 where ipaddr0=$ipp0 and prefix>=32 and prefix<64 and ipaddr1>>(64-prefix)=$ipp1>>(64-prefix)";
		$res2=mysql_query($sql2, $link);
		$row2=mysql_fetch_array($res2);
		$flag=$flag+$row2[0];
	}
	
	if($flag==0)
	{
		$sql3="select count(*) from ipassign6 where prefix<32 and prefix>=0 and ipaddr0>>(32-prefix)=$ipp0>>(32-prefix)";
		$res3=mysql_query($sql3, $link);
		$row3=mysql_fetch_array($res3);
		$flag=$flag+$row3[0];
	}
	if($flag>0)
		return 1;
	else
		return 0;
}
function bwc($bwt)
{
	if($bwt>1000000)
		{$bw1=round($bwt/1000000,2);$bw2='MB/s';}
	elseif($bwt>1000)
		{$bw1=round($bwt/1000,2);$bw2='KB/s';}
	else
		{$bw1=round($bwt,2);$bw2='B/s';}
	return array($bw1,$bw2);
}
?>
