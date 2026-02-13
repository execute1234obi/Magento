require([
    'jquery',
    'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places'
], function ($) {

    $(document).ready(function () {

        setTimeout(function () {

            var $input = $('#vendor_map');
            if (!$input.length) {
                console.log('Vendor map field not found');
                return;
            }

            if ($('#vendor-google-map').length) {
                return;
            }

            var mapDiv = $('<div id="vendor-google-map" style="height:300px;margin-top:10px;border:1px solid #ccc;"></div>');
            $input.after(mapDiv);

            var defaultLocation = { lat: 28.6139, lng: 77.2090 };

            var map = new google.maps.Map(document.getElementById('vendor-google-map'), {
                center: defaultLocation,
                zoom: 10
            });

            var marker = new google.maps.Marker({
                map: map,
                position: defaultLocation,
                draggable: true
            });

            var autocomplete = new google.maps.places.Autocomplete($input[0]);

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (!place.geometry) return;

                map.setCenter(place.geometry.location);
                marker.setPosition(place.geometry.location);

                $input.val(
                    place.formatted_address +
                    ' | ' +
                    place.geometry.location.lat() +
                    ',' +
                    place.geometry.location.lng()
                );
            });

            marker.addListener('dragend', function () {
                $input.val(
                    marker.getPosition().lat() +
                    ',' +
                    marker.getPosition().lng()
                );
            });

        }, 1000);

    });
});
