<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>SLA - IPv6 Web server distribution</title>
	<META HTTP-EQUIV="Refresh" content="3600"> 
	<style>
      html, body, #map-canvas { height: 100%; margin: 0px; padding: 0px }
    #panel {
        position: absolute;
        bottom: 20px;
        right: 5px;
        margin-left: -180px;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
      }
</style>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script>
var citymap = {};
var flightPath = {};
<?php
require('mysqllogin.php');
$db_name = 'mnt';
$link = new mysqli($db_host, $db_user, $db_pass, $db_name);
$link2 = new mysqli($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) 
{
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$sql = "select count(*) as num,longitude,latitude,webdomain,id,location,performance,aspath from ipv6server where aspath is not null group by location having longitude is not null";
$result = $link->query($sql) or die($link->error.__LINE__);
$count = 1;
while($row = $result->fetch_assoc())
{
	$des = $row['location']."<br># of websites: ".$row['num']."<br>Eg: <a target=blank href=http://".$row['webdomain'].">".$row['webdomain']."</a>,&nbsp;AS path:&nbsp;".$row['aspath']."<br>".$row['performance']."<br>Plot: ";
	if($row['num']>1)
	{
		$num = 0;
		$sql = "select id from mnt.ipv6server where ip!='0' and location='".$row['location']."'";
		$result2 = $link2->query($sql) or die($link2->error.__LINE__);
		while($row2 = $result2->fetch_assoc())
		{
			$des .= "&nbsp;<a target=blank href=index.php?version=6&kid=".$row2['id'].">".$row2['id']."</a>";
			$num += 1;
			if($num>9)
			{$des .= "<br>";$num=0;}
		}
	}
	else
		$des .= "<a target=blank href=index.php?version=6&kid=".$row['id'].">".$row['id']."</a>";

    echo "citymap['$count'] = {
        center: new google.maps.LatLng($row[latitude],$row[longitude]),
        population: $row[num],
        description: \"$des\"
    };\n";
    $count += 1;
}
?>
//citymap['losangeles'] = {
//  center: new google.maps.LatLng(34.052234, -118.243684),
//  population: 3844829
//};
var map;
function initialize() {
    // Create the map.
    var mapOptions = {
	    zoom: 4,
        center: new google.maps.LatLng(39.9289,116.388),
        mapTypeId: google.maps.MapTypeId.TERRAIN
    };
  
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	var image = '../img/red.gif';
	var infowindow2 = new google.maps.InfoWindow({content: "Pathperf probe point: <a target=blank href=http://www.edu.cn/>CERNET Headquarter</a>" });
    var marker2 = new google.maps.Marker({
		position: new google.maps.LatLng(39.99316,116.330199),
		map: map, title: 'Pathperf probe location'
	});
	google.maps.event.addListener(marker2, 'click', function() {infowindow2.open(map,marker2);});
    var infowindow = new google.maps.InfoWindow({content: ""});
    for (var city in citymap) {
		var marker = new google.maps.Marker({ position: citymap[city].center, map: map, icon:image, title:"Web server information" });
		//add a click event to the circle
		bindInfoWindow(marker, map, infowindow, citymap[city].description);
			google.maps.event.addListener(marker, 'click', function(){infoWindow.open(map, marker);});
	}
    var flightPlanCoordinates = [
      new google.maps.LatLng(37.772323, -122.214897),
      new google.maps.LatLng(21.291982, -157.821856)
    ];
    var straightline = [
      new google.maps.LatLng(37.772323, 122.214897),
      new google.maps.LatLng(21.291982, 157.821856),
    ];
}
function bindInfoWindow(marker, map, infowindow, strDescription) {
	    google.maps.event.addListener(marker, 'click', function() {
		infowindow.setContent(strDescription);
		infowindow.open(map, marker);
		});
}

	google.maps.event.addDomListener(window, 'load', initialize);

    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
  </body>
</html>
