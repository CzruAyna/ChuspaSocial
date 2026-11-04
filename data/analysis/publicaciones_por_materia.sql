-- =========================================================
-- Consulta para medir métricas de éxito: publicaciones por materia y semana
-- Para cada materia (courseid) y semana, cuenta el total de
-- publicaciones (total_publicaciones), ordenadas de mayor a menor
-- actividad, para medir la adopción y la actividad académica en la
-- plataforma.
-- Utiliza la sintaxis estándar de Moodle {tabla} para los prefijos.
--
-- Tablas: local_chuspasocial_post (campos courseid, timecreated) y
-- course, del núcleo de Moodle (campos id, shortname).
--
-- Portabilidad (PostgreSQL, MySQL y MariaDB):
--   No se usan funciones de fecha propias de un motor. La semana se
--   calcula con aritmética sobre el timestamp Unix:
--     semana_unix = FLOOR(p.timecreated / 604800)
--   donde 604800 es la duración de una semana en segundos (7 dias).
--   semana_unix cuenta semanas completas desde el 1 de enero de 1970
--   (UTC); como esa fecha fue jueves, cada semana empieza en jueves.
--
--   Como obtener la fecha a partir de semana_unix (en la aplicacion):
--     inicio_semana = semana_unix * 604800   (timestamp Unix, UTC)
--   y luego formatear ese timestamp como fecha, o calcular
--   anio = FLOOR(inicio_semana / 31557600) + 1970 (aproximado).
--   Ejemplo: semana_unix 2962 -> semana que inicia el 8 de octubre de 2026.
--
-- No expone datos personales: solo identificadores y nombres de curso
-- (courseid, shortname) y agregados por materia y semana.
-- =========================================================

SELECT
    c.id AS courseid,
    c.shortname AS materia,
    FLOOR(p.timecreated / 604800) AS semana_unix,
    COUNT(p.id) AS total_publicaciones
FROM
    {local_chuspasocial_post} p
JOIN
    {course} c ON c.id = p.courseid
GROUP BY
    c.id,
    c.shortname,
    FLOOR(p.timecreated / 604800)
ORDER BY
    total_publicaciones DESC,
    FLOOR(p.timecreated / 604800) DESC;

/*
=========================================
EJEMPLO DE RESULTADO (Criterio de aceptación)
=========================================
courseid | materia | semana_unix | total_publicaciones
-------------------------------------------------------
2        | INF-111 | 2962        | 45
2        | INF-111 | 2961        | 39
5        | MAT-115 | 2962        | 28
8        | LIN-111 | 2961        | 12
(2962 = semana del 8 de oct 2026, 2961 = semana del 1 de oct 2026)
=========
*/
