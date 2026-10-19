CREATE DATABASE preguntones_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE preguntones_db;

CREATE TABLE usuarios(
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       usuario VARCHAR(150) NOT NULL UNIQUE,
                       email VARCHAR(150) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL
);

INSERT INTO usuarios (usuario, password) VALUES ('juan', '$2y$10$Dopr.hNwiUeOEXdNWrJZU.a7fwFrtEUcepSE4mUPmXuAWfMu8JrBG'); //123456