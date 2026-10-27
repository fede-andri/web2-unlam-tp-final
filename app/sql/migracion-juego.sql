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

-- Datos de ejemplo

INSERT INTO categorias (nombre, color) VALUES
    ('Historia', '#d4a017'),
    ('Deportes', '#27ae60'),
    ('Cultura', '#8e44ad'),
    ('Ciencia', '#2e86de'),
    ('Geografía', '#e67e22'),
    ('Entretenimiento', '#e84393');

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Historia'), '¿En qué año se declaró la independencia argentina?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '1816', 1), (@p, '1810', 0), (@p, '1853', 0), (@p, '1825', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Historia'), '¿Quién comandó el Ejército de los Andes en el cruce de la cordillera?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'José de San Martín', 1), (@p, 'Manuel Belgrano', 0), (@p, 'Martín Miguel de Güemes', 0), (@p, 'Mariano Moreno', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Historia'), '¿En qué año comenzó la Segunda Guerra Mundial?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '1939', 1), (@p, '1914', 0), (@p, '1941', 0), (@p, '1945', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Historia'), '¿Qué civilización construyó Machu Picchu?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Inca', 1), (@p, 'Maya', 0), (@p, 'Azteca', 0), (@p, 'Olmeca', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Historia'), '¿En qué año cayó el Muro de Berlín?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '1989', 1), (@p, '1961', 0), (@p, '1975', 0), (@p, '1991', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Deportes'), '¿Cuántos jugadores de un mismo equipo hay en la cancha en un partido de fútbol?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '11', 1), (@p, '9', 0), (@p, '10', 0), (@p, '12', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Deportes'), '¿Qué selección ganó el Mundial de fútbol de Qatar 2022?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Argentina', 1), (@p, 'Francia', 0), (@p, 'Brasil', 0), (@p, 'Croacia', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Deportes'), '¿En qué deporte se disputa el torneo de Wimbledon?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Tenis', 1), (@p, 'Golf', 0), (@p, 'Rugby', 0), (@p, 'Cricket', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Deportes'), '¿Cada cuántos años se celebran los Juegos Olímpicos de verano?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '4', 1), (@p, '2', 0), (@p, '3', 0), (@p, '5', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Deportes'), '¿En qué país nació el rugby?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Inglaterra', 1), (@p, 'Francia', 0), (@p, 'Nueva Zelanda', 0), (@p, 'Australia', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Cultura'), '¿Quién pintó La Gioconda (Mona Lisa)?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Leonardo da Vinci', 1), (@p, 'Miguel Ángel', 0), (@p, 'Rafael', 0), (@p, 'Botticelli', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Cultura'), '¿Quién escribió Don Quijote de la Mancha?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Miguel de Cervantes', 1), (@p, 'Lope de Vega', 0), (@p, 'Francisco de Quevedo', 0), (@p, 'Jorge Luis Borges', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Cultura'), '¿Quién escribió el cuento El Aleph?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Jorge Luis Borges', 1), (@p, 'Julio Cortázar', 0), (@p, 'Ernesto Sábato', 0), (@p, 'Adolfo Bioy Casares', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Cultura'), '¿Qué instrumento es característico del tango?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Bandoneón', 1), (@p, 'Sitar', 0), (@p, 'Banjo', 0), (@p, 'Gaita', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Cultura'), '¿Quién compuso la Novena Sinfonía?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Ludwig van Beethoven', 1), (@p, 'Wolfgang Amadeus Mozart', 0), (@p, 'Johann Sebastian Bach', 0), (@p, 'Frédéric Chopin', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ciencia'), '¿Cuál es el planeta más grande del sistema solar?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Júpiter', 1), (@p, 'Saturno', 0), (@p, 'Neptuno', 0), (@p, 'Tierra', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ciencia'), '¿Cuál es el símbolo químico del oro?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Au', 1), (@p, 'Ag', 0), (@p, 'Fe', 0), (@p, 'Go', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ciencia'), '¿Cuántos huesos tiene el cuerpo de un adulto?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '206', 1), (@p, '201', 0), (@p, '212', 0), (@p, '300', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ciencia'), '¿Qué gas absorben las plantas durante la fotosíntesis?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Dióxido de carbono', 1), (@p, 'Oxígeno', 0), (@p, 'Nitrógeno', 0), (@p, 'Helio', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Ciencia'), '¿Cuál es la velocidad aproximada de la luz en el vacío?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, '300.000 km/s', 1), (@p, '30.000 km/s', 0), (@p, '150.000 km/s', 0), (@p, '1.000.000 km/s', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Geografía'), '¿Cuál es la capital de Australia?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Canberra', 1), (@p, 'Sídney', 0), (@p, 'Melbourne', 0), (@p, 'Perth', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Geografía'), '¿Cuál es el río más caudaloso de Sudamérica?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Amazonas', 1), (@p, 'Paraná', 0), (@p, 'Orinoco', 0), (@p, 'Magdalena', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Geografía'), '¿Cuál es la montaña más alta de América?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Aconcagua', 1), (@p, 'Denali', 0), (@p, 'Huascarán', 0), (@p, 'Chimborazo', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Geografía'), '¿En qué continente se encuentra Egipto?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'África', 1), (@p, 'Asia', 0), (@p, 'Europa', 0), (@p, 'Oceanía', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Geografía'), '¿Cuál es el país con mayor superficie del mundo?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Rusia', 1), (@p, 'Canadá', 0), (@p, 'China', 0), (@p, 'Estados Unidos', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Entretenimiento'), '¿Quién es el joven mago creado por J. K. Rowling?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Harry Potter', 1), (@p, 'Percy Jackson', 0), (@p, 'Frodo Bolsón', 0), (@p, 'Merlín', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Entretenimiento'), '¿Quién dirigió la película Titanic (1997)?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'James Cameron', 1), (@p, 'Steven Spielberg', 0), (@p, 'Martin Scorsese', 0), (@p, 'Christopher Nolan', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Entretenimiento'), '¿Cómo se llama el fontanero bigotudo emblema de Nintendo?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'Mario', 1), (@p, 'Sonic', 0), (@p, 'Link', 0), (@p, 'Donkey Kong', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Entretenimiento'), '¿Quién escribió El Señor de los Anillos?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'J. R. R. Tolkien', 1), (@p, 'C. S. Lewis', 0), (@p, 'George R. R. Martin', 0), (@p, 'Roald Dahl', 0);

INSERT INTO preguntas (categoria_id, enunciado) VALUES ((SELECT id FROM categorias WHERE nombre = 'Entretenimiento'), '¿Qué banda británica publicó el álbum Abbey Road?');
SET @p = LAST_INSERT_ID();
INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (@p, 'The Beatles', 1), (@p, 'The Rolling Stones', 0), (@p, 'Queen', 0), (@p, 'Pink Floyd', 0);
