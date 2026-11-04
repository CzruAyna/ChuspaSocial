# Casos de prueba funcionales — Moderación

Casos manuales para las funciones de reporte y moderación de contenido. Cada caso
referencia el requerimiento funcional (RF) de `docs/requerimientos.md` que verifica.

| ID | RF | Precondición | Pasos | Resultado esperado |
|---|---|---|---|---|
| CF-MOD-01 | RF-029 | Usuario autenticado; en el muro hay una publicación ofensiva de otro usuario. | 1. Abrir el muro. 2. En la publicación ofensiva, elegir la opción Reportar. 3. Seleccionar un motivo. 4. Escribir un comentario opcional. 5. Confirmar el reporte. | El sistema registra el reporte asociado a la publicación y al motivo indicado, y notifica que fue registrado correctamente. |
| CF-MOD-02 | RF-030 | Usuario autenticado con capacidad de moderador; existe al menos un reporte abierto en el sistema. | 1. Abrir el panel de moderación. 2. Acceder a la cola de reportes. 3. Seleccionar un reporte. | La cola muestra los reportes abiertos pendientes de revisión y, al seleccionar uno, se muestra su información (contenido, motivo y comentario) para que pueda revisarse. |
| CF-MOD-03 | RF-030 | Usuario autenticado con capacidad de moderador; la cola contiene dos reportes sobre publicaciones distintas. | 1. Abrir el panel de moderación. 2. Seleccionar el primer reporte y elegir ocultar el contenido. 3. Seleccionar el segundo reporte y elegir descartar. 4. Abrir el muro. | La publicación ocultada deja de mostrarse en el muro, la publicación del reporte descartado sigue visible, y ambos reportes salen de la cola de pendientes. |
