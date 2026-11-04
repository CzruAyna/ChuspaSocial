# Modelo de datos de ChuspaSocial

## Introducción

El presente documento describe de manera general el modelo de datos del sistema ChuspaSocial.

Su propósito es organizar y documentar las tablas que formarán parte de la base de datos antes de iniciar su implementación.

Esta documentación permitirá mantener una estructura clara, facilitar el desarrollo del sistema y establecer criterios comunes para la definición de las tablas.

## Tablas del modelo de datos

El modelo de datos contempla un total de siete tablas:

1. `local_chuspasocial_post`
2. `local_chuspasocial_comment`
3. `local_chuspasocial_reaction`
4. `local_chuspasocial_follow`
5. `local_chuspasocial_report`
6. `local_chuspasocial_listing`
7. `local_chuspasocial_lstcourse`

## Regla de nomenclatura de Moodle

Para mantener la compatibilidad con las restricciones de Moodle, los nombres de las tablas de la base de datos deben tener un máximo de 28 caracteres.

Esta regla debe respetarse al definir los nombres de las siete tablas del modelo de datos.

## Tabla `local_chuspasocial_post`

Almacena las publicaciones del muro general y de los cursos en ChuspaSocial.

| Campo | Tipo XMLDB | Nulo | Descripción |
|---|---|---|---|
| `id` | INT(10), secuencia | No | Clave primaria autoincremental de la publicación. |
| `userid` | INT(10) | No | ID del usuario que crea la publicación (referencia a `user.id`). |
| `courseid` | INT(10) | No | ID del curso asociado. El valor `0` representa el muro general. |
| `type` | CHAR(20) | No | Tipo de publicación. Valores permitidos: `post`, `announcement`, `activity`. |
| `content` | TEXT | No | Contenido de la publicación. |
| `contentformat` | INT(2) | No | Formato del contenido según las constantes `FORMAT_*` de Moodle. |
| `hidden` | INT(1) | No | Indica si la publicación está oculta: `0` visible, `1` oculta. |
| `timecreated` | INT(10) | No | Marca de tiempo Unix de creación. |
| `timemodified` | INT(10) | No | Marca de tiempo Unix de la última modificación. |

### Valores permitidos de `type`

XMLDB no define un tipo enum para este campo, por lo que `type` se almacena como `CHAR` y sus valores se validan en la aplicación.

| Valor | Significado |
|---|---|
| `post` | Publicación normal de un usuario. |
| `announcement` | Anuncio publicado en el muro. |
| `activity` | Publicación asociada a una actividad. |

## Tabla `local_chuspasocial_follow`

Almacena los elementos que sigue cada usuario: otros usuarios y etiquetas (tags). Cada fila representa un seguimiento.

| Campo         | Tipo XMLDB | Nulo | Descripción                                                                 |
|---------------|------------|------|-----------------------------------------------------------------------------|
| `id`          | INT(10)    | No   | Clave primaria autoincremental.                                             |
| `userid`      | INT(10)    | No   | Id del usuario que sigue (referencia a `user.id`).                          |
| `itemtype`    | CHAR(10)   | No   | Tipo de elemento seguido. Valores permitidos: `user`, `tag`.                |
| `itemid`      | INT(10)    | No   | Id del elemento seguido: un `user.id` o un `tag.id`, según `itemtype`.      |
| `timecreated` | INT(10)    | No   | Marca de tiempo Unix del momento en que se creó el seguimiento.             |

### Valores permitidos de `itemtype`

XMLDB no tiene un tipo enum, por lo que `itemtype` se define como `CHAR` y sus valores se validan en la aplicación.

| Valor  | Significado                      | `itemid` apunta a |
|--------|----------------------------------|-------------------|
| `user` | El usuario sigue a otro usuario. | `user.id`         |
| `tag`  | El usuario sigue una etiqueta.   | `tag.id`          |
## Tabla local_chuspasocial_comment

La tabla local_chuspasocial_comment almacena los comentarios realizados por los usuarios en las publicaciones de ChuspaSocial.

### Estructura de la tabla

| Campo | Tipo XMLDB | Nulo | Descripción |
|---|---|---|---|
| id | int(10) | No | Identificador único del comentario. |
| postid | int(10) | No | Identificador de la publicación a la que pertenece el comentario. |
| userid | int(10) | No | Identificador del usuario que realiza el comentario. |
| content | text | No | Contenido del comentario realizado por el usuario. |
| contentformat | int(2) | No | Formato utilizado para almacenar el contenido del comentario. |
| timecreated | int(10) | No | Fecha y hora de creación del comentario en formato Unix. |
| timemodified | int(10) | No | Fecha y hora de la última modificación del comentario en formato Unix. |

### Relaciones con otras tablas

