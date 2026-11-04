# Revisión de escape de salida: etiquetas sin escape en las plantillas

**Fecha:** 2026-10-18
**Rama:** security/audit-escaping
**Alcance:** todas las plantillas Mustache del plugin (`src/templates/`) y el
código PHP que arma su contexto.
**Método:** buscar las etiquetas sin escape (`{{{ }}}`) en las plantillas y
comprobar que el contenido que reciben haya pasado por `format_text()` antes de
llegar a la plantilla. Las etiquetas dobles (`{{ }}`) no se revisan porque
Mustache escapa su salida automáticamente.

## Resumen

- Plantillas Mustache revisadas: 4.
- Etiquetas sin escape (`{{{ }}}`) encontradas: 2.
- Llamadas a `format_text()` en el plugin: 0.
- Contenido que se pueda confirmar que pasó por `format_text()`: 0.
- Problema explotable encontrado: ninguno (ver Conclusión).

## Etiquetas sin escape encontradas

| Archivo | Línea | Etiqueta | Estado |
|---|---|---|---|
| src/templates/post.mustache | 53 | `{{{content}}}` | Sin confirmar: ningún PHP renderiza la plantilla ni arma su contexto. |
| src/templates/comments.mustache | 60 | `{{{content}}}` | Sin confirmar: ningún PHP renderiza la plantilla ni arma su contexto. |

`content` es el texto que publica el usuario. Al usar `{{{ }}}` (sin escape), la
plantilla exige que el valor ya venga saneado con `format_text()`; hoy ninguna
parte del plugin llama a esa función, así que no se puede confirmar el saneado.
`src/index.php` solo imprime el encabezado y el pie de Moodle y todavía no
renderiza ninguna plantilla (no hay llamadas a `render_from_template`), por lo
que ambas etiquetas están en plantillas que aún no tienen datos.

## Plantillas revisadas sin etiquetas sin escape

- src/templates/listing.mustache: usa solo etiquetas dobles (`{{ }}`), que Mustache escapa.
- src/templates/wall.mustache: no contiene etiquetas, solo un `div` vacío.

## Otros archivos revisados sin etiquetas sin escape

La búsqueda de `{{{ }}}` se hizo sobre todo `src/`. En estos archivos no
aparece ninguna etiqueta y, en los PHP, tampoco `format_text` ni
`render_from_template`:

- src/README.md
- src/amd/src/follow.js
- src/amd/src/wall.js
- src/classes/event/comment_created.php
- src/classes/event/listing_created.php
- src/classes/event/post_created.php
- src/classes/event/post_deleted.php
- src/classes/event/reaction_added.php
- src/classes/form/listing_form.php
- src/classes/form/post_form.php
- src/classes/local/hook_callbacks.php
- src/classes/local/listing_service.php
- src/classes/local/post_service.php
- src/db/access.php
- src/db/events.php
- src/db/install.xml
- src/db/messages.php
- src/db/services.php
- src/db/tag.php
- src/index.php
- src/lang/en/local_chuspasocial.php
- src/lang/es/local_chuspasocial.php
- src/pix/icon.svg
- src/settings.php
- src/styles.css
- src/tests/behat/wall.feature
- src/tests/lib_test.php
- src/tests/local/persistent/report_test.php
- src/tests/local/post_service_test.php
- src/version.php

## Conclusión

En la fecha de esta revisión no había ninguna salida de contenido de usuario
sin sanear, porque ninguna plantilla se renderizaba todavía desde PHP: las dos
etiquetas sin escape estaban en plantillas que aún no recibían datos. Por eso
no se abrió ningún issue `security:` nuevo. Quedó como requisito obligatorio
que el `content` de publicaciones y comentarios pase por `format_text()` (con
el `format` guardado en la publicación o el comentario) antes de armar el
contexto; las directrices de abajo detallan cómo cumplirlo.

## Directrices de escape en Mustache

Estas directrices complementan la revisión anterior (fecha 2026-10-18) y son la
regla vigente para el código actual y para cualquier plantilla nueva.

### Llave doble y llave triple

- `{{valor}}` (llave doble) escapa el HTML antes de insertar el valor: `<`
  queda como `&lt;`, `&` como `&amp;` y las comillas como `&quot;`. Es la
  opción por defecto para datos de usuario de texto plano: nombres, títulos,
  asuntos, etiquetas, atributos y mensajes sin formato.
- `{{{valor}}}` (llave triple) inserta el valor sin escapar. Solo se usa
  cuando el valor ya es HTML seguro: el resultado de `format_text()` para
  publicaciones y comentarios, o el HTML que generan las APIs de Moodle
  (`html_writer`, `$OUTPUT`, etc.).
- Un dato crudo de la base o de la petición (`PARAM_RAW`, `PARAM_TEXT`, etc.)
  nunca va dentro de `{{{ }}}`. Si no pasó por `format_text()` ni por una API
  que devuelva HTML seguro, va con `{{ }}`.
- Un valor que ya es HTML no va dentro de `{{ }}`: Mustache lo escaparía y las
  etiquetas se verían como texto.

### Uso de s()

`s()` escapa los caracteres HTML (incluidas las comillas) y se usa para texto
plano cuando la salida se arma en PHP:

    echo s($user->firstname);
    $title = s($post->subject);
    echo html_writer::tag('span', s($name), ['class' => 'font-weight-bold']);

En las plantillas no hace falta llamar a `s()`: Mustache ya escapa con
`{{ }}`. Si un valor llega a la plantilla ya escapado con `s()`, se inserta
con `{{{ }}}` para no escaparlo dos veces; lo recomendable es pasar el valor
sin escapar y dejar que la plantilla use `{{ }}`.

`s()` no reemplaza a `format_text()`: sirve para texto plano y no interpreta
el formato enriquecido. Tampoco se aplica al contenido que ya pasó por
`format_text()`, porque volvería a escaparlo y se verían las etiquetas HTML.

### Uso de format_text()

`format_text()` se usa para contenido enriquecido de usuario (publicaciones,
comentarios, anuncios) antes de pasarlo a la plantilla:

    $content = format_text($post->content, $post->format, ['context' => $context]);

- El segundo argumento es el formato guardado con el contenido
  (`FORMAT_MOODLE`, `FORMAT_HTML`, `FORMAT_MARKDOWN` o `FORMAT_PLAIN`), no un
  valor fijo.
- El contexto (`['context' => $context]`) es obligatorio para que los filtros
  y la limpieza de HTML usen la configuración correcta.
- Devuelve HTML seguro, así que en la plantilla va con `{{{ }}}`:

    <div class="card-text">{{{content}}}</div>

- En los servicios web se usa `\core_external\util::format_text()` con el
  mismo formato y contexto antes de devolver el contenido.

### Lista de verificación

- [ ] Los datos de usuario de texto plano usan `{{ }}`.
- [ ] Las etiquetas `{{{ }}}` solo reciben HTML ya saneado con `format_text()`
      o generado por las APIs de Moodle.
- [ ] Ningún `PARAM_RAW` de la petición llega a `{{{ }}}` sin pasar por
      `format_text()`.
- [ ] `format_text()` recibe el formato guardado y el contexto del contenido.
- [ ] `s()` se usa para texto plano en salida PHP y no se aplica a contenido
      ya formateado con `format_text()`.
- [ ] No se usa `{{ }}` para insertar HTML formateado (se verían las etiquetas).
