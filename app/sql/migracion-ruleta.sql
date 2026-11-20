-- migracion-ruleta.sql

ALTER TABLE categorias
    ADD imagen VARCHAR(255) NULL;

ALTER TABLE partidas
    ADD categoria_id INT NULL,
ADD CONSTRAINT fk_partidas_categoria
FOREIGN KEY (categoria_id)
REFERENCES categorias(id);