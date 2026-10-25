const map = L.map('map').setView(
    [
        MAP_CONFIG.defaultLat,
        MAP_CONFIG.defaultLng
    ],
    MAP_CONFIG.defaultZoom
);
let marker = null;

L.tileLayer(
    MAP_CONFIG.tileLayer,
    {
        attribution: '&copy; OpenStreetMap'
    }
).addTo(map);

if (MAP_CONFIG.modoEdicion){
    map.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;
        console.log(lat, lng);
    });
}