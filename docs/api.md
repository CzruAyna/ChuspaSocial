# Documentación de API

## Introducción

ChuspaSocial expone sus funciones mediante el sistema de **servicios web de Moodle** (External Functions API), no mediante endpoints REST tradicionales. Cada función externa se registra en Moodle y puede invocarse de dos formas:

### 1. Desde el propio Moodle (AJAX)

Cuando el usuario ya tiene una sesión iniciada en Moodle, el frontend del plugin llama a las funciones externas mediante el módulo `core/ajax`, sin necesidad de token ni URL pública. Ejemplo desde JavaScript (AMD):

```javascript
import Ajax from 'core/ajax';

const request = {
    methodname: 'local_chuspasocial_toggle_follow',
    args: {
        component: 'user',
        itemid: 123
    }
};

Ajax.call([request])[0].then((response) => {
    console.log(response);
});
```

### 2. Desde fuera de Moodle (REST con token)

Para integraciones externas (apps móviles, scripts, otros sistemas), las funciones se llaman vía REST usando un **token de servicio web** generado para el usuario. El formato general es:

https://<tu-sitio-moodle>/webservice/rest/server.php
?wstoken=<TOKEN>
&wsfunction=<nombre_de_la_funcion>
&moodlewsrestformat=json
&<parametros_de_la_funcion>

Por ejemplo, para llamar a `local_chuspasocial_toggle_follow`:

https://<tu-sitio-moodle>/webservice/rest/server.php
?wstoken=<TOKEN>
&wsfunction=local_chuspasocial_toggle_follow
&moodlewsrestformat=json
&component=user
&itemid=123

El token se genera y gestiona desde **Administración del sitio → Servidor → Servicios web → Gestionar tokens**, y requiere que el usuario tenga las capacidades correspondientes habilitadas.

## Funciones Externas

### `local_chuspasocial_toggle_follow`

Permite seguir o dejar de seguir a un usuario o etiqueta (*tag*) sin necesidad de recargar la página.

**Descripción:**  
Alterna el estado de seguimiento (*follow/unfollow*) para un usuario o etiqueta especificados dentro de la plataforma.

**Parámetros:**  

* `component` (string): Tipo de objetivo a seguir. Valores permitidos: `'user'` o `'tag'`.
* `itemid` (int): ID del usuario o del tag al que se desea seguir o dejar de seguir.

**Capacidades requeridas:**  

* `local/chuspasocial:follow`

**Respuesta de ejemplo (JSON):**  

```json
{
  "status": true,
  "action": "followed",
  "message": "Ahora sigues a este usuario."
}
```

### `local_chuspasocial_get_listings`

Permite consultar los libros publicados en el marketplace: los de las materias del usuario o los que coinciden con una búsqueda.

**Descripción:**  
Si no se envía texto de búsqueda, devuelve los anuncios disponibles vinculados a las materias en las que está inscrito el usuario. Si se envía texto, devuelve los anuncios cuyo título o autor coinciden con él. Los resultados se devuelven paginados.

**Parámetros:**  

* `search` (string, opcional): Texto a buscar en el título o el autor del libro. Por defecto `''`; si está vacío, se devuelven los libros de mis materias.
* `page` (int, opcional): Número de página, empezando en `0`. Por defecto `0`.
* `perpage` (int, opcional): Cantidad de anuncios por página, entre `1` y `50`. Por defecto `20`.

**Capacidades requeridas:**  

* `local/chuspasocial:view`

**Respuesta de ejemplo (JSON):**  

```json
{
  "listings": [
    {
      "id": 2,
      "userid": 2,
      "title": "Fisica",
      "author": "Serway",
      "description": "<p>Buen estado</p>",
      "descriptionformat": 1,
      "price": 120.5,
      "externalcontact": "70000000",
      "status": "available",
      "timecreated": 1760000100
    }
  ]
}
```

Campos de cada anuncio: `id` (id del anuncio), `userid` (id del vendedor), `title`, `author`, `description` (descripción ya formateada), `descriptionformat`, `price`, `externalcontact` (contacto externo; vacío si no se indicó), `status` (`available` o `sold`) y `timecreated` (marca de tiempo Unix).

**Errores:**  

* `invalid_parameter_exception`: `page` es negativo o `perpage` está fuera del rango de 1 a 50.
* `required_capability_exception`: el usuario no tiene la capacidad `local/chuspasocial:view`.
* `require_login`: el usuario no tiene sesión iniciada.

### `local_chuspasocial_get_posts`

Obtiene una página de publicaciones del muro general de ChuspaSocial o del muro de una materia. Devuelve los datos de las publicaciones, la información de sus autores, las imágenes y etiquetas asociadas, y el estado de las reacciones del usuario actual.

**Descripción:**

La función permite consultar las publicaciones de forma paginada. Si `courseid` es `0`, consulta el muro general; si es mayor que `0`, consulta el muro del curso indicado. Las publicaciones ocultas se excluyen de los resultados del muro general.

**Parámetros:**

* `courseid` (int, opcional): ID del curso cuyas publicaciones se desean consultar. El valor `0` indica el muro general. Por defecto, `0`.
* `page` (int, opcional): Número de página que se desea obtener, comenzando desde `0`. Por defecto, `0`. No puede ser negativo.
* `perpage` (int, opcional): Cantidad de publicaciones por página. Por defecto, `20`. Debe estar entre `1` y `50`, ambos inclusive.

**Capacidades requeridas:**

* `local/chuspasocial:view`: se comprueba en el contexto del sistema cuando se consulta el muro general y en el contexto del curso cuando se consulta el muro de una materia.

**Respuesta de ejemplo (JSON):**

