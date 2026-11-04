# Revisión de control de acceso a los archivos

**Fecha:** 2026-10-22
**Rama:** security/audit-files
**Alcance:** las áreas de archivos (pluginfile) declaradas por el plugin (`src/`).
**Método:** se buscaron todas las referencias a `filearea` y a funciones
`_pluginfile` en `src/` (fuera de `tests/`), y para cada área encontrada se
comprobó que verifique login y visibilidad antes de entregar el archivo:

    grep -rn --include=*.php -i "filearea\|pluginfile\|file_area" src/ (fuera de tests/)  →  1 función pluginfile, 1 área ('postimage')

## Resumen

- Funciones `_pluginfile` declaradas: 1 (`local_chuspasocial_pluginfile` en `src/lib.php`).
- Áreas de archivos servidas: 1 (`postimage`).
- Áreas con verificación de login: 1.
- Áreas con verificación de visibilidad: 1.
- Problemas encontrados: ninguno.

## Áreas de pluginfile revisadas

| Archivo | Línea | Área | Estado |
|---|---|---|---|
| src/lib.php | 68-105 | postimage | OK: comprueba el nivel de contexto (solo sistema o curso, línea 79), rechaza cualquier `$filearea` distinto de `postimage` (línea 83), llama a `require_login($course, false, $cm)` (línea 87) y, antes de servir el archivo, busca el `post` dueño de la imagen y llama a `post_service::can_view()` (línea 100). `can_view()` (`src/classes/local/post_service.php`, líneas 225-253) exige inscripción en el curso (o la capacidad `moodle/course:view`) para publicaciones de materia, la capacidad `local/chuspasocial:view` para el muro general, y, si la publicación está oculta, que el usuario sea el autor o tenga `local/chuspasocial:moderate`. Si cualquier comprobación falla, la función devuelve `false` sin llamar a `send_stored_file()`. |

Esta es la única área de archivos que declara el plugin: no hay otra llamada
a `get_file_storage()->get_file()` ni otra función `_pluginfile` en `src/`.

## Observación (fuera del alcance de este informe)

`src/tests/lib_test.php::test_pluginfile_bloquea_usuario_no_inscrito()` es un
marcador de posición: no llega a invocar `local_chuspasocial_pluginfile()`
(la línea está comentada) y la aserción real es `assertTrue(true)`, así que
hoy no ejercita la verificación revisada arriba. No se modifica porque el
archivo no está en el alcance de este issue.

## Conclusión

No se encontró ninguna acción que sirva un archivo sin verificación de login
o de visibilidad: la única área de archivos del plugin (`postimage`) pasa por
`require_login()` y por `post_service::can_view()` antes de entregar el
archivo. Por eso no hace falta abrir ningún issue de seguridad nuevo.
