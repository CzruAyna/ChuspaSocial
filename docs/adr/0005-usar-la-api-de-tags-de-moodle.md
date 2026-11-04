# ADR-005: Usar la API de tags de Moodle

## Estado

Aceptado.

## Contexto

ChuspaSocial necesita que las publicaciones puedan etiquetarse con tags (RF-015) y que el muro pueda filtrarse por esas etiquetas (RF-016). Además, los usuarios deben poder seguir tags para armar su feed personalizado.

Moodle ya incluye un sistema de etiquetas propio, el subsistema `core_tag`, que se usa en otras partes de la plataforma (cursos, perfiles, recursos). Había que decidir si ChuspaSocial reutiliza ese sistema o guarda sus etiquetas por su cuenta.

## Decisión

Usar la API de tags de Moodle (`core_tag`) para etiquetar las publicaciones de ChuspaSocial, en lugar de crear una tabla propia de tags.

`core_tag` ya resuelve el guardado de etiquetas, su asociación a cualquier elemento mediante componente e item, el autocompletado en formularios y la administración de tags por parte del sitio. Reutilizarlo evita duplicar esa lógica y permite que las publicaciones compartan las mismas etiquetas que el resto de Moodle, incluidos los tags predefinidos.

## Alternativas descartadas

- Crear una tabla propia de tags dentro del plugin. Se descarta porque obligaría a reimplementar el guardado, la búsqueda, el autocompletado y la administración de etiquetas, y las etiquetas de ChuspaSocial quedarían separadas de las del resto de Moodle.
- Guardar los tags como texto dentro de cada publicación. Se descarta porque dificulta filtrar el muro por etiqueta, seguir tags y evitar duplicados con distinta escritura.

## Consecuencias

Las publicaciones se asociarán a tags mediante `core_tag`, registrando el componente `local_chuspasocial` y su área de tags. El plugin no necesitará tablas propias para guardar etiquetas.

Los administradores podrán gestionar las etiquetas de ChuspaSocial desde la administración de tags de Moodle. A cambio, el comportamiento de las etiquetas queda sujeto a las reglas y limitaciones de `core_tag`, y cualquier necesidad que esa API no cubra deberá resolverse sin romper su modelo de datos.

## Interacción con el core de Moodle

Para asegurar el correcto funcionamiento, la interacción con el subsistema `core_tag` se estructurará de la siguiente manera:

1. **Definición del área de tags:** Se implementará la función `local_chuspasocial_get_tagged_areas()` en el archivo `lib.php` del plugin para registrar formalmente el componente ante el core. El área de tags del componente (`itemtype`) será `chuspasocial_posts` y utilizará la colección de tags por defecto del sistema (`core_cllct_default`).
2. **Asociación y guardado:** Al crear o editar una publicación, se invocará la API `core_tag_tag::set_item_tags()` pasando el componente `local_chuspasocial`, el itemtype `chuspasocial_posts`, el ID de la publicación y el arreglo con los tags ingresados.
3. **Recuperación y renderizado:** Al listar el muro o el feed personalizado, se utilizarán las funciones del core como `core_tag_tag::get_item_tags()` para extraer las etiquetas asociadas a cada ID de publicación de forma eficiente.
