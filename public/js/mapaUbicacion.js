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
    map.on('click', async function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;

        try {

            const response = await fetch(
                `${MAP_CONFIG.reverseGeocode}?format=json&lat=${lat}&lon=${lng}`
            );

            const data = await response.json();
            document.getElementById('pais').value =
                data.address.country || '';

            document.getElementById('ciudad').value =
                data.address.city ||
                data.address.town ||
                data.address.village ||
                data.address.municipality ||
                '';

        }
        catch(error) {

            console.error(
                'Error obteniendo país y ciudad',
                error
            );

        }
    });
}