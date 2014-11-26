<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>SLA - IPv4 Web server distribution</title>
	<META HTTP-EQUIV="Refresh" content="3600"> 
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script>
var citymap = {};
var mergeline = {};
var flightPath = {};
<?php
require('data.php');
$lines = file('merge.txt');
foreach ($lines as $line_num => $line) {
	//2 (39.928899999999999, 116.38800000000001) 1458 (31.0456, 121.40000000000001) 1.05406193129e-05 1086.87
	//if($line_num>80)break;
	$parts = explode(' ',$line);
	echo "mergeline['$line_num']=[
		new google.maps.LatLng$parts[1]$parts[2],
		new google.maps.LatLng$parts[4]$parts[5]];
		\n";
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
	    zoom: 2,
        center: new google.maps.LatLng(39.9289,116.388),
        mapTypeId: google.maps.MapTypeId.TERRAIN
    };
  
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	var red = {
		url:'../img/red.gif',
		anchor: new google.maps.Point(8, 12),
		scaledSize: new google.maps.Size(16, 16)
	};
	var yellow = {
		url:'../img/yellow.gif',
		anchor: new google.maps.Point(8, 12),
		scaledSize: new google.maps.Size(16, 16)
	};
	var green = {
		url:'../img/green.gif',
		anchor: new google.maps.Point(8, 12),
		scaledSize: new google.maps.Size(16, 16)
	};
	var infowindow2 = new google.maps.InfoWindow({content: "Pathperf probe point: <a target=blank href=http://www.edu.cn/>CERNET Headquarter</a>" });
    var marker2 = new google.maps.Marker({
		position: new google.maps.LatLng(39.99316,116.330199),
		map: map, title: 'Pathperf probe location'
	});
	google.maps.event.addListener(marker2, 'click', function() {infowindow2.open(map,marker2);});
    var infowindow = new google.maps.InfoWindow({content: ""});
	for (var city in citymap) {
		if(citymap[city].color=='red')
		var marker = new google.maps.Marker({ position: citymap[city].center, map: map, icon:red, title:"Web server information" });
		if(citymap[city].color=='yellow')
		var marker = new google.maps.Marker({ position: citymap[city].center, map: map, icon:yellow, title:"Web server information" });
		if(citymap[city].color=='green')
		var marker = new google.maps.Marker({ position: citymap[city].center, map: map, icon:green, title:"Web server information" });
		//add a click event to the circle
		bindInfoWindow(marker, map, infowindow, citymap[city].description);
			google.maps.event.addListener(marker, 'click', function(){infoWindow.open(map, marker);});
	}
	/*
	var flightPlanCoordinates = [
      new google.maps.LatLng(37.772323, -122.214897),
      new google.maps.LatLng(21.291982, -157.821856)
    ];
    var straightline = [
      new google.maps.LatLng(37.772323, 122.214897),
      new google.maps.LatLng(21.291982, 157.821856),
	];
*/
	for (var line in mergeline) {
    	flightPath[line] = new google.maps.Polyline({
     		path: mergeline[line],
      		geodesic: true,
      		strokeColor: '#FF0000',
      		strokeOpacity: 1.0,
      		strokeWeight: 1.2
    	});
	}  
	//addLine();
	map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById('legend'));

}
function bindInfoWindow(marker, map, infowindow, strDescription) {
	    google.maps.event.addListener(marker, 'click', function() {
		infowindow.setContent(strDescription);
		infowindow.open(map, marker);
		});
}
function addLine() {
	for (var line in flightPath) {
  		flightPath[line].setMap(map);
	}
}

function removeLine() {
	for (var line in flightPath) {
  		flightPath[line].setMap(null);
	}
}

google.maps.event.addDomListener(window, 'load', initialize);

</script>
</head>
<body>
<!--
	<div id="panel">
    	<input onclick="removeLine();" type=button value="Hide merge line">
    	<input onclick="addLine();" type=button value="Restore merge line">
	</div>
-->
<div id="panel">
<?php
$callpage = basename($_SERVER['SCRIPT_FILENAME']);
echo "<form action=\"$callpage\">";
echo "Date &amp; time: <input type=\"datetime-local\" name=\"datetime\" value = \"$datetime\">";
?>
  <input type="submit">
</form>
	</div>
	<div id="map-canvas"></div>

    <div id="legend">
		<h3>Legend</h3>
    	<img src="../img/green.gif" alt="Green Star" height="24" width="24">&gt;2MB/s<br />
    	<img src="../img/yellow.gif" alt="Yellow Star" height="24" width="24">&gt;200KB/s<br />
    	<img src="../img/red.gif" alt="Red Star" height="24" width="24">&lt;200KB/s<br />
    </div>  
</body>
</html>
