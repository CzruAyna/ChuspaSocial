# Requerimientos del Proyecto

## Requerimientos Funcionales

### Muro

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-001 | Ver el muro general al ingresar y mostrar primero las publicaciones más recientes | Alta | Pendiente |
| RF-003 | Publicar en el muro de una materia | Alta | Pendiente |
| RF-004 | Adjuntar imágenes a una publicación | Media | Pendiente |
| RF-006 | Eliminar una publicación propia | Alta | Pendiente |
| RF-007 | Ver el muro de una materia | Alta | Pendiente |
| RF-008 | Cargar más publicaciones | Media | Pendiente |
| RF-009 | Dar formato seguro al texto de una publicación (enlaces y negrita) | Alta | Pendiente |

### Comentarios

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-010 | Comentar una publicación | Alta | Pendiente |
| RF-011 | Eliminar un comentario propio | Media | Pendiente |
| RF-012 | Ver los comentarios de una publicación | Alta | Pendiente |

### Reacciones

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-013 | Reaccionar a una publicación | Alta | Pendiente |
| RF-014 | Ver el número de reacciones | Media | Pendiente |

### Tags

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-015 | Etiquetar una publicación | Alta | Pendiente |
| RF-016 | Filtrar el muro por tag | Alta | Pendiente |
| RF-017 | Seguir un tag | Media | Pendiente |

### Seguidores

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-018 | Seguir a un usuario | Media | Pendiente |
| RF-019 | Ver el perfil social de un usuario | Media | Pendiente |
| RF-020 | Ver la lista de seguidores y seguidos | Baja | Pendiente |
| RF-021 | Ver el feed personalizado | Media | Pendiente |

### Anuncios

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-022 | Publicar un anuncio oficial en una materia | Alta | Pendiente |
| RF-023 | Publicar un anuncio institucional en el muro general | Media | Pendiente |
| RF-024 | Destacar visualmente los anuncios oficiales | Media | Pendiente |

### Actividad de Moodle

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-025 | Mostrar tareas nuevas en el muro de la materia | Media | Pendiente |
| RF-026 | Avisar en privado que hay una calificación publicada: solo lo recibe el estudiante calificado y nunca es público | Baja | Pendiente |

### Notificaciones

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-027 | Notificar un comentario en mi publicación | Media | Pendiente |
| RF-028 | Notificar al usuario cuando obtiene un nuevo seguidor | Baja | Pendiente |

### Reportes

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-029 | Reportar una publicación, comentario o anuncio de libro | Alta | Pendiente |
| RF-030 | Revisar la cola de reportes | Alta | Pendiente |
| RF-031 | Ocultar el contenido reportado para que deje de mostrarse a los usuarios | Alta | Pendiente |

### Marketplace

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-032 | Publicar un anuncio de libro con título, autor, descripción, precio y fotos | Alta | Pendiente |
| RF-033 | Vincular un libro a una o más materias | Alta | Pendiente |
| RF-034 | Ver los libros de mis materias | Alta | Pendiente |
| RF-035 | Buscar libros por título o autor | Media | Pendiente |
| RF-036 | Contactar al vendedor por la mensajería de Moodle | Alta | Pendiente |
| RF-037 | Mostrar un contacto externo opcional | Baja | Pendiente |
| RF-038 | Marcar un libro como vendido | Media | Pendiente |
| RF-039 | Editar o eliminar un anuncio propio | Media | Pendiente |
| RF-040 | Subir fotos del libro | Media | Pendiente |

### Administración

| ID | Descripción | Prioridad | Estado |
|---|---|---|---|
| RF-041 | Activar o desactivar el Marketplace | Media | Pendiente |
| RF-042 | Configurar los límites del muro: longitud máxima de una publicación, cantidad máxima de imágenes por publicación y número de publicaciones mostradas por página | Baja | Pendiente |

### Criterios de aceptación

#### Muro

##### RF-001 - Ver el muro general al ingresar

**Criterio 1**
- **Dado** un usuario autenticado,
- **Cuando** ingresa al módulo Muro,
- **Entonces** el sistema muestra las publicaciones disponibles en el muro general.