La tabla `local_chuspasocial_comment` se relaciona con otras tablas del sistema mediante los siguientes campos:

- **postid:** establece una relación con la tabla `local_chuspasocial_post`, identificando la publicación a la que pertenece cada comentario.

- **userid:** establece una relación con la tabla estándar `user` de Moodle, identificando al usuario que realizó el comentario.

### Descripción de las relaciones

Una publicación registrada en `local_chuspasocial_post` puede tener varios comentarios asociados mediante el campo `postid`.

Asimismo, un usuario registrado en la tabla `user` de Moodle puede realizar varios comentarios, los cuales se relacionan mediante el campo `userid`.

Estas relaciones permiten organizar los comentarios, identificar a sus autores y determinar a qué publicación pertenece cada uno.
## Tabla `local_chuspasocial_report` (reportes de contenido)

Almacena los reportes que los usuarios hacen sobre publicaciones, comentarios y anuncios.

| Campo | Tipo XMLDB | Nulo | Descripción |
|---|---|---|---|
| `id` | INT(10), secuencia | NO | Clave primaria autoincremental del reporte. |
| `itemtype` | CHAR(20) | NO | Tipo de contenido reportado. Valores permitidos: `post`, `comment`, `listing`. |
| `itemid` | INT(10) | NO | ID del contenido reportado (se interpreta según `itemtype`). |
| `reporterid` | INT(10) | NO | ID del usuario que realiza el reporte (referencia a `user.id`). |
| `reason` | TEXT | NO | Motivo del reporte. |
| `status` | CHAR(20) | NO | Estado del reporte. Valores permitidos: `open`, `resolved`, `dismissed`. Por defecto: `open`. |
| `resolvedby` | INT(10) | SÍ | ID del usuario que resolvió el reporte (referencia a `user.id`). Nulo mientras no se resuelva. |
| `timecreated` | INT(10) | NO | Marca de tiempo Unix de creación. |
| `timeresolved` | INT(10) | SÍ | Marca de tiempo Unix de la resolución. Nulo mientras no se resuelva. |

### Valores permitidos de `itemtype`

| Valor | Significado |
|---|---|
| `post` | El reporte es sobre una publicación. |
| `comment` | El reporte es sobre un comentario. |
| `listing` | El reporte es sobre un anuncio de libro. |
## Tabla `local_chuspasocial_listing` (anuncios de libros)

Almacena los anuncios de libros publicados por los usuarios.

| Campo | Tipo XMLDB | Nulo | Descripción |
|---|---|---|---|
| `id` | INT(10), secuencia | NO | Clave primaria autoincremental del anuncio. |
| `userid` | INT(10) | NO | ID del usuario que publica el anuncio (referencia a `user.id`). |
| `title` | CHAR(255) | NO | Título del libro. |
| `author` | CHAR(255) | NO | Autor del libro. |
| `description` | TEXT | SÍ | Descripción del anuncio. |
| `descriptionformat` | INT(4) | NO | Formato de la descripción (constantes `FORMAT_*` de Moodle; por defecto `FORMAT_HTML` = 1). |
| `price` | NUMBER(10,2) | NO | Precio del libro. |
| `externalcontact` | CHAR(255) | SÍ | Contacto externo del vendedor (teléfono, correo u otro medio). |
| `status` | CHAR(20) | NO | Estado del anuncio. Valores permitidos: `available` (disponible), `sold` (vendido). Por defecto: `available`. |
| `timecreated` | INT(10) | NO | Marca de tiempo Unix de creación. |
| `timemodified` | INT(10) | NO | Marca de tiempo Unix de la última modificación. |

### Valores permitidos de `status`

| Valor | Significado |
|---|---|
| `open` | El reporte está pendiente de revisión. |
| `resolved` | El reporte fue atendido y se tomó una acción. |
| `dismissed` | El reporte fue revisado y descartado sin acción. |
| `available` | El libro está disponible para la venta. |
| `sold` | El libro ya fue vendido. |

## Diagrama de Tablas de la base de datos (Mermaid)

