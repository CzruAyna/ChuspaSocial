# ADR-001: Usar un plugin de tipo local

## Estado

Aceptado.

## Contexto

ChuspaSocial necesita ofrecer páginas propias y una navegación disponible de forma global dentro de Moodle. Estas funciones no corresponden a una actividad concreta de un curso ni a un bloque que deba colocarse en una región específica de una página.

El tipo de plugin elegido debe permitir integrar estas páginas y funcionalidades sociales sin obligar a que cada curso agregue una actividad o un bloque para acceder a ellas.

## Decisión

Implementar ChuspaSocial como un plugin de tipo local con el componente `local_chuspasocial`.

Los plugins locales permiten incorporar funcionalidad específica del sitio y páginas propias, por lo que se ajustan al alcance de ChuspaSocial y a la necesidad de disponer de navegación global.

## Alternativas descartadas

- Usar un plugin de tipo `block_`. Se descarta porque un bloque está orientado a mostrarse dentro de regiones de bloques y no representa adecuadamente las páginas y navegación global que necesita ChuspaSocial. Además, un bloque depende de que alguien lo agregue en cada página o curso donde deba aparecer, y su espacio reducido sirve para mostrar contenido resumido, no para los flujos completos del muro y del marketplace (publicar, explorar, ver el detalle y gestionar anuncios).
- Usar un plugin de tipo `mod_`. Se descarta porque los módulos de actividad se agregan dentro de cursos como actividades, mientras que ChuspaSocial necesita funcionalidades disponibles más allá de una actividad concreta. Además, un `mod_` se instancia por curso y se acopla a mecanismos propios de las actividades (respaldo y restauración de cursos, calificaciones, finalización), que no aportan valor al muro ni al marketplace, ya que ambos son funcionalidades del sitio completo.

## Consecuencias

ChuspaSocial se identificará mediante el componente `local_chuspasocial` y su código seguirá la estructura esperada para un plugin local de Moodle.

La solución podrá ofrecer páginas y navegación propias sin depender de que un docente agregue una actividad o un bloque en cada curso. A cambio, las funcionalidades específicas de ChuspaSocial deberán mantenerse dentro del alcance y convenciones de un plugin local.

Frente a un bloque o un módulo de actividad, el plugin local ofrece ventajas operativas para coordinar el muro y el marketplace:

- Un único componente de alcance de sitio aloja el muro y el marketplace, por lo que ambos comparten servicios, capacidades y tablas de base de datos sin depender del contexto de un curso.
- Se instala, se actualiza y se versiona una sola vez, sin instancias por curso ni bloques que configurar en cada página.
- Concentra en un mismo lugar las páginas propias, los servicios web, las tareas programadas y los observadores de eventos de ambas funcionalidades.

Como costo, el tipo local no incluye interfaz propia ni integración automática con respaldo y restauración, calificaciones o finalización de actividades: la navegación y los puntos de entrada se registran por código, y el muro y el marketplace quedan acoplados a la misma unidad de versionado y despliegue.
