# Análisis de métricas

Esta carpeta reúne las consultas SQL que miden las métricas de éxito de ChuspaSocial.
Todas devuelven solo agregados, `userid` o nombres de catálogo (etiquetas y materias):
ninguna expone nombres de personas, correos ni contenido de publicaciones.

## Métricas analizadas

| Métrica | Consulta | Qué mide | Tablas de origen (en la consulta) | Tablas del plugin (docs/modelo-datos.md) |
| --- | --- | --- | --- | --- |
| Usuarios más activos | `usuarios_activos.sql` | Publicaciones, comentarios e interacciones totales por usuario, de mayor a menor. | `tabla_publicaciones`, `tabla_comentarios` (campo `userid`) | `local_chuspasocial_post`, `local_chuspasocial_comment` |
| Tasa de libros vendidos | `tasa_vendidos.sql` | Por mes: libros publicados, libros vendidos y la tasa vendidos / publicados. | `tabla_libros` (campos `fecha_publicacion`, `fecha_venta`) | `local_chuspasocial_listing` |
| Tags más usados | `tags_mas_usados.sql` | Etiquetas más usadas en las publicaciones, por mes y con su total de usos. | `tag_instance`, `tag`, `local_chuspasocial_post` (campo `timecreated`) | `local_chuspasocial_post` |
| Publicaciones por materia | `publicaciones_por_materia.sql` | Publicaciones por materia y semana, para medir adopción y actividad académica. | `local_chuspasocial_post` y `course` (campos `courseid` y `timecreated`) | `local_chuspasocial_post` |
| Reacciones por publicación | `reacciones_por_post.sql` | Volumen y distribución de reacciones por publicación: una fila por publicación y tipo de reacción, con el total recibido y la fecha de la última reacción. | `local_chuspasocial_reaction` (campos `postid`, `type` y `timecreated`) | `local_chuspasocial_reaction` |
| Comentarios por hora del día | `comentarios_por_hora.sql` | Distribución horaria de los comentarios: cuántos se publicaron en cada hora del día (de 0 a 23), para detectar los picos de actividad. | `local_chuspasocial_comment` (campo `timecreated`) | `local_chuspasocial_comment` |

`usuarios_activos.sql` y `tasa_vendidos.sql` usan nombres genéricos de tabla y columna;
antes de correrlas hay que ajustarlos al esquema real descrito en `docs/modelo-datos.md`.
`tags_mas_usados.sql` y `publicaciones_por_materia.sql` ya usan los nombres reales; las
tablas `tag`, `tag_instance` y `course` son del núcleo de Moodle, no del plugin.
`reacciones_por_post.sql` y `comentarios_por_hora.sql` también usan los nombres reales de
las tablas del plugin: `local_chuspasocial_reaction` y `local_chuspasocial_comment`.

## Ejemplo de resultado

Usuarios más activos (`usuarios_activos.sql`):

| userid | total_publicaciones | total_comentarios | interacciones_totales |
| --- | --- | --- | --- |
| 14 | 12 | 34 | 46 |
| 5 | 8 | 15 | 23 |
| 89 | 1 | 5 | 6 |

Tasa de libros vendidos (`tasa_vendidos.sql`):

| anio | mes | libros_publicados | libros_vendidos | tasa_vendidos |
| --- | --- | --- | --- | --- |
| 2026 | 7 | 40 | 12 | 0.30 |
| 2026 | 8 | 55 | 31 | 0.56 |
| 2026 | 9 | 48 | 29 | 0.60 |

Tags más usados (`tags_mas_usados.sql`):

| anio | mes | tagid | tag | total_usos |
| --- | --- | --- | --- | --- |
| 2026 | 7 | 3 | literatura | 18 |
| 2026 | 7 | 5 | novela | 12 |
| 2026 | 8 | 3 | literatura | 24 |
| 2026 | 8 | 8 | ciencia | 15 |
| 2026 | 9 | 5 | novela | 20 |

Publicaciones por materia (`publicaciones_por_materia.sql`):

| courseid | materia | anio | semana | total_publicaciones |
| --- | --- | --- | --- | --- |
| 2 | INF-111 | 2026 | 41 | 45 |
| 5 | MAT-115 | 2026 | 41 | 28 |
| 2 | INF-111 | 2026 | 40 | 39 |
| 8 | LIN-111 | 2026 | 40 | 12 |

Reacciones por publicación (`reacciones_por_post.sql`):

| postid | reaction | total_reacciones | ultima_reaccion |
| --- | --- | --- | --- |
| 42 | like | 18 | 2026-10-18 |
| 42 | wow | 5 | 2026-10-19 |
| 17 | like | 9 | 2026-10-17 |

Comentarios por hora del día (`comentarios_por_hora.sql`):

| hora | total_comentarios |
| --- | --- |
| 0 | 2 |
| 8 | 14 |
| 12 | 31 |
| 18 | 22 |

Los valores son de ejemplo, no datos reales.

## Datos personales

- Los usuarios se identifican solo por `userid`; no se cruzan con la tabla `user` de Moodle.
- La tasa de libros vendidos solo devuelve totales por mes.
- Los tags más usados solo devuelven el nombre de la etiqueta y los totales por mes.
- Las publicaciones por materia solo devuelven el curso (`courseid` y `shortname`) y totales por semana.
- Las reacciones por publicación solo devuelven el identificador de la publicación, el tipo de reacción y totales; no identifican a quién reaccionó.
- Los comentarios por hora solo devuelven la hora del día y el total de comentarios; no se cruzan con el `userid`.
