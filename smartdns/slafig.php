<?php
function common($in)
{
	$ratio = 1;
	while($in>100)
	{
		$ratio *= 10;
		$in /= 10;
	}
	return array($in, $ratio);
}
function big_ceil($in)
{
	list($in2, $ratio) = common($in);
	return ceil($in2)*$ratio;
}
function big_floor($in)
{
	list($in2, $ratio) = common($in);
	return floor($in2)*$ratio;
}
$img_height = 200;  //画布高度
$img_width = 1000;  //画布宽度
$pinterval = 0;  // space between points
$pinterval2 = 0;  // space between points
$left = 70;  //左侧留下的宽度
$right = 40;  //右侧留下的宽度
$up = 10;  //上面留下的宽度
$down = 60;  //下面留下的宽度
$max = 1;  //最大数据值
$inx=$_GET['xaxis'];
$in3=$_GET['yvalue'];
$in4=$_GET['min'];
$in5=$_GET['max'];
$in6=$_GET['xzoom'];
$in7=$_GET['yzoom'];
$t1=$_GET['time1'];
$t2=$_GET['time2'];
$id0=$_GET['id'];
$cluster = $_GET['cluster'];
if(!$cluster) $cluster = 0;
$short = $_GET['short'];	
if(strlen($short)==0) $short=0;
$margin = 50;
if($short) {$down -= $margin; $img_height -= $margin;}
if(isset($_GET['entry']))
	$entry=strtolower($_GET['entry']);
else
	$entry='bandwidth';
$table='ipv4dnsperf';
$img_width=$img_width * $in6;
$img_height=$img_height * $in7;
$xmark = (int)($in6 * 30) ;
if($cluster)
	$ymark = (int)($cluster) ;
else
	$ymark = (int)($in7 * 3) ;
$link = mysql_connect("115.25.86.2", "pathperf", "perf@FIT4204!") or die('Connecting Failure!'); 
$db = mysql_select_db("mnt"); 
mysql_query("set names utf8", $link);

$id=$id0;

$result0 = mysql_query("select max(time) from $table where id=$id", $link);
$row0 = mysql_fetch_array($result0);
$date = $row0[0];
/*if($entry == 'latency')
	$cmd .= "and time>'2013-08-11' ";
if($entry == 'lossrate')
	$cmd .= "and time>'2013-10-31' ";
 */
if($inx=="Two_weeks")
	$where = "and TO_DAYS('$date')-TO_DAYS(time)<=15 and TO_DAYS('$date')-TO_DAYS(time)>=0 ";
else if($inx=="Month")
	$where = "and TO_DAYS('$date')-TO_DAYS(time)<=30 and TO_DAYS('$date')-TO_DAYS(time)>=0 ";
else if($inx=="--OR--")
	$where = "and TO_DAYS(time)>=TO_DAYS($t1) and TO_DAYS(time)<=TO_DAYS($t2) ";

$cmd = "select max($entry),min($entry),min(time),max(time) from $table where pagesize!=0 and id=$id and hour(time)>8 ". $where;
//file_put_contents('debug',$cmd);
$result = mysql_query($cmd, $link);
$row = mysql_fetch_array($result);
list($max, $min, $stime, $etime) = $row;
$max = big_ceil($max);
$min = big_floor($min);
$unit = '';
if($max>pow(10,7))
{
	$scale = pow(10,6);
	$max /= $scale;
	$min /= $scale;
	$unit = 'M';
}	
elseif($max>pow(10,4))
{	//$left=$left+10;
	$scale = pow(10,3);
	$max /= $scale;
	$min /= $scale;
	$unit = 'K';
}
else
	$scale = 1;
$image = imagecreate($img_width, $img_height);  //创建画布
$white = imagecolorallocate($image,0xFF,0xFF,0xFF);
$black = imagecolorallocate($image,0x00,0x00,0x00);

