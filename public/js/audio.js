(function () {
    var CLAVE_SILENCIADA = 'musica_silenciada';
    var CLAVE_TIEMPO = 'musica_tiempo';

    var boton = document.getElementById('boton-musica');
    if (!boton) return;

    // localStorage puede no estar disponible (modo privado, datos bloqueados): se envuelve en try/catch
    function leer(clave) {
        try { return localStorage.getItem(clave); } catch (e) { return null; }
    }
    function guardar(clave, valor) {
        try { localStorage.setItem(clave, valor); } catch (e) { /* sin memoria: igual funciona */ }
    }

    var audio = new Audio(boton.dataset.src);
    audio.loop = true;
    audio.volume = 0.3;

    var silenciada = leer(CLAVE_SILENCIADA) === '1';

    // Retomar desde el segundo en que se dejó (hay que esperar a que cargue el audio)
    var tiempoGuardado = parseFloat(leer(CLAVE_TIEMPO));
    if (!isNaN(tiempoGuardado)) {
        audio.addEventListener('loadedmetadata', function () {
            if (tiempoGuardado < audio.duration) audio.currentTime = tiempoGuardado;
        }, { once: true });
    }

    function pintarBoton() {
        boton.setAttribute('aria-pressed', silenciada ? 'true' : 'false');
        boton.setAttribute('aria-label', silenciada ? 'Activar música' : 'Silenciar música');
        boton.querySelector('.music-icon').className = 'bi music-icon ' + (silenciada ? 'bi-volume-mute-fill' : 'bi-volume-up-fill');
        boton.querySelector('.music-text').textContent = silenciada ? 'Música apagada' : 'Música';
    }

    function reproducir() {
        if (silenciada) return;
        var promesa = audio.play();
        if (promesa && promesa.catch) {
            // El navegador bloquea el sonido hasta que el usuario toca algo: se reintenta al primer gesto
            promesa.catch(function () {
                var eventos = ['click', 'keydown', 'touchstart'];
                function alPrimerGesto() {
                    eventos.forEach(function (e) { document.removeEventListener(e, alPrimerGesto); });
                    reproducir();
                }
                eventos.forEach(function (e) { document.addEventListener(e, alPrimerGesto); });
            });
        }
    }

    boton.addEventListener('click', function () {
        silenciada = !silenciada;
        guardar(CLAVE_SILENCIADA, silenciada ? '1' : '0');
        if (silenciada) {
            audio.pause();
        } else {
            audio.play();
        }
        pintarBoton();
    });

    // Guardar en qué segundo va, para seguir desde ahí en la próxima página
    window.addEventListener('pagehide', function () {
        guardar(CLAVE_TIEMPO, String(audio.currentTime));
    });

    pintarBoton();
    reproducir();
})();