**Criterio 2**
- **Dado** que existen varias publicaciones en el muro,
- **Cuando** el usuario visualiza el muro general,
- **Entonces** las publicaciones se muestran desde la más reciente hasta la más antigua.

##### RF-003 - Publicar en el muro de una materia

**Criterio 1**
- **Dado** que el usuario ha iniciado sesión y pertenece a una materia,
- **Cuando** publica un mensaje en el muro de esa materia,
- **Entonces** el sistema debe registrar y mostrar la publicación.

**Criterio 2**
- **Dado** que el usuario intenta publicar un mensaje vacío,
- **Cuando** confirma la publicación,
- **Entonces** el sistema debe impedir que se publique.

##### RF-004 - Adjuntar imágenes a una publicación

**Criterio 1**
- **Dado** un usuario autenticado que está creando o editando una publicación en el muro,
- **Cuando** selecciona adjuntar imágenes en formatos válidos (`.jpg`, `.png`, `.webp`) y la cantidad no supera el límite máximo N configurado,
- **Entonces** el sistema debe permitir adjuntar y previsualizar las imágenes en la publicación.

**Criterio 2**
- **Dado** un usuario autenticado en la pantalla de crear/editar publicación,
- **Cuando** intenta adjuntar un archivo con un formato no permitido o supera la cantidad máxima N de imágenes permitidas,
- **Entonces** el sistema debe mostrar un mensaje de error restrictivo y evitar que se suban las imágenes.

##### RF-006 - Eliminar una publicación propia

**Criterio 1**
- **Dado** un usuario autenticado que tiene una publicación propia en el muro,
- **Cuando** selecciona la opción de eliminar su publicación y confirma la acción,
- **Entonces** el sistema elimina la publicación seleccionada y deja de mostrarla en el muro.

**Criterio 2**
- **Dado** un usuario que intenta eliminar una publicación creada por otro usuario,
- **Cuando** confirma la acción de eliminación,
- **Entonces** el sistema rechaza la operación y mantiene la publicación sin cambios.

##### RF-007 - Ver el muro de una materia

**Criterio 1**
- **Dado** un estudiante inscrito en una materia,
- **Cuando** accede al curso y selecciona la opción para ver el muro de la materia,
- **Entonces** el sistema muestra el muro filtrado con las publicaciones de esa materia.

**Criterio 2**
- **Dado** un usuario que no está inscrito en una materia,
- **Cuando** intenta acceder al muro de esa materia,
- **Entonces** el sistema rechaza el acceso y no muestra las publicaciones de la materia.

##### RF-008 - Cargar más publicaciones

**Criterio 1**
- **Dado** que el usuario se encuentra en el módulo Muro y existen más publicaciones disponibles en la base de datos,
- **Cuando** visualice el final de las publicaciones actuales y haga clic en el botón "Cargar más",
- **Entonces** el sistema deberá cargar y mostrar las siguientes publicaciones en la pantalla sin recargar la página.

**Criterio 2**
- **Dado** que el usuario está en el módulo Muro,
- **Cuando** ya se hayan cargado todas las publicaciones existentes en la base de datos,
- **Entonces** el botón "Cargar más" deberá quedar oculto o deshabilitado.

##### RF-009 - Dar formato seguro al texto de una publicación

**Criterio 1**
- **Dado** que el usuario está redactando una publicación en el muro,
- **Cuando** aplica negrita a una parte del texto o agrega un enlace y publica,
- **Entonces** la publicación se muestra con el texto en negrita y el enlace activo, que se puede abrir con un clic.

**Criterio 2**
- **Dado** que el contenido de una publicación incluye código HTML o JavaScript no permitido (por ejemplo, etiquetas `<script>` o atributos como `onclick`),
- **Cuando** el usuario publica,
- **Entonces** el sistema limpia ese contenido antes de guardarlo y mostrarlo, sin ejecutar ningún código, y conserva el texto y los formatos permitidos.

