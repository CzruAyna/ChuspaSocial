-- =========================================================
-- Consulta: tags mas usados por mes en ChuspaSocial
-- Issue #20
--
-- Relaciona las etiquetas de Moodle con las publicaciones
-- de ChuspaSocial mediante tag_instance.
--
-- Supuestos:
--   tag_instance.itemtype = 'local_chuspasocial_post'
--   tag_instance.component = 'local_chuspasocial'
--   local_chuspasocial_post.id identifica cada publicacion
--   local_chuspasocial_post.timecreated almacena su fecha
--
-- Portabilidad (PostgreSQL, MySQL y MariaDB):
--   No se usan funciones de fecha propias de un motor. El mes se
--   calcula a partir del timestamp Unix como un indice de mes:
--     mes_indice = FLOOR(p.timecreated / 2629800)
--   donde 2629800 es la duracion promedio de un mes en segundos
--   (365.25 dias / 12). El indice cuenta periodos desde 1970.
--
--   Como obtener anio y mes a partir del indice (en la aplicacion):
--     inicio_periodo = mes_indice * 2629800   (timestamp Unix, UTC)
--   y formatear ese timestamp como fecha. Cada indice es un periodo de
--   2629800 s (30,4375 dias), no un mes de calendario, por lo que el
--   resultado es aproximado. Para asignar el periodo a un mes de
--   calendario, usar su punto medio:
--     fecha_referencia = (mes_indice + 0.5) * 2629800
--   Ejemplo: mes_indice 678 comienza el 2 de julio de 2026 (UTC) y
--   llega hasta el 2 de agosto; su punto medio cae el 17 de julio.
--
-- Tablas del nucleo de Moodle utilizadas:
--   tag_instance: asocia una etiqueta con el elemento etiquetado.
--     tagid     -> tag.id de la etiqueta.
--     itemid    -> id del elemento etiquetado (aqui, local_chuspasocial_post.id).
--     component -> componente que creo la asociacion ('local_chuspasocial').
--     itemtype  -> tipo de elemento etiquetado ('local_chuspasocial_post').
--   tag: catalogo de etiquetas del sitio.
--     id   -> identificador de la etiqueta (tagid de tag_instance).
--     name -> nombre normalizado de la etiqueta, usado para agrupar y mostrar.
--
-- Solo devuelve datos agregados; no expone datos personales.
--
-- LIMIT configurable:
--   La consulta no trae LIMIT por defecto: devuelve todas las etiquetas
--   con uso en el periodo. Para quedarse solo con las mas usadas de cada
--   mes, agregar un LIMIT total (no es LIMIT por grupo) despues del
--   ORDER BY, por ejemplo:
--     LIMIT 10   -- panel o reporte breve (top 10 general).
--     LIMIT 50   -- analisis mas detallado por mes.
--   Sin LIMIT, para limitar el total de filas desde la aplicacion, usar
--   paginacion con OFFSET.
-- =========================================================

SELECT
    FLOOR(p.timecreated / 2629800) AS mes_indice,
    t.id AS tagid,
    t.name AS tag,
    COUNT(*) AS total_usos
FROM tag_instance ti
INNER JOIN tag t
    ON t.id = ti.tagid
INNER JOIN local_chuspasocial_post p
    ON p.id = ti.itemid
WHERE
    ti.component = 'local_chuspasocial'
    AND ti.itemtype = 'local_chuspasocial_post'
GROUP BY
    FLOOR(p.timecreated / 2629800),
    t.id,
    t.name
ORDER BY
    FLOOR(p.timecreated / 2629800),
    total_usos DESC
-- LIMIT 10   -- descomentar y ajustar segun el uso (ver LIMIT configurable arriba).
;

/*
=========================================
EJEMPLO DE RESULTADO (datos ficticios)
=========================================
mes_indice | tagid | tag        | total_usos
-----------------------------------------------
678        | 3     | literatura | 18
678        | 5     | novela     | 12
679        | 3     | literatura | 24
679        | 8     | ciencia    | 15
680        | 5     | novela     | 20
(678 ~ julio 2026, 679 ~ agosto 2026, 680 ~ septiembre 2026; ver conversion arriba)
=========================================
*/
