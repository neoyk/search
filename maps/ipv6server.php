<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>IPv6 web server distribution</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script>

var citymap = {};
<?php
	$link = mysql_connect("127.0.0.1","root", "") or die('Connection Failure!'); 
	$db = mysql_select_db("webserver");
	$sql = "select count(*) as num,longitude,latitude from ipv6server where longitude is not null group by longitude,latitude";
	$result = mysql_query($sql, $link);
	$count = 1;
	while($row = mysql_fetch_array($result))
	{
		echo "citymap['$count'] = {
			center: new google.maps.LatLng($row[2],$row[1]),
			population: $row[0]
		};";
		$count += 1;
	}
?>
//citymap['chicago'] = {
//  center: new google.maps.LatLng(41.878113, -87.629798),
//  population: 2842518
//};
//citymap['newyork'] = {
//  center: new google.maps.LatLng(40.714352, -74.005973),
//  population: 8143197
//};
//citymap['losangeles'] = {
//  center: new google.maps.LatLng(34.052234, -118.243684),
//  population: 3844829
//};
var cityCircle;
var marker;
function initialize() {
  // Create the map.
  var mapOptions = {
    zoom: 4,
    center: new google.maps.LatLng(37.09024, -95.712891),
    mapTypeId: google.maps.MapTypeId.TERRAIN
  };

  var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  // Construct the circle for each value in citymap.
  for (var city in citymap) {
  	if(citymap[city].population<10)
	{
		var populationOptions = 
  		{
      	strokeColor: '#0000FF',
      	strokeOpacity: 0.8,
      	strokeWeight: 1,
      	fillColor: '#0000FF',
      	fillOpacity: 0.35,
		clickable: true,
		map: map,
		center: citymap[city].center,
      	radius: 70000
	  	}
	// Add the circle for this city to the map.
		cityCircle = new google.maps.Circle(populationOptions);
   }
	else if(citymap[city].population<100)
	{
		var populationOptions = 
  		{
      	strokeColor: '#9933FA',
      	strokeOpacity: 0.8,
      	strokeWeight: 1,
      	fillColor: '#9933FA',
      	fillOpacity: 0.35,
      	map: map,
      	center: citymap[city].center,
      	radius: 70000
	  	}
		cityCircle = new google.maps.Circle(populationOptions);
   }
	else if(citymap[city].population<1000)
	{
		var populationOptions = 
  		{
      	strokeColor: '#FF0000',
      	strokeOpacity: 0.8,
      	strokeWeight: 1,
      	fillColor: '#FF0000',
      	fillOpacity: 0.35,
      	map: map,
      	center: citymap[city].center,
      	radius: 70000
	  	}
		cityCircle = new google.maps.Circle(populationOptions);
   }
	else 
	{
		marker = new google.maps.Marker({ position: citymap[city].center, map: map });
	}
  }
	map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById('legend'));

}
    google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
    <div id="legend">
	<h3>Legend</h3>
	<p id=small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>&nbsp;&lt;10<br />
	<p id=median>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>&nbsp;&lt;100<br />
	<p id=large>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>&nbsp;&lt;1000<br />
	<img src="marker.png" alt="Marker" height="30" width="18">&nbsp;&gt;1000<br />
	</div>  
</body>
</html>
