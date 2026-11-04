# Guía de eventos y observadores de ChuspaSocial

## Objetivo

Este documento describe los eventos principales de ChuspaSocial y la forma en que
otros componentes de Moodle pueden observarlos. Los eventos del plugin extienden
`\core\event\base` y permiten registrar acciones relevantes sin acoplar la lógica
que reacciona a ellas con el código que las genera.

## Arquitectura de eventos

Las clases de evento se encuentran en `src/classes/event/`. Cada una define en
`init()`:

- `crud`: tipo de operación realizada.
- `edulevel`: nivel educativo del evento.
- `objecttable`: tabla relacionada con el objeto afectado.

Los datos como `objectid`, `userid` y `context` se entregan cuando se crea la
instancia con `::create()`. Las clases actuales no agregan datos específicos en
`other`, por lo que los observadores no deben depender de ese campo.

## Eventos disponibles

| Evento | Clase | Tabla del objeto | CRUD | Nivel educativo | Contexto y datos principales |
|---|---|---|---|---|---|
| `post_created` | `\local_chuspasocial\event\post_created` | `local_chuspasocial_post` | `c` | `LEVEL_PARTICIPATING` | `objectid`: id de la publicación; `userid`: autor de la acción; `context`: se proporciona al crear el evento. |
| `post_deleted` | `\local_chuspasocial\event\post_deleted` | `local_chuspasocial_post` | `d` | `LEVEL_PARTICIPATING` | `objectid`: id de la publicación eliminada; `userid`: usuario que elimina; `context`: curso de la publicación o sistema para el muro general. |
| `comment_created` | `\local_chuspasocial\event\comment_created` | `local_chuspasocial_comment` | `c` | `LEVEL_PARTICIPATING` | `objectid`: id del comentario; `userid`: autor del comentario; `context`: se proporciona al crear el evento. |
| `reaction_added` | `\local_chuspasocial\event\reaction_added` | `local_chuspasocial_reaction` | `c` | `LEVEL_PARTICIPATING` | `objectid`: id de la reacción; `userid`: usuario que reacciona; `context`: se proporciona al crear el evento. |

### `post_created`

Representa la creación de una publicación.

- **Clase:** `\local_chuspasocial\event\post_created`.
- **Objeto afectado:** registro de `local_chuspasocial_post`.
- **CRUD:** creación (`c`).
- **Nivel educativo:** `LEVEL_PARTICIPATING`.
- **Datos:** el id de la publicación viaja en `objectid` y el usuario responsable
  en `userid`.
- **`other`:** no se define información adicional actualmente.

### `post_deleted`

Representa la eliminación de una publicación.

- **Clase:** `\local_chuspasocial\event\post_deleted`.
- **Objeto afectado:** registro de `local_chuspasocial_post`.
- **CRUD:** eliminación (`d`).
- **Nivel educativo:** `LEVEL_PARTICIPATING`.
- **Datos:** el id eliminado viaja en `objectid` y el usuario que realiza la
  acción en `userid`.
- **Contexto:** `post_service::delete_post()` usa el contexto del curso cuando la
  publicación pertenece a una materia y `context_system` para el muro general.
- **`other`:** no se define información adicional actualmente.

### `comment_created`

Representa la creación de un comentario.

- **Clase:** `\local_chuspasocial\event\comment_created`.
- **Objeto afectado:** registro de `local_chuspasocial_comment`.
- **CRUD:** creación (`c`).
- **Nivel educativo:** `LEVEL_PARTICIPATING`.
- **Datos:** el id del comentario viaja en `objectid` y el autor en `userid`.
- **`other`:** no se define información adicional actualmente.

### `reaction_added`

Representa la creación de una reacción.

- **Clase:** `\local_chuspasocial\event\reaction_added`.
- **Objeto afectado:** registro de `local_chuspasocial_reaction`.
- **CRUD:** creación (`c`).
- **Nivel educativo:** `LEVEL_PARTICIPATING`.
- **Datos:** el id de la reacción viaja en `objectid` y el usuario que reacciona
  en `userid`.
- **`other`:** no se define información adicional actualmente.

## Ejemplo de creación y disparo de un evento

El código que genera el evento debe conocer el objeto afectado y su contexto.

```php
$event = \local_chuspasocial\event\post_created::create([
    'objectid' => $postid,
    'context' => $context,
    'userid' => $USER->id,
]);

$event->trigger();
```

El evento debe dispararse cuando la operación que representa se haya completado
correctamente.

## Suscribirse a un evento con un observador

Un plugin que necesite reaccionar a un evento de ChuspaSocial puede registrarlo
en su propio `db/events.php`. Por ejemplo, para observar la creación de
comentarios:

```php
<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => \local_chuspasocial\event\comment_created::class,
        'callback' => '\\local_miplugin\\observer::comment_created',
    ],
];
```

El callback recibe el objeto del evento:

```php
<?php

namespace local_miplugin;

class observer {
    public static function comment_created(
        \local_chuspasocial\event\comment_created $event
    ): void {
        $commentid = (int) $event->objectid;
        $userid = (int) $event->userid;
        $contextid = (int) $event->contextid;

        // Procesar el evento sin modificar sus datos.
    }
}
```

La misma estructura puede utilizarse para `post_created`, `post_deleted` y
`reaction_added`, cambiando `eventname` y el tipo recibido por el callback.

## Observadores registrados por ChuspaSocial

El archivo `src/db/events.php` también se usa para observar eventos externos al
plugin. Actualmente ChuspaSocial escucha eventos del núcleo de Moodle, como
`\core\event\user_graded` y `\core\event\course_module_created`, y los
redirige a métodos de `\local_chuspasocial\local\observer`.

Los eventos propios documentados en esta guía son independientes de esos
observadores: se generan en ChuspaSocial y pueden ser consumidos por cualquier
observador registrado en Moodle.
