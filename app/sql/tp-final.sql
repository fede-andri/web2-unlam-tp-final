CREATE DATABASE preguntones_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE preguntones_db;

CREATE TABLE usuarios(
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         nombre VARCHAR(100) NOT NULL,
                         usuario VARCHAR(150) NOT NULL UNIQUE,
                         email VARCHAR(150) NOT NULL UNIQUE,
                         password VARCHAR(255) NOT NULL,
                         fecha_nacimiento DATE NOT NULL,
                         sexo VARCHAR(30) NOT NULL,
                         pais VARCHAR(100) NOT NULL,
                         ciudad VARCHAR(100) NOT NULL,
                         foto VARCHAR(255) DEFAULT 'default.png',
                         rol VARCHAR(20) NOT NULL DEFAULT 'JUGADOR',
                         validado BOOLEAN NOT NULL DEFAULT FALSE,
                         fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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