**Criterio 3**
- **Dado** que el usuario agrega un enlace con un esquema no seguro (por ejemplo, `javascript:`),
- **Cuando** la publicación se muestra en el muro,
- **Entonces** el sistema no lo presenta como un enlace activo.

#### Comentarios

##### RF-010 - Comentar una publicación

**Criterio 1**
- **Dado** un usuario autenticado que puede ver una publicación,
- **Cuando** escribe un comentario válido y lo envía,
- **Entonces** el sistema registra el comentario y lo muestra asociado a la publicación.

**Criterio 2**
- **Dado** un usuario que no puede ver una publicación,
- **Cuando** intenta comentar esa publicación,
- **Entonces** el sistema impide que registre un comentario en ella.

##### RF-011 - Eliminar un comentario propio

**Criterio 1**
- **Dado** un usuario autenticado que ha realizado un comentario en una publicación,
- **Cuando** selecciona la opción de eliminar su comentario y confirma la acción,
- **Entonces** el sistema debe remover el comentario de la publicación y actualizar el contador de comentarios.

**Criterio 2**
- **Dado** un usuario autenticado que navega por los comentarios de una publicación,
- **Cuando** visualiza un comentario que pertenece a otro usuario,
- **Entonces** el sistema no debe mostrar la opción de eliminar dicho comentario.

##### RF-012 - Ver los comentarios de una publicación

**Criterio 1**
- **Dado** un usuario que visualiza una publicación con comentarios,
- **Cuando** consulta la publicación,
- **Entonces** el sistema muestra sus comentarios en orden cronológico debajo de la publicación.

**Criterio 2**
- **Dado** un usuario que visualiza una publicación sin comentarios,
- **Cuando** consulta la publicación,
- **Entonces** el sistema muestra el área de comentarios sin entradas y permite identificar que aún no existen comentarios.

#### Reacciones

##### RF-013 - Reaccionar a una publicación

**Criterio 1**
- **Dado** un usuario autenticado que visualiza una publicación,
- **Cuando** selecciona la opción «Me gusta»,
- **Entonces** el sistema registra la reacción del usuario en esa publicación.

**Criterio 2**
- **Dado** un usuario que ya reaccionó con «Me gusta» a una publicación,
- **Cuando** vuelve a seleccionar la misma opción,
- **Entonces** el sistema actualiza el estado de su reacción sin crear registros duplicados.

##### RF-014 - Ver el número de reacciones

**Criterio 1**
- **Dado** un usuario que visualiza una publicación,
- **Cuando** la publicación tiene una o más reacciones,
- **Entonces** se muestra el número total de reacciones de la publicación.

**Criterio 2**
- **Dado** una publicación que no tiene reacciones,
- **Cuando** el usuario visualiza la publicación,
- **Entonces** se muestra que el total de reacciones es cero.

#### Tags

##### RF-015 - Etiquetar una publicación

**Criterio 1**
- **Dado** que el usuario se encuentra creando o editando una publicación,
- **Cuando** empiece a escribir en el campo de etiquetas o use los tags predefinidos de Moodle,
- **Entonces** el sistema deberá asociar de forma correcta dichas etiquetas a la publicación al momento de guardarla.

**Criterio 2**
- **Dado** que una publicación cuenta con etiquetas asociadas,
- **Cuando** se visualice la publicación en el muro,
- **Entonces** las etiquetas deberán ser visibles para todos los usuarios y permitir la navegación o filtrado por esos tags.

##### RF-016 - Filtrar el muro por tag

**Criterio 1**
- **Dado** que el usuario se encuentra visualizando las publicaciones en el muro,
- **Cuando** haga clic sobre una etiqueta (tag) específica en cualquier publicación,
- **Entonces** el sistema deberá recargar el listado mostrando únicamente las publicaciones que contengan dicha etiqueta.

**Criterio 2**
- **Dado** que el muro se encuentra filtrado por una etiqueta específica,
- **Cuando** el usuario decida limpiar el filtro o hacer clic en la opción de restablecer el muro,
- **Entonces** el sistema deberá mostrar nuevamente todas las publicaciones disponibles sin ningún tipo de filtro.

