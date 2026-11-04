# Modelo de amenazas

Análisis de qué puede salir mal en el plugin `local_chuspasocial` (Moodle 4.5 LTS)
y cómo se mitiga. Las amenazas se clasifican con el modelo STRIDE y cada
mitigación indica si ya está implementada en `src/` o si es parcial.

## Activos

| ID | Activo | Por qué importa |
|---|---|---|
| A-01 | Cuentas y sesiones de usuarios | Permiten actuar en nombre de estudiantes, docentes y administradores. |
| A-02 | Publicaciones, comentarios y anuncios | Contenido visible para otros usuarios; puede llevar código malicioso o información falsa. |
| A-03 | Contenido de las materias (muro, tareas, anuncios oficiales) | Solo debe verlo quien está matriculado en la materia. |
| A-04 | Datos personales (nombre, foto, seguidores, seguidos) | Información privada de los usuarios. |
| A-05 | Anuncios del marketplace | Ofertas entre usuarios; pueden usarse para estafas. |

## Actores

| ID | Actor | Descripción |
|---|---|---|
| AC-01 | Estudiante malicioso | Usuario autenticado que intenta ver o modificar lo que no le corresponde. |
| AC-02 | Atacante externo | Sin cuenta; intenta que un usuario autenticado ejecute acciones sin saberlo. |
| AC-03 | Spammer o bot | Cuenta que publica contenido masivo o repetitivo. |
| AC-04 | Estafador en el marketplace | Usuario que publica ofertas falsas o intenta cobrar por fuera. |

## Categorías STRIDE

| Categoría | Qué cubre | Amenazas |
|---|---|---|
| Spoofing (S) | Suplantar la identidad de un usuario o de un vendedor. | T-02, T-05 |
| Tampering (T) | Modificar datos o contenido que otro usuario va a ver o ejecutar. | T-01 |
| Repudiation (R) | Negar una acción realizada sin que quede un registro que la atribuya. | T-06 |
| Information Disclosure (I) | Ver información de materias o de usuarios sin autorización. | T-03 |
| Denial of Service (DoS) | Saturar el muro o los recursos con contenido masivo o repetitivo. | T-04 |
| Elevation of Privilege (E) | Realizar acciones reservadas a docentes, moderadores o al autor del contenido. | T-07 |

Las categorías no son excluyentes: T-01 también puede exponer información,
T-02 también permite modificar datos, T-03 también es una elevación de
privilegios y T-05 también dificulta atribuir la autoría. Cada amenaza se
detalla bajo su categoría principal.

## Amenazas y mitigaciones

### Spoofing (S)

| ID | Amenaza | Actor | Activo | Mitigación implementada | Estado |
|---|---|---|---|---|---|
| T-02 | CSRF: un sitio externo hace que el usuario publique, borre, siga o publique en el marketplace sin querer. | AC-02 | A-01, A-02, A-05 | Los formularios extienden `moodleform` (`src/classes/form/post_form.php` y `listing_form.php`), que agrega el campo `sesskey` y lo comprueba al enviar. `src/listing.php` llama a `require_sesskey()` antes de marcar un anuncio como vendido. Los servicios web de escritura de `src/db/services.php` se declaran con `ajax => true`, así que Moodle exige la sesskey; además, cada función externa valida el contexto y la capacidad. | Implementada |
| T-05 | Fraude en el marketplace: ofertas falsas, cobro adelantado o suplantación del vendedor. | AC-04 | A-05 | El anuncio se asocia siempre a `$USER->id` y el campo `userid` no se puede sobrescribir (`listing_service::create_listing()`); editar o marcar como vendido exige ser el autor (`update_listing()`, `mark_sold()` y `src/listing.php`). Los reportes guardan `reporterid` y `timecreated`, no admiten duplicados y notifican a los moderadores (`report_service::create_report()`); `resolve_and_hide()` exige `local/chuspasocial:moderate`. Pendiente: aviso visible de que el plugin no gestiona pagos y advertencia de contacto externo; la cadena `externalcontactwarning` existe en los archivos de idioma, pero no se muestra en ninguna plantilla. | Parcial |

### Tampering (T)

