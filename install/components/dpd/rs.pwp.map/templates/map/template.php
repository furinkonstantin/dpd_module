<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<div id="map_dpd"></div>
<script>	
	var myMap;
	ymaps.ready(init);
	var startCoords = JSON.parse('<?=$arResult['START_COORDS']?>');
	var markers = JSON.parse('<?=$arResult['ITEMS']?>');
	var dpdMarkers = [];
	function init () {
			myMap = new ymaps.Map('map_dpd', {
					center: [startCoords["latitude"], startCoords["longitude"]],
					zoom: 12
			});
			
			for (i in markers) {
					var marker = markers[i];
					myPlacemark = new ymaps.Placemark([marker.coordinates["latitude"], marker.coordinates["longitude"]], {
							balloonContentHeader: marker.nameinput,
							balloonContentBody: markers[i].content,
							hintContent: marker.address
					},
					{
						code: marker.terminalCode
					});

					myMap.geoObjects.add(myPlacemark);
					dpdMarkers.push(myPlacemark);
			}
			$("input#TERMINAL_CODE").on("click", function() {
				for (i in dpdMarkers) {
					if (dpdMarkers[i].options.get("code") == $(this).val()) {
						var coords = dpdMarkers[i].geometry.getCoordinates();
						dpdMarkers[i].balloon.open();
						myMap.setCenter(coords);
						break;
					}
				}
			});
	}
</script>