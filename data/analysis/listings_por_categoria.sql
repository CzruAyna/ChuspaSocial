-- =========================================================
-- Consulta para medir métricas de éxito: ofertas del marketplace por
-- categoría y estado
-- Para cada categoría (category) y estado (status), cuenta el total de
-- anuncios (total_anuncios) y calcula el precio promedio
-- (precio_promedio) y el precio máximo (precio_maximo), para evaluar la
-- dinámica comercial y el inventario del marketplace universitario.
-- Utiliza la sintaxis estándar de Moodle {tabla} para los prefijos.
--
-- Tabla: local_chuspasocial_listing (campos category, status, price).
-- Estados: available (activo), sold (vendido) y retired (retirado).
--
-- No expone datos personales: solo devuelve agregados por categoría y
-- estado, sin userid ni externalcontact.
-- =========================================================

SELECT
    l.category,
    l.status,
    COUNT(*) AS total_anuncios,
    ROUND(AVG(l.price), 2) AS precio_promedio,
    MAX(l.price) AS precio_maximo
FROM
    {local_chuspasocial_listing} l
GROUP BY
    l.category,
    l.status
ORDER BY
    l.category ASC,
    total_anuncios DESC;

/*
=========================================
EJEMPLO DE RESULTADO (Criterio de aceptación)
=========================================
category   | status    | total_anuncios | precio_promedio | precio_maximo
-------------------------------------------------------------------------
Ingenieria | available | 24             | 85.50           | 220.00
Ingenieria | sold      | 11             | 70.25           | 150.00
Literatura | available | 18             | 40.00           | 95.00
Literatura | retired   | 3              | 35.00           | 50.00
(Los valores son de ejemplo, no datos reales)
=========================================
*/
