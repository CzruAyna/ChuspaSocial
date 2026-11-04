-- =========================================================
-- Consulta para medir métricas de actividad: comentarios por hora del día
-- Cuenta el total de comentarios (total_comentarios) por cada hora del día
-- (0 a 23), ordenados cronológicamente por hora, para identificar los
-- picos de interacción dentro de ChuspaSocial.
-- Solo devuelve agregados por hora: no expone userid, contenido de los
-- comentarios ni otros datos personales.
--
-- Tabla: local_chuspasocial_comment (en Moodle lleva el prefijo
-- configurado, por ejemplo mdl_local_chuspasocial_comment).
-- Campo usado: timecreated (marca de tiempo Unix, en segundos).
--
-- Notas:
--   * La hora se calcula con aritmética (MOD y FLOOR) para que la consulta
--     no dependa de funciones de fecha propias de cada motor de base de datos.
--   * La hora resultante está en UTC, no en la zona horaria del usuario.
--   * Las horas sin comentarios no aparecen en el resultado.
-- =========================================================

SELECT
    FLOOR(MOD(timecreated, 86400) / 3600) AS hora,
    COUNT(*) AS total_comentarios
FROM local_chuspasocial_comment
GROUP BY
    FLOOR(MOD(timecreated, 86400) / 3600)
ORDER BY
    hora ASC;
