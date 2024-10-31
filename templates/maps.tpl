<div id="map" style="height: 600px;"></div>

<script>
var map;

function createMarker(name,lat,lng,id,result_icon) {
    var info_content = name+": <a href='#"+id+"'>Jump to</a>";
    var info_window = new google.maps.InfoWindow({ content: info_content });

    var marker = new google.maps.Marker({
      map: map,
      icon: {
        url: result_icon,
        anchor: new google.maps.Point(20, 20),
        scaledSize: new google.maps.Size(20, 20)
      },
      title: name,
      position: new google.maps.LatLng(lat,lng),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });
    marker.addListener('click', function() {
        info_window.open(map,marker);
    });
}


function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
{literal}
          center: {lat: {/literal}{$geolocation[0]}{literal},lng: {/literal}{$geolocation[1]}{literal}},
          scrollwheel: false,
          zoom: 11
        });

{/literal}

{foreach from=$results_combined item=result name=foo}       
	{if isset($result['id'])}
		createMarker('{$result['name']|escape}',{$result['latitude']},{$result['longitude']},'{$result['id']}','{$result['icon']}');
	{/if}		
{/foreach}
}

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={$maps_api_key}&callback=initMap&libraries=places"></script>
