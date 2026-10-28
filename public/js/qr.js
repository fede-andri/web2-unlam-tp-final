// Dibuja un QR dentro de cada elemento con data-qr-url.
// Requiere la librería qrcode-generator cargada antes (ver components/qrView.mustache).
(function () {
    document.querySelectorAll('[data-qr-url]').forEach(function (el) {
        var qr = qrcode(0, 'M');
        qr.addData(el.dataset.qrUrl);
        qr.make();

        el.innerHTML = qr.createSvgTag({
            cellSize: 4,
            margin: 0,
            scalable: true,
            alt: el.dataset.qrAlt || 'Código QR',
            title: el.dataset.qrAlt || 'Código QR'
        });
    });
})();
