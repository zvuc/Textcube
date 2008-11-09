// Google Map Plugin Common Library
// depends on Google Maps API

var geocoder = null;
var locationMap = null;

function GMap_addLocationMark(location_path, title, link) {
	if (!geocoder)
		geocoder = new GClientGeocoder();
	var address = location_path.replace(/\//g, ' ').trim();
	geocoder.getLocations(address, function(response) {GMap_findLocationCallback(response, address, title, link);});
}

function GMap_findLocationCallback(response, address, title, link) {
	if (!response || response.Status.code != 200) {
		// alert('Can\'t retrieve this address "'+address+'"');
	} else {
		var place = response.Placemark[0];
		var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
		var marker = new GMarker(point);
		marker.bindInfoWindowHtml('<div class="GMapInfo"><h4><a href="'+link+'">'+title+'</a></h4><p>'+address+'</p></div>');
		locationMap.addOverlay(marker);
	}
}