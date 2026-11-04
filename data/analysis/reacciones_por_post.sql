-- =========================================================
-- Consulta para medir métricas de interacción: reacciones por publicación
-- Para cada publicación (postid) y tipo de reacción devuelve el total de
-- reacciones recibidas y la fecha de la última reacción.
-- Solo devuelve agregados por publicación: no expone userid, nombres,
-- correos ni otros datos personales.
--
-- Tabla: local_chuspasocial_reaction (en Moodle lleva el prefijo
-- configurado, por ejemplo mdl_local_chuspasocial_reaction).
-- Campos usados: postid, type, timecreated.
-- ultima_reaccion es una marca de tiempo Unix (timecreated), para que la
-- consulta sea compatible con PostgreSQL y MariaDB/MySQL.
-- =========================================================

SELECT
    postid,
    type AS reaction,
    COUNT(*) AS total_reacciones,
    MAX(timecreated) AS ultima_reaccion
FROM local_chuspasocial_reaction
GROUP BY
    postid,
    type
ORDER BY
    total_reacciones DESC;
