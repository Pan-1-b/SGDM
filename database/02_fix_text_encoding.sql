-- Repara texto UTF-8 que fue guardado tras una doble interpretación de charset
-- (por ejemplo, "FÃºtbol" -> "Fútbol").
-- Es seguro volver a ejecutarlo: solo modifica valores que contienen el patrón
-- de bytes C3 83 C2, característico de ese problema.

START TRANSACTION;

UPDATE categoria
SET nombre = CONVERT(BINARY(CONVERT(nombre USING latin1)) USING utf8mb4)
WHERE LOCATE('C383C2', HEX(nombre)) > 0;

UPDATE categoria
SET descripcion = CONVERT(BINARY(CONVERT(descripcion USING latin1)) USING utf8mb4)
WHERE LOCATE('C383C2', HEX(descripcion)) > 0;

UPDATE torneo
SET nombretorneo = CONVERT(BINARY(CONVERT(nombretorneo USING latin1)) USING utf8mb4)
WHERE LOCATE('C383C2', HEX(nombretorneo)) > 0;

UPDATE torneo
SET descripcion = CONVERT(BINARY(CONVERT(descripcion USING latin1)) USING utf8mb4)
WHERE LOCATE('C383C2', HEX(descripcion)) > 0;

UPDATE usuario
SET nombre = CONVERT(BINARY(CONVERT(nombre USING latin1)) USING utf8mb4)
WHERE LOCATE('C383C2', HEX(nombre)) > 0;

COMMIT;
