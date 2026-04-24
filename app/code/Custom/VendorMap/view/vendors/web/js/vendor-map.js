// require([
//     'jquery'
// ], function ($) {

//     $(document).ready(function () {

//        window.initVendorMap = function () {

//     console.log('Google Map Init Called');

//     var input = document.getElementById('vendor_map');

//     if (!input) {
//         console.log('Input not found');
//         return;
//     }

//     // prevent duplicate map
//     if (document.getElementById('vendor-google-map')) {
//         return;
//     }

//     var mapDiv = document.createElement('div');
//     mapDiv.id = 'vendor-google-map';
//     mapDiv.style.height = '300px';
//     mapDiv.style.marginTop = '10px';
//     mapDiv.style.border = '1px solid #ccc';

//     input.parentNode.appendChild(mapDiv);

//     var defaultLocation = { lat: 28.6139, lng: 77.2090 };

//     var map = new google.maps.Map(mapDiv, {
//         center: defaultLocation,
//         zoom: 10
//     });

//     var marker = new google.maps.Marker({
//         position: defaultLocation,
//         map: map,
//         draggable: true
//     });

//     var autocomplete = new google.maps.places.Autocomplete(input);

//     autocomplete.addListener('place_changed', function () {
//         var place = autocomplete.getPlace();

//         if (!place.geometry) return;

//         map.setCenter(place.geometry.location);
//         marker.setPosition(place.geometry.location);

//         input.value =
//             place.formatted_address +
//             ' | ' +
//             place.geometry.location.lat() +
//             ',' +
//             place.geometry.location.lng();
//     });

//     marker.addListener('dragend', function () {
//         input.value =
//             marker.getPosition().lat() +
//             ',' +
//             marker.getPosition().lng();
//     });
// };

//         // wait until google loads
//         var interval = setInterval(function () {
//             if (typeof google !== 'undefined') {
//                 clearInterval(interval);
//                 initVendorMap();
//             }
//         }, 500);

//     });
// });