document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("ruleta");
    const dataScript = document.getElementById("categorias-data");
    if (!canvas || !dataScript) return;

    const categorias = JSON.parse(dataScript.textContent || "[]");
    if (categorias.length === 0) return;

    const ctx = canvas.getContext("2d");
    const centroX = canvas.width / 2;
    const centroY = canvas.height / 2;
    const radio = 275;

    function coloresPredefinidos(i) {
        const colores = [
            "#e74c3c", "#3498db", "#2ecc71", "#f39c12",
            "#9b59b6", "#1abc9c", "#34495e", "#e67e22"
        ];
        return colores[i % colores.length];
    }

    function dibujarRuleta() {
        const gradosSector = 360 / categorias.length;

        categorias.forEach((categoria, index) => {
            const inicio = (index * gradosSector) * Math.PI / 180;
            const fin = ((index + 1) * gradosSector) * Math.PI / 180;

            ctx.beginPath();
            ctx.moveTo(centroX, centroY);
            ctx.arc(centroX, centroY, radio, inicio, fin);
            ctx.closePath();

            ctx.fillStyle = categoria.color || coloresPredefinidos(index);
            ctx.fill();
            ctx.lineWidth = 2;
            ctx.strokeStyle = "#091411";
            ctx.stroke();

            const textoAngulo = inicio + ((fin - inicio) / 2);
            const textoX = centroX + Math.cos(textoAngulo) * 165;
            const textoY = centroY + Math.sin(textoAngulo) * 165;

            ctx.save();
            ctx.translate(textoX, textoY);
            ctx.rotate(textoAngulo);
            ctx.fillStyle = "#fff";
            ctx.font = "bold 19px system-ui, -apple-system, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(categoria.nombre, 0, 0);
            ctx.restore();
        });
    }

    function girarRuleta() {
        const indiceGanador = Math.floor(Math.random() * categorias.length);
        const categoria = categorias[indiceGanador];

        const gradosSector = 360 / categorias.length;
        const centroSector = (indiceGanador * gradosSector) + (gradosSector / 2);

        const vueltas = 360 * 6;
        // 270° alinea el centro del sector ganador con la flecha superior (12 en punto)
        const anguloObjetivo = (270 - centroSector + 360) % 360;
        const rotacionFinal = vueltas + anguloObjetivo;

        // requestAnimationFrame asegura que la transición CSS se dispare tras pintar la ruleta
        requestAnimationFrame(() => {
            canvas.style.transform = `rotate(${rotacionFinal}deg)`;
        });

        setTimeout(() => {
            document.getElementById("categoria_id").value = categoria.id;
            document.getElementById("nombreCategoria").textContent = categoria.nombre;
            document.getElementById("estadoRuleta").textContent = "Preparando partida...";

            setTimeout(() => {
                document.getElementById("formCategoria").submit();
            }, 1500);
        }, 5000);
    }

    dibujarRuleta();
    girarRuleta();
});