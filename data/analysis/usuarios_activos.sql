-- =========================================================
-- Consulta para medir métricas de éxito: usuarios más activos
-- Se utiliza estrictamente el 'userid' para no exponer datos personales.
-- =========================================================

-- =========================================================
-- INTERVALO DE TIEMPO
-- La consulta NO filtra por fecha: no hay ventana temporal. Cuenta todas
-- las publicaciones y todos los comentarios registrados desde el inicio,
-- por lo que el resultado es un acumulado histórico por usuario.
--
-- TABLAS
-- tabla_publicaciones corresponde a local_chuspasocial_post y
-- tabla_comentarios corresponde a local_chuspasocial_comment (con el
-- prefijo de tablas de Moodle).
--
-- ÍNDICES TEMPORALES
--   local_chuspasocial_post: columnas de tiempo: timecreated, timemodified. Índices y claves foráneas: ninguno declarado.
--   local_chuspasocial_comment: columnas de tiempo: timecreated, timemodified. Índices y claves foráneas: ninguno declarado.
-- Ninguna de las dos tablas declara un índice sobre su columna de tiempo. Con la consulta actual no se usa ningún índice temporal, porque no hay filtro por fecha.
-- Sin filtro, la consulta recorre ambas tablas completas; un índice sobre
-- userid solo ayudaría a agrupar, no a reducir las filas leídas.
--
-- Si en el futuro se agrega una ventana de tiempo, se filtraría por timecreated (publicaciones)
-- y timecreated (comentarios), y convendría un índice sobre esas columnas para que
-- el filtro sea eficiente.
-- =========================================================

SELECT 
    userid,
    SUM(es_publicacion) AS total_publicaciones,
    SUM(es_comentario) AS total_comentarios,
    (SUM(es_publicacion) + SUM(es_comentario)) AS interacciones_totales
FROM (
    SELECT userid, 1 AS es_publicacion, 0 AS es_comentario 
    FROM tabla_publicaciones
    
    UNION ALL
    
    SELECT userid, 0 AS es_publicacion, 1 AS es_comentario 
    FROM tabla_comentarios
) AS actividad
GROUP BY 
    userid
ORDER BY 
    interacciones_totales DESC;

/*
=========================================
EJEMPLO DE RESULTADO (Criterio de aceptación)
=========================================
userid  | total_publicaciones | total_comentarios | interacciones_totales
-------------------------------------------------------------------------
14      | 12                  | 34                | 46
5       | 8                   | 15                | 23
89      | 1                   | 5                 | 6
=========================================
*/