##### RF-017 - Seguir un tag

**Criterio 1**
- **Dado** un usuario autenticado que visualiza una etiqueta (tag) en una publicación o en el muro filtrado por esa etiqueta,
- **Cuando** presiona la opción "Seguir tag",
- **Entonces** el sistema registra que el usuario sigue ese tag y le muestra que ya lo está siguiendo.

**Criterio 2**
- **Dado** un usuario que sigue un tag,
- **Cuando** accede a su feed personalizado,
- **Entonces** ve las publicaciones etiquetadas con ese tag, junto con las de los usuarios que sigue, ordenadas por fecha.

**Criterio 3**
- **Dado** un usuario que sigue un tag,
- **Cuando** presiona la opción "Dejar de seguir",
- **Entonces** el sistema elimina el seguimiento y las nuevas publicaciones con ese tag dejan de aparecer en su feed personalizado.

#### Seguidores

##### RF-018 - Seguir a un usuario

**Criterio 1**
- **Dado** un usuario autenticado que visita el perfil social de otro usuario,
- **Cuando** presiona el botón "Seguir",
- **Entonces** el sistema registra que el usuario está siguiendo a ese perfil.

**Criterio 2**
- **Dado** un usuario que ya sigue a otro usuario,
- **Cuando** consulta el perfil social de ese usuario,
- **Entonces** el sistema muestra que ya lo está siguiendo y no permite crear un seguimiento duplicado.

##### RF-019 - Ver el perfil social de un usuario

**Criterio 1**
- **Dado** que el usuario se encuentra navegando en la plataforma,
- **Cuando** haga clic sobre el nombre o foto de perfil de otro usuario,
- **Entonces** el sistema deberá redirigirlo a su perfil social, mostrando sus publicaciones visibles, su lista de seguidores y sus seguidos.

**Criterio 2**
- **Dado** que el usuario está visitando el perfil de otra persona,
- **Cuando** intente interactuar con la información pública del perfil,
- **Entonces** el sistema deberá permitirle ver el contador actualizado de seguidores y seguidos en tiempo real.

##### RF-020 - Ver la lista de seguidores y seguidos

**Criterio 1**
- **Dado** un usuario autenticado que sigue a otros usuarios y tiene seguidores,
- **Cuando** accede a la lista de seguidores y seguidos,
- **Entonces** ve ambas listas separadas y paginadas, mostrando la información básica de cada usuario.

**Criterio 2**
- **Dado** un usuario con muchos seguidores o seguidos,
- **Cuando** navega por la lista,
- **Entonces** la lista se pagina correctamente y puede avanzar o retroceder entre páginas sin perder el contexto.

##### RF-021 - Ver el feed personalizado

**Criterio 1**
- **Dado** un usuario autenticado que sigue a otros usuarios y tags,
- **Cuando** accede a su feed personalizado,
- **Entonces** ve las publicaciones de los usuarios y tags que sigue, ordenadas por fecha.

**Criterio 2**
- **Dado** un usuario que sigue nuevos usuarios o tags,
- **Cuando** recarga su feed personalizado,
- **Entonces** las nuevas publicaciones de esos usuarios y tags aparecen en el feed.

#### Anuncios

##### RF-022 - Publicar un anuncio oficial en una materia

**Criterio 1**
- **Dado** un docente autenticado en una materia,
- **Cuando** marca una publicación como anuncio oficial,
- **Entonces** el anuncio aparece destacado en el muro de la materia para todos los inscritos.

**Criterio 2**
- **Dado** un docente que redacta una publicación en su materia,
- **Cuando** la marca como anuncio oficial y confirma,
- **Entonces** el anuncio queda registrado como oficial con autor, fecha y materia, y es visible para los estudiantes inscritos.

##### RF-023 - Publicar un anuncio institucional en el muro general

**Criterio 1**
- **Dado** un administrador autenticado en la plataforma,
- **Cuando** publica un anuncio institucional desde el módulo Anuncios,
- **Entonces** el anuncio aparece en el muro general visible para todos los usuarios.

