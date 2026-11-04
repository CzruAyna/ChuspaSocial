# Checklist de seguridad previa a despliegue

Lista de verificación manual de los controles de seguridad del plugin
`local_chuspasocial` que deben comprobarse **antes de publicar cada release**.
Cada control se marca con `[ ]` cuando está verificado.

Esta lista parte de las revisiones estáticas ya guardadas en `security/`, que
son el punto de partida de cada verificación:

| Informe | Control que cubre |
| --- | --- |
| `security/revisiones/login.md` | Sesión iniciada y acceso a las páginas del plugin |
| `security/revisiones/sesskey.md` | Protección CSRF en las acciones que modifican datos |
| `security/revisiones/sql.md` | Inyecciones SQL en las consultas |
| `security/revisiones/escaping.md` | Escape de salida y XSS en las plantillas |
| `security/modelo-amenazas.md` | Amenazas y mitigaciones (T-01 a T-05) |

**Cómo usar:** antes de cada release recorre las cinco secciones y marca cada
casilla `[x]` cuando el control esté verificado en el código o en la
instalación. Si un control todavía no aplica, déjalo en `[ ]` y anótalo en el
apartado *Observaciones* del final.

- Versión del plugin: `____________`
- Rama revisada: `____________`
- Responsable: `____________`
- Fecha: `____________`

## 1. Control de acceso y capacidades

| | ID | Control a verificar | Criterio de validación | Archivo de referencia |
| --- | --- | --- | --- | --- |
| [ ] | SEG-01 | Toda página del plugin exige sesión iniciada antes de emitir salida | `require_login()` se ejecuta antes de `$OUTPUT->header()` | `src/index.php` |
| [ ] | SEG-02 | Los servicios web validan su contexto antes de operar | `validate_context($context)` se invoca sobre el contexto correcto | `src/classes/external/get_listings.php` |
| [ ] | SEG-03 | Los servicios web comprueban la capacidad requerida | `require_capability('local/chuspasocial:view', $context)` | `src/classes/external/get_listings.php` |
| [ ] | SEG-04 | Las capacidades del plugin están declaradas con contexto coherente | Cada capacidad de `src/db/access.php` está definida y no se concede a roles indebidos | `src/db/access.php` |
| [ ] | SEG-05 | Las consultas filtran por contexto y matrícula, no solo por el identificador recibido | Toda consulta incluye el filtro de curso o de pertenencia del usuario | `src/classes/local/listing_service.php` |
| [ ] | SEG-06 | Cada servicio web declarado tiene su clase implementada | La clase `classname` de cada entrada de `src/db/services.php` existe en `src/classes/external/` | `src/db/services.php` |
| [ ] | SEG-07 | Las páginas por materia validan la matrícula del usuario | `require_login($courseid)` y comprobación de matrícula en cada página por curso | Pendiente: `src/course.php` aún no existe |

## 2. Protección CSRF (sesskey)

| | ID | Control a verificar | Criterio de validación | Archivo de referencia |
| --- | --- | --- | --- | --- |
| [ ] | SEG-08 | Los formularios que modifican datos heredan la verificación de sesión de Moodle | Las clases extienden `moodleform`, que inserta la `sesskey` y llama a `confirm_sesskey()` al enviar | `src/classes/form/post_form.php`, `src/classes/form/listing_form.php` |
| [ ] | SEG-09 | Los servicios web de escritura están declarados con `type => 'write'` y `ajax => true` | Moodle exige la `sesskey` antes de ejecutarlos | `src/db/services.php` |
| [ ] | SEG-10 | Las acciones que borran o modifican datos exigen confirmación de sesión | Ninguna acción destructiva se ejecuta desde una petición sin `sesskey` | `src/classes/local/post_service.php` |
| [ ] | SEG-11 | Las clases de servicio no son un punto de entrada sin control | Cada servicio se invoca desde una página o un servicio web con verificación, nunca directamente | `src/classes/local/post_service.php` |

## 3. Parámetros y tipos