```mermaid
erDiagram
    user {
        int id PK
    }

    course {
        int id PK
    }

    local_chuspasocial_post {
        int id PK
        int userid FK "Autor de la publicacion"
        int courseid FK "Curso asociado, 0 es muro general"
        string type "Tipo de publicacion"
        text content "Contenido"
        int contentformat "Formato del contenido"
        int hidden "Publicacion oculta"
        int timecreated "Creacion"
        int timemodified "Modificacion"
    }

    local_chuspasocial_comment {
        int id PK
        int postid FK "Publicacion comentada"
        int userid FK "Autor del comentario"
        text content "Contenido"
        int contentformat "Formato del contenido"
        int timecreated "Creacion"
        int timemodified "Modificacion"
    }

    local_chuspasocial_reaction {
        int id PK
        int postid FK "Publicacion reaccionada"
        int userid FK "Usuario que reacciona"
        string type "Tipo de reaccion, like por defecto"
        int timecreated "Creacion"
    }

    local_chuspasocial_follow {
        int id PK
        int userid FK "Usuario que sigue"
        string itemtype "user o tag"
        int itemid "Id del usuario o etiqueta seguida"
        int timecreated "Creacion"
    }

    local_chuspasocial_listing {
        int id PK
        int userid FK "Vendedor"
        string title "Titulo del libro"
        string author "Autor del libro"
        text description "Descripcion"
        int descriptionformat "Formato de la descripcion"
        decimal price "Precio"
        string externalcontact "Contacto externo"
        string status "available o sold"
        int timecreated "Creacion"
        int timemodified "Modificacion"
    }

    local_chuspasocial_lstcourse {
        int id PK
        int listingid FK "Anuncio de libro"
        int courseid FK "Materia"
    }

    local_chuspasocial_report {
        int id PK
        string itemtype "Tipo de contenido reportado"
        int itemid "Id del contenido reportado"
        int reporterid FK "Usuario que reporta"
        text reason "Motivo"
        string status "Estado, open por defecto"
        int resolvedby FK "Moderador que resuelve"
        int timecreated "Creacion"
        int timeresolved "Resolucion"
    }

    user ||--o{ local_chuspasocial_post : "publica"
    course |o--o{ local_chuspasocial_post : "contiene"

    local_chuspasocial_post ||--o{ local_chuspasocial_comment : "tiene"
    user ||--o{ local_chuspasocial_comment : "comenta"

    local_chuspasocial_post ||--o{ local_chuspasocial_reaction : "recibe"
    user ||--o{ local_chuspasocial_reaction : "reacciona"

    user ||--o{ local_chuspasocial_follow : "sigue"

    user ||--o{ local_chuspasocial_listing : "vende"
    local_chuspasocial_listing ||--o{ local_chuspasocial_lstcourse : "se vincula"
    course ||--o{ local_chuspasocial_lstcourse : "tiene libros"

    user ||--o{ local_chuspasocial_report : "reporta"
    user |o--o{ local_chuspasocial_report : "resuelve"
```

## Tabla `local_chuspasocial_reaction` (reacciones)

Almacena las reacciones de tipo «me gusta» que los usuarios realizan sobre las publicaciones. Actualmente el plugin soporta una única reacción (`like`); la columna `type` está normalizada como `CHAR` para admitir tipos adicionales en el futuro.

| Campo | Tipo XMLDB | Nulo | Descripción |
|---|---|---|---|
| `id` | INT(10), secuencia | NO | Clave primaria autoincremental de la reacción. |
| `postid` | INT(10) | NO | ID de la publicación sobre la que se reacciona (referencia a `local_chuspasocial_post.id`). Por defecto: `0`. |
| `userid` | INT(10) | NO | ID del usuario que reacciona (referencia a `user.id`). Por defecto: `0`. |
| `type` | CHAR(20) | NO | Tipo de reacción. Valor permitido: `like`. Por defecto: `like`. |
| `timecreated` | INT(10) | NO | Marca de tiempo Unix de creación de la reacción. Por defecto: `0`. |

### Valores permitidos de `type`

XMLDB no tiene un tipo enum, por lo que `type` se define como `CHAR` y sus valores se validan en la aplicación.

| Valor | Significado |
|---|---|
| `like` | El usuario le da «me gusta» a la publicación. |

### Relaciones con otras tablas

- **postid:** relación con `local_chuspasocial_post`; una publicación puede recibir varias reacciones.
- **userid:** relación con `user`; un usuario puede reaccionar a varias publicaciones.

## Tabla `local_chuspasocial_lstcourse` (vínculos anuncio ↔ materia)

Almacena los vínculos entre los anuncios de libros y las materias (cursos) en las que se ofrecen. Cada fila relaciona un anuncio con una materia.

| Campo | Tipo XMLDB | Nulo | Descripción |
|---|---|---|---|
| `id` | INT(10), secuencia | NO | Clave primaria autoincremental del vínculo. |
| `listingid` | INT(10) | NO | ID del anuncio de libro (referencia a `local_chuspasocial_listing.id`). |
| `courseid` | INT(10) | NO | ID de la materia (referencia a `course.id`). |

### Relaciones con otras tablas

- **listingid:** relación con `local_chuspasocial_listing`; un anuncio puede estar vinculado a varias materias.
- **courseid:** relación con `course`; una materia puede tener varios anuncios vinculados.

La tabla resuelve la relación de muchos a muchos entre anuncios y materias: permite consultar los libros de las materias en las que está inscrito un usuario.
