# Revisión de funciones externas: validate_context y capacidades en classes/external/

**Fecha:** 2026-10-21
**Rama:** security/audit-external
**Alcance:** todas las clases de `src/classes/external/`.
**Método:** en cada clase se comprobó que la función externa valide sus parámetros
con `validate_parameters()`, llame a `self::validate_context()` con el contexto
correcto antes de leer o escribir datos, y exija una capacidad con
`require_capability()` en ese mismo contexto. También se revisó que la capacidad
exigida esté definida en `src/db/access.php` y que el servicio que se invoca
exista. Las búsquedas se hicieron sobre `src/classes/external/`:

    grep -rn "validate_parameters" src/classes/external/  →  4 coincidencias
    grep -rn "validate_context" src/classes/external/  →  4 coincidencias
    grep -rn -E "require_capability|has_capability" src/classes/external/  →  5 coincidencias

`self::validate_context()` llama a `require_login($course, false, $cm, false, true)`
(external_api.php de Moodle, línea 521). En un contexto de curso eso exige que el
usuario tenga acceso al curso; en el contexto de sistema exige sesión iniciada.

## Resumen

- Archivos revisados: 5.
- Funciones externas (`extends external_api`): 4.
- Exporters (`extends exporter`): 1.
- Funciones con `validate_parameters()`, `validate_context()` y `require_capability()`: 4 de 4.
- Problemas de seguridad encontrados: 3 (se reportan como issues `security:` nuevos, uno por archivo).
- Problemas de funcionamiento encontrados: 3 (no son de seguridad; ver la última sección).

## Funciones externas revisadas

| Archivo | Línea | Verificación | Estado |
|---|---|---|---|
| src/classes/external/get_listings.php | 78 | `validate_parameters()` | OK: valida `search` (PARAM_TEXT), `page` y `perpage` (PARAM_INT). |
| src/classes/external/get_listings.php | 84-86 | `validate_context()` + `require_capability()` | OK: contexto de sistema y `local/chuspasocial:view` (definida en access.php). |
| src/classes/external/get_listings.php | 88-93 | Límites de paginación | OK: rechaza `page < 0` y `perpage` fuera de 1..50 con `invalid_parameter_exception`. |
| src/classes/external/add_comment.php | 66 | `validate_parameters()` | OK: valida `postid` (PARAM_INT) y `content` (PARAM_RAW_TRIMMED). |
| src/classes/external/add_comment.php | 71-85 | `validate_context()` + `require_capability()` | OK: toma el contexto del curso de la publicación (o el de sistema si es del muro general) y exige `local/chuspasocial:comment` (definida en access.php). |
| src/classes/external/add_comment.php | 71-76 | Visibilidad de la publicación | **Problema 1:** lee la publicación sin comprobar el campo `hidden`; se puede comentar una publicación ocultada por un moderador si se conoce su id. |
| src/classes/external/toggle_reaction.php | 64 | `validate_parameters()` | OK: valida `postid` (PARAM_INT). |
| src/classes/external/toggle_reaction.php | 68-82 | `validate_context()` + `require_capability()` | OK: mismo contexto que `add_comment` y exige `local/chuspasocial:react`. |
| src/classes/external/toggle_reaction.php | 68-73 | Visibilidad de la publicación | **Problema 2:** igual que en `add_comment`, no comprueba `hidden`; se puede reaccionar a una publicación oculta. |
| src/classes/external/report_item.php | 65 | `validate_parameters()` | OK: valida `itemtype` (PARAM_ALPHA), `itemid` (PARAM_INT) y `reason` (PARAM_RAW_TRIMMED). |
| src/classes/external/report_item.php | 71-73 | `validate_context()` + `require_capability()` | OK: contexto de sistema y `local/chuspasocial:report` (definida en access.php). |
| src/classes/external/report_item.php | 75-79 | Validación del elemento reportado | **Problema 3:** no comprueba que `itemtype` sea `post`, `comment` o `listing`, ni que el elemento exista y sea visible para quien reporta; se pueden crear reportes de elementos inexistentes o de cursos a los que el usuario no tiene acceso. |

## Archivos revisados que no son funciones externas

| Archivo | Tipo | Estado |
|---|---|---|
| src/classes/external/post_exporter.php | Exporter (`extends exporter`) | No aplica: no es un punto de entrada; no recibe parámetros de la petición. El contenido pasa por `format_text` con el contexto recibido, y `has_capability('local/chuspasocial:moderate')` (línea 158) solo se usa si la capacidad existe (`get_capability_info`). |

## Problemas de seguridad encontrados

| N.º | Archivo | Línea | Problema | Issue |
|---|---|---|---|---|
| 1 | src/classes/external/add_comment.php | 71-76 | No se comprueba que la publicación no esté oculta (`hidden = 0`) antes de comentar. | #918 |
| 2 | src/classes/external/toggle_reaction.php | 68-73 | No se comprueba que la publicación no esté oculta (`hidden = 0`) antes de reaccionar. | #919 |
| 3 | src/classes/external/report_item.php | 75-79 | No se valida `itemtype` contra los valores permitidos ni que el elemento exista y sea visible para quien reporta. | #920 |

## Problemas de funcionamiento (no de seguridad)

Estos problemas no abren ningún acceso indebido: en todos los casos la función
falla antes de modificar datos. Se dejan anotados porque impiden usarlas.

| Archivo | Línea | Problema |
|---|---|---|
| src/classes/external/toggle_reaction.php | 82 | Exige `local/chuspasocial:react`, que no está definida en `src/db/access.php`; `require_capability()` la deniega siempre. |
| src/classes/external/toggle_reaction.php | 84 | Llama a `reaction_service::toggle()`, pero `src/classes/local/reaction_service.php` no existe. |
| src/classes/external/report_item.php | 75 | Llama a `report_service::create_report()`, pero `src/classes/local/report_service.php` no existe. |