```json
{
  "posts": [
    {
      "id": 15,
      "userid": 3,
      "content": "Bienvenidos al curso de Física.",
      "contentformat": 1,
      "timecreated": 1760000100,
      "authorfullname": "Usuario de prueba",
      "authorpictureurl": "https://moodle.example/user/pix.php/3/f1.jpg",
      "canedit": true,
      "candelete": true,
      "images": [
        {
          "url": "https://moodle.example/pluginfile.php/1/local_chuspasocial/postimage/15/experimento.png",
          "filename": "experimento.png"
        }
      ],
      "tags": [
        {
          "name": "Fisica",
          "url": "https://moodle.example/local/chuspasocial/tag.php?tag=Fisica"
        }
      ],
      "reactions": 4,
      "hasreacted": true
    }
  ]
}
```

Los valores del ejemplo son ilustrativos. Las URL dependen de la instalación de Moodle y de los archivos existentes.

**Campos de cada publicación:**

* `id`: identificador de la publicación.
* `userid`: identificador de su autor.
* `content`: contenido de la publicación, procesado mediante el formato de texto de Moodle.
* `contentformat`: formato del contenido.
* `timecreated`: fecha de creación en formato de marca de tiempo Unix.
* `authorfullname`: nombre completo del autor.
* `authorpictureurl`: URL de la imagen de perfil del autor.
* `canedit`: indica si el usuario actual puede editar la publicación.
* `candelete`: indica si el usuario actual puede eliminarla.
* `images`: arreglo de imágenes asociadas; cada elemento contiene `url` y `filename`.
* `tags`: arreglo de etiquetas; cada elemento contiene `name` y `url`.
* `reactions`: número total de reacciones de la publicación.
* `hasreacted`: indica si el usuario actual ya reaccionó a la publicación.

Si no hay publicaciones disponibles para la página solicitada, la respuesta contiene `"posts": []`.

**Errores:**

* `invalid_parameter_exception`: el parámetro `page` es negativo, o `perpage` es menor que `1` o mayor que `50`.
* `required_capability_exception`: el usuario no dispone de la capacidad `local/chuspasocial:view` en el contexto correspondiente.
* `require_login` u otros errores de contexto de Moodle: pueden producirse si la sesión o el acceso al contexto requerido no son válidos.

### `local_chuspasocial_toggle_reaction`

Permite reaccionar a una publicación o quitar la reacción sin necesidad de recargar la página.

**Descripción:**  
Alterna la reacción del usuario actual sobre una publicación (*post*): si todavía no reaccionó, agrega la reacción; si ya había reaccionado, la elimina.

**Parámetros:**  

* `postid` (int): ID de la publicación sobre la que se desea reaccionar o quitar la reacción.

**Capacidades requeridas:**  

* `local/chuspasocial:react` (en el contexto del curso de la publicación, o en el contexto del sistema si la publicación no pertenece a un curso).

**Respuesta de ejemplo (JSON):**  

```json
{
  "reacted": true
}
```

`reacted` indica si la publicación quedó con la reacción del usuario: `true` si se agregó, `false` si se quitó.

**Errores:**  

* `invalid_parameter_exception`: `postid` no es un entero válido.
* `dml_missing_record_exception`: no existe una publicación con ese `postid`.
* `required_capability_exception`: el usuario no tiene la capacidad `local/chuspasocial:react`.
* `require_login`: el usuario no tiene sesión iniciada.

### `local_chuspasocial_add_comment`

Permite comentar una publicación sin necesidad de recargar la página.

**Descripción:**  
Agrega un comentario del usuario actual a una publicación (*post*) y devuelve el comentario creado.

**Parámetros:**  

* `postid` (int): ID de la publicación que se desea comentar.
* `content` (string): Contenido del comentario. Se recibe como `PARAM_RAW_TRIMMED`, es decir, se conserva el texto tal cual pero se eliminan los espacios al inicio y al final.

**Capacidades requeridas:**  

* `local/chuspasocial:comment` (en el contexto del curso de la publicación, o en el contexto del sistema si la publicación no pertenece a un curso).

**Respuesta de ejemplo (JSON):**  

```json
{
  "id": 14,
  "postid": 7,
  "userid": 3,
  "content": "Muy buena publicacion.",
  "contentformat": 2,
  "timecreated": 1760000500
}
```

Campos devueltos: `id` (id del comentario creado), `postid` (id de la publicación comentada), `userid` (id del autor del comentario), `content` (contenido del comentario), `contentformat` (formato del contenido; `FORMAT_PLAIN` si no se especifica) y `timecreated` (marca de tiempo Unix de creación).

**Errores:**  

* `invalid_parameter_exception`: `postid` no es un entero válido o `content` no cumple la validación.
* `dml_missing_record_exception`: no existe una publicación con ese `postid`.
* `required_capability_exception`: el usuario no tiene la capacidad `local/chuspasocial:comment`.
* `require_login`: el usuario no tiene sesión iniciada.

## Errores

Las funciones externas de Moodle no devuelven códigos HTTP tradicionales para errores de lógica de negocio. En su lugar, lanzan excepciones de tipo `moodle_exception` (o subclases como `invalid_parameter_exception`, `required_capability_exception`), que el cliente recibe como un objeto JSON con esta forma:

```json
{
  "exception": "moodle_exception",
  "errorcode": "nopermissions",
  "message": "No tienes permisos para realizar esta acción."
}
```

Casos comunes:

* **`invalid_parameter_exception`**: un parámetro no cumple la validación definida en `execute_parameters()`.
* **`required_capability_exception`** / **`nopermissions`**: el usuario no tiene la capacidad requerida.
* **`require_login`**: el usuario no tiene sesión iniciada.
