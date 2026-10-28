-- MIGRACIÓN: agrega el juego a una base preguntones_db que ya existía.
-- Ejecutar UNA sola vez.
USE preguntones_db;

ALTER TABLE partidas
    ADD estado VARCHAR(20) NOT NULL DEFAULT 'EN_CURSO' COMMENT 'EN_CURSO | TERMINADA',
    ADD pregunta_actual_id INT NULL;

-- Las partidas que ya estaban cargadas se consideran terminadas
UPDATE partidas SET estado = 'TERMINADA';

-- ============================================================
-- JUEGO: categorías, preguntas y respuestas
-- ============================================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    color CHAR(7) NOT NULL COMMENT 'Color hexadecimal que pinta la pantalla de la pregunta',
    UNIQUE KEY uk_categoria_nombre (nombre)
);

CREATE TABLE preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    enunciado VARCHAR(500) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVA' COMMENT 'ACTIVA | REPORTADA | PENDIENTE | BAJA',
    fecha_creacion TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto VARCHAR(255) NOT NULL,
    es_correcta TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
);

-- Preguntas que se le mostraron a cada partida (evita repetir dentro de una misma partida)
CREATE TABLE partida_pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partida_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta_elegida_id INT NULL,
    acerto TINYINT(1) NULL,
    fecha TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_partida_pregunta (partida_id, pregunta_id),
    FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id)
);

-- ============================================================
-- Datos de ejemplo: League of Legends
-- ============================================================

-- Si ya habías corrido una versión anterior de este script (con otras categorías),
-- descomentá este bloque ANTES de los INSERT para limpiar esos datos de ejemplo.
-- Borra también las respuestas de partidas ya jugadas: usarlo solo en bases de desarrollo.
/*
DELETE FROM partida_pregunta;
UPDATE partidas SET pregunta_actual_id = NULL;
DELETE FROM respuestas;
DELETE FROM preguntas;
DELETE FROM categorias;
*/

INSERT INTO categorias (nombre, color) VALUES
    ('Campeones', '#c89b3c'),
    ('eSports', '#e84393'),
    ('Ítems', '#27ae60'),
    ('Runas y hechizos', '#8e44ad'),
    ('Lore de Runaterra', '#2e86de');

-- Campeones

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Campeones'), '¿Qué campeón es conocido como "El Explorador Veloz"?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Teemo', 1), (@p, 'Ezreal', 0), (@p, 'Lulu', 0), (@p, 'Jinx', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Campeones'), '¿Qué habilidad de Yasuo bloquea los proyectiles enemigos?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Muro de viento', 1), (@p, 'Tormenta de acero', 0), (@p, 'Último aliento', 0), (@p, 'Barrido de hoja', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Campeones'), '¿Qué campeón es conocido como "El Poder de Demacia"?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Garen', 1), (@p, 'Jarvan IV', 0), (@p, 'Lux', 0), (@p, 'Fiora', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Campeones'), '¿Cuál de estos campeones es un yordle?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Poppy', 1), (@p, 'Katarina', 0), (@p, 'Darius', 0), (@p, 'Garen', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Campeones'), '¿Qué campeón dispara la Flecha de cristal encantada como habilidad definitiva?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Ashe', 1), (@p, 'Caitlyn', 0), (@p, 'Vayne', 0), (@p, 'Miss Fortune', 0);

-- eSports

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'eSports'), '¿A qué equipo pertenece Faker, uno de los jugadores más famosos de la historia de LoL?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'T1', 1), (@p, 'G2 Esports', 0), (@p, 'Fnatic', 0), (@p, 'Cloud9', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'eSports'), '¿Cómo se conoce al campeonato mundial anual de League of Legends?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Worlds', 1), (@p, 'MSI', 0), (@p, 'LCS', 0), (@p, 'All-Star', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'eSports'), '¿Qué equipo ganó el primer Campeonato Mundial de League of Legends en 2011?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Fnatic', 1), (@p, 'SK Telecom T1', 0), (@p, 'Taipei Assassins', 0), (@p, 'Cloud9', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'eSports'), '¿Cómo se llama el trofeo que se entrega al campeón del Mundial?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Copa del Invocador', 1), (@p, 'Copa de Runaterra', 0), (@p, 'Trofeo del Nexo', 0), (@p, 'Copa Hextech', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'eSports'), '¿Cuántos jugadores componen un equipo en una partida competitiva en la Grieta del Invocador?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '5', 1), (@p, '3', 0), (@p, '4', 0), (@p, '6', 0);