## Directrices para funciones externas

Estas directrices complementan la revisión anterior (fecha 2026-10-21) y son la
regla vigente para las funciones externas actuales y para cualquier función
nueva de `src/classes/external/`.

### Validación de contexto obligatoria

Toda función externa que extienda `external_api` debe llamar a
`self::validate_context()` antes de leer o escribir los datos y antes de armar
la respuesta. El orden obligatorio es:

1. `self::validate_parameters(self::execute_parameters(), [...])` para validar
   los tipos y valores que llegan en la petición.
2. Obtener el contexto que corresponde a los datos:
   - `context_course::instance($courseid)` si la operación es sobre una
     publicación, un comentario o un anuncio de un curso;
   - `context_system::instance()` si es sobre el muro general o datos sin
     curso.
3. `self::validate_context($context)`.
4. `require_capability('local/chuspasocial:...', $context)` con la capacidad
   que corresponda, en ese mismo contexto.

`self::validate_context()` llama a
`require_login($course, false, $cm, false, true)` (external_api.php de Moodle,
línea 521): en un contexto de curso exige que el usuario tenga acceso al curso
y en el contexto de sistema exige sesión iniciada. No alcanza con
`require_login()` a nivel de sitio ni con comprobar la capacidad en un contexto
distinto del que se usa para leer o escribir.

Las funciones AJAX (`ajax => true` en `src/db/services.php`) no son una
excepción: Moodle comprueba la sesskey antes de ejecutarlas, pero la validación
de contexto y de capacidad sigue siendo responsabilidad de la función externa.

La única lectura permitida antes de `validate_context()` es la mínima para
obtener el `courseid` del dato (por ejemplo, `id` y `courseid`); el resto de la
lectura y cualquier escritura van después de la validación.

Ejemplo:

    global $DB;

    $params = self::validate_parameters(self::execute_parameters(), [
        'postid' => $postid,
    ]);

    $post = $DB->get_record(
        'local_chuspasocial_post',
        ['id' => $params['postid']],
        'id, courseid',
        MUST_EXIST
    );

    if ((int) $post->courseid > 0) {
        $context = context_course::instance((int) $post->courseid);
    } else {
        $context = context_system::instance();
    }

    self::validate_context($context);
    require_capability('local/chuspasocial:comment', $context);

### Pautas para estructurar descripciones de parámetros y retornos

Cada parámetro de `execute_parameters()` y cada campo de `execute_returns()`
debe declararse con `external_value()` o con la estructura anidada que
corresponda (`external_single_structure` o `external_multiple_structure`) y
llevar una descripción en el segundo argumento. Esa descripción es la
documentación que Moodle muestra del servicio y la que usa el revisor para
entender el contrato; no reemplaza a la validación con `validate_parameters()`.

- Escribir la descripción en inglés (convención del código), en una frase
  corta, en presente y terminada en punto.
- Decir qué representa el valor, sin repetir el tipo: `'Post id.'`, no
  `'Integer with the post id.'`.
- Incluir la unidad, el rango, los valores permitidos o el valor por defecto
  cuando correspondan: `'Page number, starting at 0.'`,
  `'Listings per page (1 to 50).'`, `'Listing status: available or sold.'`.
- Si el parámetro es opcional, usar `VALUE_DEFAULT` con el valor por defecto
  (o `VALUE_OPTIONAL` si puede no venir) y decirlo en la descripción:
  `'Text to search in the title or author. Empty to get the listings of my
  courses.'`.
- En los retornos, la estructura debe tener exactamente las mismas claves que
  el array que devuelve `execute()`. Los objetos anidados usan
  `external_single_structure` y las listas `external_multiple_structure`.
- Si un campo del retorno puede faltar, declararlo con `VALUE_OPTIONAL` y
  explicarlo en la descripción.
- Si el retorno es contenido formateado, decirlo y devolver también el formato:
  `'Formatted description.'` junto con `descriptionformat`.
- El PHPDoc de `execute()` documenta cada `@param` y el `@return`, y
  `execute_parameters()` y `execute_returns()` declaran su `@return`
  (`external_function_parameters`, `external_single_structure` o
  `external_multiple_structure`).

Ejemplo:

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'postid' => new external_value(PARAM_INT, 'Post id.'),
            'content' => new external_value(PARAM_RAW_TRIMMED, 'Comment content.'),
        ]);
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Comment id.'),
            'postid' => new external_value(PARAM_INT, 'Post id.'),
            'content' => new external_value(PARAM_RAW, 'Comment content.'),
            'contentformat' => new external_value(PARAM_INT, 'Comment content format.'),
        ]);
    }

### Lista de verificación

- [ ] Toda función externa llama a `self::validate_parameters()` con su
      `execute_parameters()`.
- [ ] El contexto se obtiene del curso del dato (`context_course`) o es de
      sistema (`context_system`), según corresponda.
- [ ] `self::validate_context()` se llama antes de leer o escribir los datos y
      antes de armar la respuesta.
- [ ] Las funciones AJAX validan el contexto y la capacidad que corresponda,
      aunque Moodle ya exija la sesskey.
- [ ] `require_capability()` se exige en el mismo contexto y la capacidad está
      definida en `src/db/access.php`.
- [ ] Antes de `validate_context()` solo se lee lo necesario para obtener el
      `courseid` del dato.
- [ ] Cada parámetro y cada retorno tiene tipo `PARAM_*` y descripción; los
      opcionales declaran su valor por defecto.
- [ ] Las claves de `execute_returns()` coinciden con las del array que
      devuelve `execute()`.
- [ ] El PHPDoc de `execute()` documenta `@param` y `@return`.