| | ID | Control a verificar | Criterio de validación | Archivo de referencia |
| --- | --- | --- | --- | --- |
| [ ] | SEG-12 | Los servicios web declaran el tipo de cada parámetro | `execute_parameters()` declara cada entrada con su constante `PARAM_*` | `src/classes/external/get_listings.php` |
| [ ] | SEG-13 | Los parámetros recibidos se validan antes de usarse | `validate_parameters()` se invoca al inicio del método `execute()` | `src/classes/external/get_listings.php` |
| [ ] | SEG-14 | Los rangos numéricos se comprueban en el servidor | `page` no es negativo y `perpage` está entre 1 y 50; si no, se lanza `invalid_parameter_exception` | `src/classes/external/get_listings.php` |
| [ ] | SEG-15 | Los formularios fijan el tipo de cada campo | Cada `addElement()` tiene su `setType()` con `PARAM_RAW`, `PARAM_INT` o `PARAM_BOOL` | `src/classes/form/post_form.php`, `src/classes/form/listing_form.php` |
| [ ] | SEG-16 | Los campos obligatorios de los formularios están validados | Los campos críticos usan `addRule(..., 'required', ...)` | `src/classes/form/listing_form.php` |
| [ ] | SEG-17 | Los parámetros de URL se leen con `required_param()` u `optional_param()` | Ninguna página lee `$_GET` o `$_POST` directamente | `src/index.php` |

## 4. Inyecciones SQL

| | ID | Control a verificar | Criterio de validación | Archivo de referencia |
| --- | --- | --- | --- | --- |
| [ ] | SEG-18 | Ningún dato variable se concatena dentro de la sentencia SQL | Todas las consultas usan placeholders o condiciones en array | `src/classes/local/listing_service.php`, `src/classes/local/post_service.php` |
| [ ] | SEG-19 | Las consultas con `LIKE` escapan los comodines | Se usan `sql_like()` y `sql_like_escape()` | `src/classes/local/listing_service.php` |
| [ ] | SEG-20 | Las listas de valores usan `get_in_or_equal()` | Ninguna lista de identificadores se concatena a mano | `src/classes/local/listing_service.php` |
| [ ] | SEG-21 | Los identificadores de las tablas del núcleo son correctos | `{tag}`, `{tag_instance}` y `{course}` son tablas de Moodle, no del plugin | `src/classes/local/listing_service.php` |
| [ ] | SEG-22 | Los borrados filtran por la clave correcta | Cada `delete_records()` filtra por la columna que corresponde (`postid` o `id`) | `src/classes/local/post_service.php` |
| [ ] | SEG-23 | La lectura de una publicación exige que exista | `get_record()` usa `MUST_EXIST` y lanza `dml_missing_record_exception` | `src/classes/local/post_service.php` |

## 5. Renderizado seguro y XSS

| | ID | Control a verificar | Criterio de validación | Archivo de referencia |
| --- | --- | --- | --- | --- |
| [ ] | SEG-24 | Las plantillas Mustache escapan la salida por defecto | Solo se usan etiquetas dobles `{{ }}` | `src/templates/listing.mustache`, `src/templates/wall.mustache` |
| [ ] | SEG-25 | Las etiquetas sin escape reciben contenido ya saneado | `{{{content}}}` solo se usa si el valor pasó por `format_text()` | `src/templates/post.mustache`, `src/templates/comments.mustache` |
| [ ] | SEG-26 | El exportador entrega el contenido formateado | `content` se devuelve con `format_text()` y se acompaña de `contentformat` | `src/classes/external/post_exporter.php` |
| [ ] | SEG-27 | Los textos con datos del usuario se escapan en PHP | Se usa `s()`, `format_string()` o `format_text()` en lugar de concatenar | `src/classes/local/`, `src/classes/external/` |
| [ ] | SEG-28 | El JavaScript no inserta HTML sin sanear | No se usa `.html()` ni `innerHTML` con datos del usuario | `src/amd/src/wall.js`, `src/amd/src/follow.js` |

## Observaciones

Anota aquí los controles pendientes, los riesgos aceptados y cualquier desviación
encontrada durante la revisión.

| Fecha | Ítem | Observación | Responsable |
| --- | --- | --- | --- |
| | | | |

## Resultado de la revisión

- [ ] Todos los ítems de seguridad están verificados
- [ ] No hay controles críticos sin verificar
- [ ] Las observaciones están documentadas y asignadas

Revisado por: `____________`  ·  Fecha: `____________`