-- Ítems

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ítems'), '¿Qué ítem potencia el daño de los golpes críticos?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Filo del Infinito', 1), (@p, 'Bastón del Vacío', 0), (@p, 'Reloj de Arena de Zhonya', 0), (@p, 'Botas de Mercurio', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ítems'), '¿Qué ítem aumenta el poder de habilidad total en un porcentaje?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Sombrero Mortal de Rabadon', 1), (@p, 'Filo del Infinito', 0), (@p, 'Botas de Mercurio', 0), (@p, 'Reloj de Arena de Zhonya', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ítems'), '¿Qué botas otorgan tenacidad para reducir la duración de los efectos de control?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Botas de Mercurio', 1), (@p, 'Grebas de Berserker', 0), (@p, 'Botas de Rapidez', 0), (@p, 'Botas de Hechicero', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ítems'), '¿Qué objeto de accesorio inicial permite colocar guardianes de visión de forma gratuita?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Tótem de Vigilancia', 1), (@p, 'Lente Oráculo', 0), (@p, 'Poción de Vida', 0), (@p, 'Reloj de Arena de Zhonya', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ítems'), '¿Qué ítem permite volverse invulnerable y no seleccionable durante unos segundos?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Reloj de Arena de Zhonya', 1), (@p, 'Filo del Infinito', 0), (@p, 'Bastón del Vacío', 0), (@p, 'Sombrero Mortal de Rabadon', 0);

-- Runas y hechizos

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Runas y hechizos'), '¿Qué hechizo de invocador te teletransporta una corta distancia hacia el cursor?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Destello', 1), (@p, 'Barrera', 0), (@p, 'Ignición', 0), (@p, 'Castigo', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Runas y hechizos'), '¿Qué hechizo de invocador usan los junglas para dañar a los monstruos de la jungla?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Castigo', 1), (@p, 'Destello', 0), (@p, 'Curar', 0), (@p, 'Fantasmal', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Runas y hechizos'), '¿Cuál de estas runas pertenece al árbol de Precisión?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Conquistador', 1), (@p, 'Electrocutar', 0), (@p, 'Guardián', 0), (@p, 'Cometa Arcano', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Runas y hechizos'), '¿Cuántos hechizos de invocador puede llevar un jugador en una partida?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '2', 1), (@p, '1', 0), (@p, '3', 0), (@p, '4', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Runas y hechizos'), '¿Qué runa del árbol de Dominación inflige daño extra tras golpear con tres ataques o habilidades distintas?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Electrocutar', 1), (@p, 'Conquistador', 0), (@p, 'Guardián', 0), (@p, 'Cometa Arcano', 0);

-- Lore de Runaterra

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Lore de Runaterra'), '¿Cómo se llama la ciudad subterránea que se encuentra bajo Piltover?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Zaun', 1), (@p, 'Noxus', 0), (@p, 'Bilgewater', 0), (@p, 'Ixtal', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Lore de Runaterra'), '¿De qué región de Runaterra son originarios Darius y Draven?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Noxus', 1), (@p, 'Demacia', 0), (@p, 'Jonia', 0), (@p, 'Freljord', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Lore de Runaterra'), '¿Qué región helada y salvaje de Runaterra es el hogar de Ashe y Sejuani?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Freljord', 1), (@p, 'Shurima', 0), (@p, 'Targon', 0), (@p, 'Piltover', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Lore de Runaterra'), '¿De qué región de Runaterra provienen Yasuo e Irelia?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Jonia', 1), (@p, 'Noxus', 0), (@p, 'Demacia', 0), (@p, 'Shurima', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Lore de Runaterra'), '¿Cómo se llama la ciudad donde viven los yordles?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Ciudad Bandle', 1), (@p, 'Bilgewater', 0), (@p, 'Piltover', 0), (@p, 'Zaun', 0);
