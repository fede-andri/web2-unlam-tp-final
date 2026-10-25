CREATE DATABASE preguntones_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE preguntones_db;

-- preguntones_db.usuarios definition

CREATE TABLE `usuarios` (
                            `id` int NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `usuario` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `fecha_nacimiento` date NOT NULL,
                            `sexo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `pais` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                            `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
                            `rol` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'JUGADOR',
                            `validado` tinyint(1) NOT NULL DEFAULT '0',
                            `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                            `latitud` decimal(10,8) DEFAULT NULL,
                            `longitud` decimal(11,8) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `usuario` (`usuario`),
                            UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Si la tabla usuarios ya la crearon correr el siguiente sql
/*
ALTER TABLE usuarios
ADD nombre VARCHAR(100) NOT NULL,
ADD fecha_nacimiento DATE NOT NULL,
ADD sexo VARCHAR(30) NOT NULL,
ADD pais VARCHAR(100) NOT NULL,
ADD ciudad VARCHAR(100) NOT NULL,
ADD foto VARCHAR(255) DEFAULT 'default.png',
ADD rol VARCHAR(20) NOT NULL DEFAULT 'JUGADOR',
ADD validado BOOLEAN NOT NULL DEFAULT FALSE,
ADD fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
*/
CREATE TABLE partidas (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          usuario_id INT NOT NULL,
                          puntaje INT NOT NULL DEFAULT 0,
                          fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO partidas (usuario_id, puntaje) VALUES (1, 5),(1,9), (2, 8), (3, 3);