imageline($image, $left, $up, $left, $img_height-$down, $black);  //画纵刻度
//echo $border;
imagerectangle($image,$left,$up,$img_width-$right,$img_height-$down,$black);

$xspace=($img_width-$left-$right)/$xmark;
for ($i = 1; $i < $xmark; $i ++)  //vertical dash line 
{
    //imageline($image, $left+$i*$pinterval, $img_height-$down, $left+$i*$pinterval, $img_height-$down-6, $black);
    //imagestring($image, 4, $left+$i*$pinterval-8, $img_height-$down+4, $pre[$i], $black);
	ImageDashedLine($image,$left+$i*$xspace, $img_height-$down, $left+$i*$xspace, $up, $black);
}

for ($i=0;$i<=$ymark;$i++)
{
	imageline($image, $left, $up+($img_height-$up-$down)*$i/$ymark, $left+6, $up+($img_height-$up-$down)*$i/$ymark, $black);  //画出y轴i/$ymark刻度的值
	imagestring($image, 4, 20, $up+($img_height-$up-$down)*$i/$ymark-$ymark, round($max*($ymark-$i)/$ymark+$min*$i/$ymark,1), $black);
	ImageDashedLine($image,$left,$up+($img_height-$up-$down)*$i/$ymark,$img_width-$right,$up+($img_height-$up-$down)*$i/$ymark,$black);//plot dashedline
}

foreach( array('original','smartdns') as $token)
{
	//file_put_contents('debug',$token, FILE_APPEND);
	if($token=='original')
		$blue = imagecolorallocate($image,0x00,0x00,0xFF);//blue
	else
		$blue = imagecolorallocate($image,0xFF,0x00,0x00);//red
	$cmd = "select $entry from $table where pagesize!=0 and id=$id and hour(time)>8 and token='$token' ";
	$cmd .= $where." order by time";
	//file_put_contents('debug',$cmd.'\n', FILE_APPEND);
	$result = mysql_query($cmd, $link);
	$data = array();
	$p_x = array();
	$p_y = array();
	while ($row = mysql_fetch_array($result))
	{
	    Array_push($data, $row[0]);
	}
	
	$cou=count($data);
	
	$pinterval=(int)($img_width-$left-$right)/($cou-1);
	
	for ($i = 0; $i <$cou; $i ++)
	{
	    array_push($p_x, $left + $i * $pinterval);
	    array_push($p_y, $up + round(($img_height-$up-$down)*(1-($data[$i]/$scale-$min)/($max-$min))));
	}
	
	
	for ($i = 0; $i < $cou - 1; $i ++)
	{	
		if($cou<1000)
	    imageline($image, $p_x[$i], $p_y[$i],$p_x[$i+1],$p_y[$i+1], $blue);
	    imagefilledrectangle($image, $p_x[$i]-1, $p_y[$i]-1,$p_x[$i]+1,$p_y[$i]+1, $blue);
	}
	
}
//for ($i = 0; $i < $cou; $i ++)
//    imagestring($image, 3, $p_x[$i]+2, $p_y[$i]-12,$data[$i],$black);
if(!$short)
imagestring($image, 4, ($img_width-$right+$pinterval)/2-170, $img_height-$down+20,"Time($stime to $etime)",$black);
if($entry=='bandwidth')
imagestringup($image, 4, 0, ($up+$img_height+$margin*$short)/1.5,"Bandwidth($unit"."B/s)",$black);
if($entry=='pagesize')
imagestringup($image, 4, 0, ($up+$img_height+$margin*$short)/1.5,"Pagesize($unit"."B)",$black);
if($entry=='latency')
imagestringup($image, 4, 0, ($up+$img_height+$margin*$short)/1.5,"Latency(ms)",$black);
if($entry=='lossrate')
imagestringup($image, 4, 0, ($up+$img_height+$margin*$short)/1.5,"Lossrate(%)",$black);
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>
