# ADR-006: Usar la mensajería de Moodle

## Estado

Aceptado.

## Contexto

ChuspaSocial necesita un mecanismo para enviar notificaciones a los usuarios y facilitar el contacto entre las personas interesadas en los libros publicados en el marketplace y sus vendedores.

Como el proyecto está desarrollado sobre Moodle, es conveniente aprovechar los servicios que la plataforma ya ofrece. Implementar un sistema de mensajería propio supondría desarrollar y mantener funcionalidades que Moodle ya proporciona, además de gestionar aspectos como los destinatarios y la integración con los usuarios de la plataforma.

## Decisión

Se decide utilizar la API de mensajería de Moodle, mediante el componente `core_message`, para gestionar el envío de mensajes y las notificaciones correspondientes dentro de ChuspaSocial.

El contacto entre compradores interesados y vendedores se apoyará en la mensajería de Moodle, utilizando los usuarios registrados en la plataforma. Las notificaciones relacionadas con las funcionalidades del plugin también podrán utilizar este mecanismo cuando corresponda.

La implementación deberá respetar las capacidades de mensajería, los permisos y las restricciones de acceso definidos por Moodle.

### Proveedores de mensajes registrados

ChuspaSocial registra en `db/messages.php` los siguientes proveedores de mensajes del componente `local_chuspasocial`, uno por cada tipo de notificación que el plugin envía a través de `core_message`:

| Nombre simbólico | Tipo de notificación | Cuándo se envía | Se envía desde |
| --- | --- | --- | --- |
| `gradepublished` | Calificación publicada | Se envía de forma privada al estudiante calificado cuando se publica una calificación; el aviso indica el curso. | `src/classes/local/observer.php` |
| `newfollower` | Nuevo seguidor | Se envía a la persona seguida cuando otra comienza a seguirla. | `src/classes/local/follow_service.php` |
| `newcomment` | Nuevo comentario | Se envía al autor de la publicación cuando otra persona la comenta; no se envía si quien comenta es el propio autor. | `src/classes/local/comment_service.php` |
| `newreport` | Nuevo reporte | Se envía a cada usuario que puede moderar, excepto a quien generó el reporte. | `src/classes/local/report_service.php` |

Todos los proveedores declaran la notificación emergente (`popup`) y el correo electrónico (`email`) como canales permitidos y habilitados por defecto, de modo que cada persona puede ajustarlos desde sus preferencias de notificación de Moodle.

## Alternativas descartadas

- **Implementar un sistema de mensajería propio:** se descarta porque duplicaría funcionalidades existentes en Moodle y aumentaría el esfuerzo de desarrollo y mantenimiento.
- **Utilizar un servicio externo de mensajería:** se descarta porque añadiría una dependencia externa, requeriría configuración adicional y podría complicar la integración con las cuentas de usuario de Moodle. Además, enviar los avisos fuera de la plataforma obligaría a compartir con un tercero datos de los usuarios (como su correo electrónico y su actividad en el marketplace), dejaría los avisos al margen de las preferencias de notificación de Moodle, donde cada persona elige por qué canal recibirlos, e impediría que el sitio los gestione desde un único lugar.

## Consecuencias

- Se aprovecha la infraestructura de mensajería existente en Moodle.
- Se facilita el contacto entre los usuarios interesados en los libros y sus vendedores.
- Se reduce la necesidad de mantener un sistema de mensajería independiente.
- La solución depende de las funcionalidades y de la configuración de mensajería disponibles en Moodle.
- El plugin deberá respetar los permisos de los usuarios y utilizar correctamente la API `core_message` para los mensajes y las notificaciones.
- Cada nuevo tipo de notificación exige declarar su proveedor en `db/messages.php` y su cadena `messageprovider:<nombre>` en los archivos de idioma del plugin.
