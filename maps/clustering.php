<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>clustering</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script>
var citymap = {};
<?php
	$num = intval($_GET['num']);
	if($num==0) $num=34;
	$asn = $_GET['asn'];
	if(empty($asn))$asn='as4538';
	$pwd = getcwd();
	exec("python linkage.py $asn 2>&1",$output);
	$count = 1;
	$numbers = explode(' ',$output[0]);
	echo "//total points:".$numbers[0].", merged into ".$numbers[1]." clusters.\n";
	array_splice($output,0,1);
	foreach ($output as $point)
	{
		$list = explode(' ',$point);
		echo "citymap['$count'] = {
			center: new google.maps.LatLng($list[1],$list[2]),
			population: $list[0]
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
    center: new google.maps.LatLng(35, 100),
    mapTypeId: google.maps.MapTypeId.TERRAIN
  };

  var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  // Construct the circle for each value in citymap.
  for (var city in citymap) {
  	if(citymap[city].population<500)
	{
		var populationOptions = 
  		{
      	strokeColor: '#0000FF',
      	strokeOpacity: 0.8,
      	strokeWeight: 1,
      	fillColor: '#0000FF',
      	fillOpacity: 0.35,
      	map: map,
      	center: citymap[city].center,
      	radius: 50000
	  	}
	// Add the circle for this city to the map.
	cityCircle = new google.maps.Circle(populationOptions);
   }
	else if(citymap[city].population<2000)
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
      	radius: 50000
	  	}
	cityCircle = new google.maps.Circle(populationOptions);
   }
	else if(citymap[city].population<20000)
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
      	radius: 50000
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
	<p id=small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>&nbsp;&lt;500<br />
	<p id=median>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>&nbsp;&lt;2000<br />
	<p id=large>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>&nbsp;&lt;20000<br />
	</div>  
</body>
</html>