**Criterio 2**
- **Dado** un administrador que redacta un anuncio institucional,
- **Cuando** confirma la publicación,
- **Entonces** el anuncio queda registrado con autor, fecha y contenido, y es visible en el muro general.

##### RF-024 - Destacar visualmente los anuncios oficiales

**Criterio 1**
- **Dado** un anuncio oficial publicado en el muro,
- **Cuando** el usuario visualiza el muro o el anuncio,
- **Entonces** el anuncio se muestra con un color y un ícono distintos que lo diferencian de las publicaciones normales.

**Criterio 2**
- **Dado** un anuncio oficial y una publicación regular en el mismo muro,
- **Cuando** el usuario los compara visualmente,
- **Entonces** puede identificar el anuncio oficial por su estilo destacado (color e ícono) sin necesidad de leer el contenido.

#### Actividad de Moodle

##### RF-025 - Mostrar tareas nuevas en el muro de la materia

**Criterio 1**
- **Dado** un docente que crea una nueva tarea en una materia,
- **Cuando** la tarea se publica,
- **Entonces** se genera automáticamente una publicación de actividad en el muro de la materia visible para los estudiantes inscritos.

**Criterio 2**
- **Dado** un estudiante inscrito en una materia con tareas nuevas,
- **Cuando** accede al muro de la materia,
- **Entonces** ve las publicaciones de actividad correspondientes a las tareas nuevas, con la información de la tarea y su fecha.

##### RF-026 - Avisar en privado que hay una calificación publicada

**Criterio 1**
- **Dado** un docente que publica la calificación de un estudiante en una tarea o parcial,
- **Cuando** la calificación queda publicada,
- **Entonces** el sistema envía un aviso privado únicamente a ese estudiante indicando que su calificación está disponible.

**Criterio 2**
- **Dado** un aviso de calificación publicada,
- **Cuando** se genera la notificación,
- **Entonces** el aviso no aparece en el muro ni es visible para otros usuarios que no sean el estudiante calificado.

#### Notificaciones

##### RF-027 - Notificar un comentario en mi publicación

**Criterio 1**
- **Dado** un usuario que tiene una publicación en el muro,
- **Cuando** otro usuario escribe un comentario en esa publicación,
- **Entonces** el autor recibe una notificación de Moodle con el nombre de quien comentó.

**Criterio 2**
- **Dado** un usuario que comenta su propia publicación,
- **Cuando** se guarda el comentario,
- **Entonces** el sistema no envía ninguna notificación.

##### RF-028 - Notificar al usuario cuando obtiene un nuevo seguidor

**Criterio 1**
- **Dado** que un usuario sigue a otro usuario,
- **Cuando** se registra el nuevo seguimiento,
- **Entonces** el usuario seguido recibe una notificación indicando que tiene un nuevo seguidor.

**Criterio 2**
- **Dado** que un usuario ha recibido un nuevo seguimiento,
- **Cuando** consulta sus notificaciones,
- **Entonces** puede visualizar la notificación con el nombre del usuario que comenzó a seguirlo.

#### Reportes

##### RF-029 - Reportar una publicación, comentario o anuncio de libro

**Criterio 1**
- **Dado** que un usuario visualiza una publicación, comentario o anuncio de libro,
- **Cuando** selecciona la opción de reportar e indica un motivo,
- **Entonces** el sistema registra el reporte asociado al contenido seleccionado y al motivo indicado.

**Criterio 2**
- **Dado** que un usuario desea reportar un contenido,
- **Cuando** selecciona un motivo y confirma el reporte,
- **Entonces** el sistema guarda el reporte y notifica que fue registrado correctamente.

##### RF-030 - Revisar la cola de reportes

**Criterio 1**
- **Dado** que existen reportes abiertos en el sistema,
- **Cuando** un moderador accede a la cola de reportes,
- **Entonces** el sistema muestra los reportes abiertos pendientes de revisión.

**Criterio 2**
- **Dado** que un moderador visualiza la cola de reportes,
- **Cuando** selecciona un reporte,
- **Entonces** el sistema muestra la información del reporte para que pueda revisarlo.

