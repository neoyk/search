<?php
function getRealIpAddr()
{
    if(!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else
		$ip=$_SERVER['REMOTE_ADDR'];
	/*if(strpos(',',$ip)!==false){
		list($ip1,$ip2) = explode(',',$ip);
		$ip = $ip2;
}*/
    return $ip;
}

function getASN($in)
{
	$adds = explode(".", $in);
    $domain=$adds[3].'.'.$adds[2].'.'.$adds[1].'.'.$adds[0].'.ip2asn.sasm4.net';
	$result=dns_get_record($domain,DNS_TXT);
    $as = $result[0]['txt'];
	if(preg_match("/AS/",$as))
	{
		$asn=explode("AS",$as);
		return intval($asn[1]);
	}
	elseif("No Record"==$as)
		return 0;
	return -1;
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
            //echo $var.'<br>';
            if($num)
            $ip=$ip.':'.str_repeat('0', 4 - strlen($var)).$var;
            else
            $ip=str_repeat('0', 4 - strlen($var)).$var;
            $num=$num+1;
        }
    }
    return $ip;
}

function getASN6($in)
{
    $ipn=inet_pton($in);
    $ipp=inet_ntop($ipn);
    $ipe=ExpandIPv6Notation($ipp);
    $ipf=padding_ipv6($ipe);

    $adds = explode(":", $ipe);
    $domain=$adds[7].'.'.$adds[6].'.'.$adds[5].'.'.$adds[4].'.'.$adds[3].'.'.$adds[2].'.'.$adds[1].'.'.$adds[0].'.ip6asn.sasm4.net';
	$result=dns_get_record($domain,DNS_TXT);
    $as = $result[0]['txt'];
	if(preg_match("/AS/",$as))
	{
		$asn=explode("AS",$as);
		return intval($asn[1]);
	}
	elseif("No Record"==$as)
		return 0;
	return -1;
}

function calcutime()
{
    $time = explode( " ", microtime());
    $usec = (float)$time[0];
    $sec = (float)$time[1];
    return $sec + $usec;
}
function now()
{
    $time = explode(" ",microtime());
    return $time[0] + $time[1];
}

function serverload()
{
	exec("uptime",$result);
	if( preg_match("%.*load average:\s([^,]+),.*%", $result[count($result)-1], $matches))
		return $matches[1];
	else
		return 0;
}
function latency($domain, $ipv)
{	
	if(strtoupper($ipv)=='IPV6')
    $cmd = "ping6 -c3 -w1 -l3 ".$domain;
else
    $cmd = "ping -c3 -w1 -l3 ".$domain;
	//echo strtoupper($ipv).' '.$cmd;
	$exec = exec($cmd, $result);
	if( preg_match("%rtt min/avg/max/mdev = ([^/]+)/.*\s(.*),\s%", $result[count($result)-1], $matches))
	{
	    //print_r($matches);
	    if (strtoupper($matches[2])=='S')
	        $latency = $matches[1] * 1000;
	    else
	        $latency = $matches[1];
	
	}else
	    $latency = -1;
	return array($result, $latency);
}
?>
