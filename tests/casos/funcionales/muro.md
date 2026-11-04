# Casos de prueba funcionales — Muro

Casos manuales para las funciones del muro. Cada caso referencia el requerimiento
funcional (RF) de `docs/requerimientos.md` que verifica.

| ID | RF | Precondición | Pasos | Resultado esperado |
|---|---|---|---|---|
| CP-MURO-01 | RF-006 | Usuario autenticado con al menos una publicación propia en el muro. | 1. Abrir el muro. 2. En una publicación propia, elegir la opción Eliminar. 3. Confirmar la acción. | La publicación se elimina y deja de mostrarse en el muro. |
| CP-MURO-02 | RF-006 | Usuario autenticado; en el muro hay una publicación creada por otro usuario. | 1. Abrir el muro. 2. Intentar eliminar la publicación del otro usuario. 3. Confirmar la acción. | El sistema rechaza la operación y la publicación queda sin cambios. |
| CP-MURO-03 | RF-021 | Usuario autenticado que sigue a otros usuarios y tags que tienen publicaciones. | 1. Abrir el feed personalizado. 2. Revisar las publicaciones mostradas y su fecha. | Se ven las publicaciones de los usuarios y tags seguidos, ordenadas por fecha. |
| CP-MURO-04 | RF-021 | Usuario autenticado con el feed personalizado abierto. | 1. Seguir a un usuario o tag nuevo que tenga publicaciones. 2. Recargar el feed personalizado. | Las publicaciones del usuario o tag recién seguido aparecen en el feed. |
| CP-MURO-05 | RF-024 | En el muro de una materia hay un anuncio oficial y una publicación regular. | 1. Abrir el muro de la materia. 2. Comparar el anuncio oficial con la publicación regular. | El anuncio oficial se muestra con un color y un ícono distintos, identificable sin leer el contenido. |
| CP-MURO-06 | RF-042 | Administrador configuró una longitud máxima de publicación; usuario autenticado en una materia. | 1. Abrir el muro de la materia. 2. Escribir una publicación que supere la longitud máxima. 3. Intentar guardarla. | La publicación no se guarda y el sistema muestra un mensaje de error. |
| CP-MURO-07 | RF-042 | Administrador configuró el número de publicaciones por página; la materia tiene más publicaciones que ese número. | 1. Abrir el muro de la materia. 2. Contar las publicaciones de la primera página. 3. Avanzar a la página siguiente. | Cada página muestra como máximo el número configurado y la paginación permite ver el resto. |
| CP-MURO-08 | RF-042 | Administrador configuró el número máximo de imágenes por publicación; usuario autenticado en una materia. | 1. Abrir el muro de la materia. 2. Crear una publicación con más imágenes que el máximo configurado. 3. Intentar publicarla. | El muro respeta el máximo de imágenes por publicación al crearla. |
| CP-MUR-009 | RF-042 | Usuario autenticado; existe una publicación con al menos una imagen adjunta y se dispone de un dispositivo móvil o vista responsive. | 1. Abrir el muro desde un dispositivo móvil o activar la vista responsive del navegador. 2. Localizar una publicación con imagen adjunta. 3. Revisar la tarjeta en distintos anchos de pantalla. 4. Comprobar que texto, imagen y acciones permanezcan visibles y utilizables. | La publicación se adapta al ancho disponible sin desbordamientos horizontales; la imagen conserva una visualización adecuada y los controles siguen siendo accesibles. |
