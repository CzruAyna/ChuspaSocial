# Casos de prueba funcionales: publicaciones del muro

Matriz de casos de prueba manuales para la creación y eliminación de
publicaciones en el muro de una materia.

## Convenciones

- **Tipo:** Éxito (flujo correcto) o Error (validación).
- **Requerimiento:** RF de `docs/requerimientos.md` que cubre el caso.

## Resumen

| ID | Caso | Tipo | Requerimiento |
| --- | --- | --- | --- |
| MP-001 | Publicar mensaje con texto simple | Éxito | RF-003 |
| MP-002 | Publicar mensaje con etiquetas | Éxito | RF-015 |
| MP-003 | Publicar mensaje con texto vacío | Error | RF-003 |
| MP-004 | Eliminar una publicación propia | Éxito | RF-006 |

## MP-001: Publicar mensaje con texto simple

**Tipo:** Éxito

Precondiciones:

- El usuario tiene sesión iniciada.
- El usuario está inscrito en una materia.

Pasos:

1. Entrar al muro de la materia.
2. Escribir un mensaje de texto simple, por ejemplo "Hola a todos".
3. Confirmar la publicación.

Resultado esperado:

- El sistema registra la publicación.
- La publicación se muestra en el muro de la materia con el texto escrito.

## MP-002: Publicar mensaje con etiquetas

**Tipo:** Éxito

Precondiciones:

- El usuario tiene sesión iniciada.
- El usuario está inscrito en una materia.
- La funcionalidad de tags de Moodle está habilitada.

Pasos:

1. Entrar al muro de la materia.
2. Escribir un mensaje, por ejemplo "Apuntes de cálculo".
3. En el campo de etiquetas, escribir una o más etiquetas, por ejemplo
   "matematicas" y "parcial", o elegir tags predefinidos de Moodle.
4. Confirmar la publicación.

Resultado esperado:

- El sistema asocia las etiquetas a la publicación al guardarla.
- Las etiquetas son visibles en la publicación para todos los usuarios.

## MP-003: Publicar mensaje con texto vacío

**Tipo:** Error

Precondiciones:

- El usuario tiene sesión iniciada.
- El usuario está inscrito en una materia.

Pasos:

1. Entrar al muro de la materia.
2. Dejar el campo de mensaje vacío.
3. Confirmar la publicación.

Resultado esperado:

- El sistema impide que se publique el mensaje.
- No aparece ninguna publicación nueva en el muro.

## MP-004: Eliminar una publicación propia

**Tipo:** Éxito

Precondiciones:

- El usuario tiene sesión iniciada.
- El usuario es el autor de al menos una publicación en el muro.

Pasos:

1. Entrar al muro donde está la publicación.
2. Ubicar una publicación propia.
3. Seleccionar la opción de eliminar.
4. Confirmar la acción.

Resultado esperado:

- El sistema elimina la publicación.
- La publicación deja de mostrarse en el muro.
- Las demás publicaciones no se modifican.