##### RF-031 - Ocultar el contenido reportado

**Criterio 1**
- **Dado** que existe una publicación, comentario o anuncio de libro que ha sido reportado,
- **Cuando** un moderador revisa el reporte y decide ocultar el contenido,
- **Entonces** el sistema oculta dicho contenido para que deje de mostrarse a los usuarios.

**Criterio 2**
- **Dado** que un contenido reportado ha sido ocultado por un moderador,
- **Cuando** un usuario intenta visualizar dicho contenido,
- **Entonces** el sistema impide que el contenido ocultado sea mostrado al usuario.

#### Marketplace

##### RF-032 - Publicar un anuncio de libro

**Criterio 1**
- **Dado** un usuario autenticado que desea publicar un libro en el Marketplace,
- **Cuando** completa el título, autor, descripción, precio y agrega fotos del libro,
- **Entonces** el sistema permite publicar el anuncio del libro correctamente.

**Criterio 2**
- **Dado** un usuario que está publicando un anuncio de libro en el Marketplace,
- **Cuando** intenta publicar el anuncio sin completar alguno de los datos obligatorios,
- **Entonces** el sistema rechaza la publicación y muestra un mensaje indicando la información que falta.

##### RF-033 - Vincular un libro a una o más materias

**Criterio 1**
- **Dado** un usuario autenticado que está publicando un libro en el Marketplace,
- **Cuando** selecciona una o más materias existentes para vincularlas al libro,
- **Entonces** el sistema guarda el libro asociado correctamente a esas materias.

**Criterio 2**
- **Dado** un usuario que intenta vincular un libro a una materia que no existe en el sistema,
- **Cuando** confirma la publicación,
- **Entonces** el sistema rechaza la vinculación y muestra un mensaje indicando que solo puede seleccionar materias existentes.

##### RF-034 - Ver los libros de mis materias

**Criterio 1**
- **Dado** un usuario autenticado que está inscrito en una o más materias,
- **Cuando** accede a la sección de Marketplace,
- **Entonces** el sistema muestra únicamente los anuncios de libros vinculados a las materias en las que está inscrito.

**Criterio 2**
- **Dado** un usuario que no está inscrito en ninguna materia con libros publicados en el Marketplace,
- **Cuando** accede a esa sección,
- **Entonces** el sistema muestra un mensaje indicando que no hay libros disponibles para sus materias.

##### RF-035 - Buscar libros por título o autor

**Criterio 1**
- **Dado** que el usuario se encuentra en el buscador del Marketplace,
- **Cuando** ingresa el título de un libro existente y ejecuta la búsqueda de texto,
- **Entonces** el sistema debe retornar el libro correspondiente en los resultados.

**Criterio 2**
- **Dado** que el usuario se encuentra en el buscador del Marketplace,
- **Cuando** ingresa el nombre de un autor en el campo de texto,
- **Entonces** el sistema debe mostrar una lista con todos los libros registrados bajo ese autor.

##### RF-036 - Contactar al vendedor por la mensajería de Moodle

**Criterio 1**
- **Dado** un usuario autenticado que visualiza un anuncio publicado por otro usuario en el Marketplace,
- **Cuando** presiona el botón "Contactar",
- **Entonces** el sistema abre una conversación con el vendedor mediante la mensajería de Moodle.

**Criterio 2**
- **Dado** un usuario que ya inició una conversación con el vendedor desde el botón "Contactar",
- **Cuando** envía un mensaje dentro de esa conversación,
- **Entonces** el vendedor recibe el mensaje en su bandeja de mensajería de Moodle.

##### RF-037 - Mostrar un contacto externo opcional

**Criterio 1**
- **Dado** que el vendedor agregó un contacto externo a su anuncio,
- **Cuando** un comprador abre el detalle del anuncio,
- **Entonces** el sistema muestra el contacto externo junto con una advertencia de que la comunicación fuera de la mensajería de Moodle es responsabilidad de ambas partes.

**Criterio 2**
- **Dado** que el vendedor no agregó un contacto externo,
- **Cuando** un comprador abre el detalle del anuncio,
- **Entonces** el sistema no muestra ningún contacto externo y solo ofrece la mensajería de Moodle.

