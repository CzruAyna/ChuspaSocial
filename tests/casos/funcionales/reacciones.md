# Casos de prueba funcionales — Reacciones

Casos manuales para las funciones de reacciones a publicaciones y comentarios. Cada caso
referencia el requerimiento funcional (RF) de `docs/requerimientos.md` que verifica.

| ID | RF | Precondición | Pasos | Resultado esperado |
|---|---|---|---|---|
| CF-REA-01 | RF-013 | Usuario autenticado; en el muro hay una publicación a la que el usuario aún no ha reaccionado. | 1. Abrir el muro. 2. Ubicar la publicación sin reacción del usuario. 3. Elegir la reacción de tipo "like". | El sistema registra la reacción "like" del usuario en esa publicación, el botón se muestra activo y la reacción permanece al recargar el muro. |
| CF-REA-02 | RF-013 | Usuario autenticado que ya agregó una reacción "like" a una publicación. | 1. Abrir el muro. 2. En la publicación con reacción activa, volver a elegir "like". 3. Recargar el muro. 4. Elegir "like" de nuevo. | El sistema actualiza el estado de la reacción sin crear registros duplicados: al repetir la acción la reacción se elimina y al elegirla otra vez se agrega nuevamente. |
| CF-REA-03 | RF-014 | Usuario autenticado; en el muro hay una publicación con un total de reacciones conocido. | 1. Abrir el muro y anotar el total de reacciones de la publicación. 2. Agregar una reacción "like". 3. Observar el total sin recargar. 4. Quitar la reacción y observar el total. | El total aumenta en uno al reaccionar y disminuye en uno al quitar la reacción, sin recargar la página. |