| ID | Amenaza | Actor | Activo | Mitigación implementada | Estado |
|---|---|---|---|---|---|
| T-01 | XSS: una publicación, comentario, anuncio o parámetro de la URL lleva HTML o JavaScript que se ejecuta en el navegador de otro usuario. | AC-01, AC-02, AC-03 | A-01, A-02 | La entrada se valida con `required_param`/`optional_param` y tipos `PARAM_*` en las páginas, con `validate_parameters()` en las funciones externas y con `setType()` en los formularios. La salida se escapa con `format_text()` (`src/index.php`, `src/profile.php`), `format_string()` (`src/listing.php`), `s()` (`src/tag.php`, `src/profile.php`) y las etiquetas dobles de Mustache. Las plantillas usan `{{{content}}}` solo para contenido que debe llegar ya formateado (`src/templates/post.mustache` y `comments.mustache`). | Implementada |

### Repudiation (R)

| ID | Amenaza | Actor | Activo | Mitigación implementada | Estado |
|---|---|---|---|---|---|
| T-06 | Un usuario niega haber publicado, comentado, reaccionado, reportado o moderado contenido. | AC-01, AC-04 | A-02, A-05 | Las tablas de publicaciones, comentarios, reacciones, seguidores, anuncios y reportes guardan `userid` (o `reporterid`) y `timecreated` (`src/db/install.xml`), y los reportes guardan `resolvedby` y `timeresolved` al moderarse. La Events API registra la eliminación de publicaciones (`post_deleted` en `src/classes/local/post_service.php`) y la creación de comentarios (`comment_created` en `src/classes/local/comment_service.php`). Quedan definidas pero todavía no se disparan las clases `post_created`, `listing_created` y `reaction_added`. | Parcial |

### Information Disclosure (I)

| ID | Amenaza | Actor | Activo | Mitigación implementada | Estado |
|---|---|---|---|---|---|
| T-03 | Acceso a materias ajenas: un usuario ve el muro, las tareas o los anuncios de una materia en la que no está matriculado cambiando el id en la URL. | AC-01 | A-03, A-04 | Las páginas llaman a `require_login()` y las de muro, perfil y marketplace exigen `local/chuspasocial:view` o `viewmarketplace` (`src/index.php`, `profile.php`, `listing.php`, `listing_edit.php`); `tag.php` exige sesión. Las funciones externas validan el contexto y la capacidad con `validate_context()` y `require_capability()`. `post_service::can_view()` exige inscripción (o `moodle/course:view`) para las publicaciones de una materia, la capacidad del muro general para `courseid = 0`, y permite las publicaciones ocultas solo al autor o a un moderador. `src/lib.php` aplica `require_login()` y `can_view()` antes de servir una imagen, y `src/profile.php` filtra las publicaciones por las materias del visitante. | Implementada |

### Denial of Service (DoS)

| ID | Amenaza | Actor | Activo | Mitigación implementada | Estado |
|---|---|---|---|---|---|
| T-04 | Spam: publicaciones o comentarios masivos que saturan el muro. | AC-03 | A-02 | Se limita la cantidad de imágenes por publicación (`maxpostimages` en `src/settings.php`, usado en `src/classes/form/post_form.php` y `post_service::save_post_images()`) y a 3 fotos por anuncio (`src/classes/form/listing_form.php`). Las publicaciones ocultas por moderación dejan de mostrarse (`report_service::resolve_and_hide()` y `post_service::can_view()`). No hay límite de publicaciones por intervalo, ni límite de longitud aplicado, ni CAPTCHA: la validación de `maxpostlength` y el RF-042 siguen pendientes. | Parcial |

### Elevation of Privilege (E)

| ID | Amenaza | Actor | Activo | Mitigación implementada | Estado |
|---|---|---|---|---|---|
| T-07 | Un estudiante realiza acciones de moderación o edita contenido ajeno (ocultar publicaciones, resolver reportes, modificar o marcar como vendido un anuncio de otro). | AC-01 | A-01, A-02, A-05 | Las capacidades están definidas en `src/db/access.php` con su contexto y, en las de escritura, `riskbitmask`; se exigen con `require_capability()` en páginas, servicios y funciones externas (`local/chuspasocial:view`, `post`, `comment`, `react`, `report`, `createlisting`, `moderate`, entre otras). `report_service::resolve_and_hide()` exige `local/chuspasocial:moderate`; `listing_service::update_listing()` y `mark_sold()` comprueban que el anuncio sea del usuario; `post_service::create_post()` exige la capacidad y la inscripción cuando la publicación es de una materia, y `delete_post()` solo permite borrar al autor o a un moderador. | Implementada |