##### RF-038 - Marcar un libro como vendido

**Criterio 1**
- **Dado** un vendedor autenticado que tiene un anuncio propio publicado en el Marketplace,
- **Cuando** marca el libro como vendido,
- **Entonces** el sistema actualiza el estado del anuncio y deja de mostrarlo entre los libros disponibles.

**Criterio 2**
- **Dado** un libro que fue marcado como vendido,
- **Cuando** un usuario consulta los libros disponibles en el Marketplace,
- **Entonces** el libro vendido no aparece entre los resultados disponibles.

##### RF-039 - Editar o eliminar un anuncio propio

**Criterio 1**
- **Dado** un vendedor autenticado que tiene un anuncio propio publicado en el Marketplace,
- **Cuando** edita los datos del anuncio y guarda los cambios,
- **Entonces** el sistema actualiza el anuncio manteniendo al mismo vendedor como propietario.

**Criterio 2**
- **Dado** un vendedor autenticado que tiene un anuncio propio publicado en el Marketplace,
- **Cuando** solicita eliminarlo y confirma la acción,
- **Entonces** el sistema elimina el anuncio y deja de mostrarlo en el Marketplace.

##### RF-040 - Subir fotos del libro

**Criterio 1**
- **Dado** que el vendedor está publicando o editando un anuncio,
- **Cuando** selecciona fotos del libro y no supera el máximo de N fotos por anuncio,
- **Entonces** el sistema guarda las fotos y las muestra en el detalle del anuncio.

**Criterio 2**
- **Dado** que el vendedor ya alcanzó el máximo de N fotos por anuncio,
- **Cuando** intenta subir otra foto,
- **Entonces** el sistema rechaza la foto y muestra un mensaje indicando el límite permitido.

#### Administración

##### RF-041 - Activar o desactivar el Marketplace

**Criterio 1**
- **Dado** un administrador autenticado en la configuración del sitio,
- **Cuando** activa el Marketplace,
- **Entonces** el módulo queda disponible y aparece en la navegación para los usuarios.

**Criterio 2**
- **Dado** un administrador autenticado en la configuración del sitio,
- **Cuando** desactiva el Marketplace,
- **Entonces** el módulo deja de aparecer en la navegación para los usuarios.

##### RF-042 - Configurar los límites del muro

**Criterio 1**
- **Dado** un administrador autenticado en el panel de Administración,
- **Cuando** configura la longitud máxima permitida para una publicación,
- **Entonces** las publicaciones que superen ese límite no pueden guardarse y el sistema muestra un mensaje de error.

**Criterio 2**
- **Dado** un administrador autenticado en el panel de Administración,
- **Cuando** configura el número máximo de imágenes por publicación y el número de publicaciones mostradas por página,
- **Entonces** el muro respeta esos límites al crear publicaciones y al paginar el feed de cada materia.

## Requerimientos No Funcionales

