-- =========================================================
-- Consulta para medir la evolución mensual de la actividad del plugin
-- Para cada mes (año y mes de timecreated) devuelve el total de
-- publicaciones (total_publicaciones), comentarios (total_comentarios)
-- y reacciones (total_reacciones) creados, ordenado cronológicamente.
-- Solo devuelve agregados por mes: no expone userid, nombres, correos,
-- direcciones IP ni contenido de publicaciones o comentarios.
--
-- Tablas (con prefijo de Moodle entre llaves):
--   {local_chuspasocial_post}, {local_chuspasocial_comment},
--   {local_chuspasocial_reaction}.
-- Campo usado en todas: timecreated (marca de tiempo Unix, en segundos).
--
-- Compatibilidad:
--   * No usa funciones de fecha propias de un motor (TO_TIMESTAMP,
--     FROM_UNIXTIME, EXTRACT...). El año y el mes se calculan solo con
--     aritmética entera (FLOOR y operadores + - * /), que existe en los
--     motores que soporta Moodle.
--   * El cálculo convierte los segundos Unix en días desde 1970-01-01 y
--     luego en año y mes del calendario gregoriano (algoritmo "civil from
--     days"). Se hace por capas (subconsultas) para que sea legible.
--   * El mes resultante está en UTC, no en la zona horaria del usuario.
--
-- Notas:
--   * Se cuentan todas las publicaciones, incluidas las ocultas (hidden).
--   * Los meses sin actividad no aparecen en el resultado.
-- =========================================================

SELECT
    anio,
    mes,
    SUM(es_publicacion) AS total_publicaciones,
    SUM(es_comentario) AS total_comentarios,
    SUM(es_reaccion) AS total_reacciones
FROM (
    -- Capa 4: mes (1 a 12) y año a partir de mp (0 = marzo ... 11 = febrero).
    SELECT
        y0 + CASE WHEN mp >= 10 THEN 1 ELSE 0 END AS anio,
        CASE WHEN mp < 10 THEN mp + 3 ELSE mp - 9 END AS mes,
        es_publicacion,
        es_comentario,
        es_reaccion
    FROM (
        -- Capa 3: día del año (desde marzo) y mes mp; año base y0.
        SELECT
            yoe + era * 400 AS y0,
            FLOOR((5 * (doe - (365 * yoe + FLOOR(yoe / 4) - FLOOR(yoe / 100))) + 2) / 153) AS mp,
            es_publicacion,
            es_comentario,
            es_reaccion
        FROM (
            -- Capa 2: año dentro de la era (yoe) de 400 años.
            SELECT
                era,
                doe,
                FLOOR((doe - FLOOR(doe / 1460) + FLOOR(doe / 36524) - FLOOR(doe / 146096)) / 365) AS yoe,
                es_publicacion,
                es_comentario,
                es_reaccion
            FROM (
                -- Capa 1: era (400 años) y día dentro de la era (doe).
                SELECT
                    FLOOR(z / 146097) AS era,
                    z - 146097 * FLOOR(z / 146097) AS doe,
                    es_publicacion,
                    es_comentario,
                    es_reaccion
                FROM (
                    -- Capa 0: días desde 0000-03-01 (días Unix + 719468).
                    SELECT
                        FLOOR(timecreated / 86400) + 719468 AS z,
                        1 AS es_publicacion,
                        0 AS es_comentario,
                        0 AS es_reaccion
                    FROM {local_chuspasocial_post}

                    UNION ALL

                    SELECT
                        FLOOR(timecreated / 86400) + 719468 AS z,
                        0 AS es_publicacion,
                        1 AS es_comentario,
                        0 AS es_reaccion
                    FROM {local_chuspasocial_comment}

                    UNION ALL

                    SELECT
                        FLOOR(timecreated / 86400) + 719468 AS z,
                        0 AS es_publicacion,
                        0 AS es_comentario,
                        1 AS es_reaccion
                    FROM {local_chuspasocial_reaction}
                ) AS dias
            ) AS era_dia
        ) AS anio_era
    ) AS mes_calendario
) AS actividad
GROUP BY
    anio,
    mes
ORDER BY
    anio ASC,
    mes ASC;
