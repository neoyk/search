<?php
$k = 2;
$id = $_GET['id']; if($id == null) $id = 8;
$link = mysql_connect("127.0.0.1", "root", "") or die('Connecting Failure!');
$db = mysql_select_db("mnt");
mysql_query("set names utf8", $link);
$cmd = "select latency from web_perf where id = $id and time > '20130820' ";
$result = mysql_query($cmd, $link);
$data = array();
while ($row = mysql_fetch_array($result))
		Array_push($data, $row[0]);
print_r($data);
$pwd = getcwd();
//echo 'python /var/www/html/sla/kmeans.py 2 ' . implode('-',$data);
exec("python /var/www/html/sla/kmeans.py $k " . implode('-',$data)."> $pwd/latency_temp");
echo "\nResult:\n";
$data = array_map('floatval',file("$pwd/latency_temp"));
$max = max($data);
echo $max, $max+1;
?>