| ID | Descripción | Categoría | Estado |
|---|---|---|---|
| RNF-001 | El plugin debe instalarse y funcionar sin errores en Moodle 4.5 LTS (rama MOODLE_405_STABLE). Verificación: ejecutar la suite PHPUnit y moodle-plugin-ci sobre Moodle 4.5 en el CI, que debe pasar sin fallos. | Compatibilidad | Pendiente |
| RNF-002 | El plugin debe instalarse y ejecutarse sin errores en PHP 8.1, 8.2 y 8.3. Verificación: ejecutar la suite PHPUnit y moodle-plugin-ci sobre las tres versiones de PHP en el CI, que debe pasar sin fallos. | Compatibilidad | Pendiente |
| RNF-003 | El plugin debe funcionar sin errores con MariaDB, MySQL y PostgreSQL compatibles con Moodle 4.5 LTS. Verificación: ejecutar la suite PHPUnit y moodle-plugin-ci usando cada motor de base de datos y comprobar que todas las pruebas finalicen sin fallos. | Compatibilidad | Pendiente |
| RNF-004 | El muro debe cargar y mostrar 50 publicaciones en menos de 2 segundos. Verificación: medir el tiempo de carga en una prueba con 50 publicaciones y comprobar que sea menor a 2 segundos. | Rendimiento | Pendiente |
| RNF-005 | Exigir sesskey y comprobar capacidades en toda acción que modifica datos. El 100% de las peticiones (POST/AJAX) deben validar el token de sesión (`sesskey`) y verificar permisos (`require_capability`). Verificación: mediante análisis de código estático y revisión manual en cada Pull Request. | Seguridad | Pendiente |
| RNF-006 | El plugin debe implementar la Privacy API de Moodle 4.5 LTS para gestionar los datos personales de los usuarios, declarando los metadatos, exportando y eliminando datos conforme al RGPD. Verificación: ejecutar la validación de la Privacy API en Moodle 4.5 y aprobar los tests `privacy` de moodle-plugin-ci. | Privacidad | Pendiente |
| RNF-007 | Toda la interfaz del plugin debe estar disponible en inglés (`lang/en`) y en español (`lang/es`), sin claves de texto faltantes en ninguno de los dos idiomas. Verificación: comparar las claves de `lang/en/` y `lang/es/` y comprobar que ambos tengan exactamente las mismas claves (0 faltantes) y que moodle-plugin-ci finalice sin errores. | Internacionalización | Pendiente |
| RNF-008 | Cumplir con el estándar de accesibilidad WCAG 2.1 nivel AA en todas las interfaces de usuario. Verificación: ejecutar la herramienta de análisis axe-core o WAVE y alcanzar 0 errores críticos y mayores. | Accesibilidad | Pendiente |
| RNF-009 | La interfaz de usuario debe adaptarse y ser completamente funcional en dispositivos móviles (smartphones y tablets). Verificación: Alcanzar un puntaje de al menos 90/100 en Google Lighthouse (Mobile) y no tener desbordamiento horizontal en pantallas desde 320px. | Usabilidad | Pendiente |
| RNF-010 | La interfaz del plugin debe funcionar correctamente en las dos últimas versiones estables de Chrome, Firefox, Edge y Safari. Verificación: ejecutar los casos de prueba manuales de interfaz en las 8 combinaciones de navegador y versión y comprobar que todos finalicen sin fallos. | Compatibilidad | Pendiente |
| RNF-011 | El código PHP del plugin debe cumplir el estándar de codificación de Moodle sin errores ni warnings de PHPCS. Verificación: ejecutar PHPCS con el estándar Moodle sobre el plugin y comprobar 0 errores y 0 warnings. | Mantenibilidad | Pendiente |
| RNF-012 | Las clases del plugin en `classes/` deben alcanzar al menos 70 % de cobertura de líneas con PHPUnit. Verificación: ejecutar PHPUnit con reporte de cobertura y comprobar que la cobertura de `classes/` sea igual o superior al 70 %. | Mantenibilidad | Pendiente |
| RNF-013 | Las acciones importantes del sistema deben registrarse mediante la Events API, incluyendo al menos la identificación de la acción, el usuario y la fecha y hora. Verificación: revisar los eventos registrados mediante la Events API y comprobar que cada acción importante contiene estos datos. | Auditoría | Pendiente |

## Requerimientos de Sistema

| ID | Descripción |
|---|---|
| RS-001 | El cron de Moodle debe estar activo y ejecutarse periódicamente para procesar las notificaciones y tareas programadas de ChuspaSocial. Si no se cumple, estas funciones pueden retrasarse o no ejecutarse. |
| RS-002 | La mensajería de Moodle debe estar habilitada mediante el ajuste `messaging`. Si no se cumple, el contacto con vendedores del Marketplace y las notificaciones de ChuspaSocial pueden no estar disponibles o funcionar incorrectamente. |
| RS-003 | La funcionalidad de tags de Moodle debe estar habilitada mediante el ajuste `usetags`. Si no se cumple, las funciones de etiquetado y filtrado por tags de ChuspaSocial pueden no estar disponibles o funcionar incorrectamente. |
